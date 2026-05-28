<?php

namespace App\Http\Controllers;

use App\Models\AssetProcurement;
use App\Models\AssetReceipt;
use App\Services\AssetNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AssetReceiptController extends Controller
{
    public function index()
    {
        $receipts = AssetReceipt::with(['procurement', 'receivedBy'])
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('assets.receipts.index', [
            'receipts' => $receipts,
            'statuses' => ['draft', 'partial_received', 'fully_received', 'rejected', 'converted_to_asset'],
        ]);
    }

    public function create($procurement_id = null)
    {
        $procurement_id ??= request('procurement_id');
        $procurement = $procurement_id ? AssetProcurement::with('items')->find($procurement_id) : null;

        return view('assets.receipts.create', [
            'receipt' => new AssetReceipt(['received_date' => now(), 'status' => 'draft']),
            'procurements' => AssetProcurement::whereIn('status', ['approved', 'purchasing'])->latest()->get(),
            'procurement' => $procurement,
            'items' => $procurement?->items ?? collect(),
        ]);
    }

    public function store(Request $request, AssetNumberService $numberService)
    {
        $validated = $this->validatedReceipt($request);
        $procurement = AssetProcurement::with('items')->findOrFail($validated['procurement_id']);

        if (! in_array($procurement->status, ['approved', 'purchasing'], true)) {
            throw ValidationException::withMessages(['procurement_id' => 'Procurement harus berstatus approved atau purchasing.']);
        }

        $receipt = DB::transaction(function () use ($request, $validated, $procurement, $numberService) {
            $items = collect($validated['items'])->filter(fn ($item) => filled($item['item_name'] ?? null));
            $status = $this->receiptStatus($items);

            $receipt = AssetReceipt::create([
                'procurement_id' => $procurement->id,
                'receipt_number' => $numberService->receiptNumber(),
                'received_by' => $request->user()?->id,
                'received_date' => $validated['received_date'],
                'supplier_name' => $validated['supplier_name'] ?? null,
                'delivery_note_number' => $validated['delivery_note_number'] ?? null,
                'invoice_number' => $validated['invoice_number'] ?? null,
                'status' => $status,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                if ((float) $item['quantity_received'] > (float) $item['quantity_ordered']) {
                    throw ValidationException::withMessages(['items' => 'Quantity diterima tidak boleh melebihi quantity ordered.']);
                }

                $receipt->items()->create($item);
            }

            if ($status === 'fully_received') {
                $procurement->update(['status' => 'received']);
            }

            return $receipt;
        });

        return redirect()->route('assets.receipts.show', $receipt)->with('success', 'Penerimaan barang berhasil disimpan.');
    }

    public function show(AssetReceipt $receipt)
    {
        $receipt->load(['procurement', 'receivedBy', 'items.procurementItem']);

        return view('assets.receipts.show', ['receipt' => $receipt]);
    }

    public function edit(AssetReceipt $receipt)
    {
        $receipt->load(['procurement.items', 'items']);

        return view('assets.receipts.edit', [
            'receipt' => $receipt,
            'procurements' => AssetProcurement::whereIn('status', ['approved', 'purchasing', 'received'])->latest()->get(),
            'procurement' => $receipt->procurement,
            'items' => $receipt->items,
        ]);
    }

    public function update(Request $request, AssetReceipt $receipt)
    {
        $validated = $this->validatedReceipt($request, $receipt);

        DB::transaction(function () use ($validated, $receipt) {
            $items = collect($validated['items'])->filter(fn ($item) => filled($item['item_name'] ?? null));
            $status = $this->receiptStatus($items);

            $receipt->update([
                'procurement_id' => $validated['procurement_id'],
                'received_date' => $validated['received_date'],
                'supplier_name' => $validated['supplier_name'] ?? null,
                'delivery_note_number' => $validated['delivery_note_number'] ?? null,
                'invoice_number' => $validated['invoice_number'] ?? null,
                'status' => $status,
                'notes' => $validated['notes'] ?? null,
            ]);

            $receipt->items()->delete();
            foreach ($items as $item) {
                if ((float) $item['quantity_received'] > (float) $item['quantity_ordered']) {
                    throw ValidationException::withMessages(['items' => 'Quantity diterima tidak boleh melebihi quantity ordered.']);
                }
                $receipt->items()->create($item);
            }
        });

        return redirect()->route('assets.receipts.show', $receipt)->with('success', 'Penerimaan barang berhasil diperbarui.');
    }

    public function destroy(AssetReceipt $receipt)
    {
        $receipt->delete();

        return redirect()->route('assets.receipts.index')->with('success', 'Penerimaan barang berhasil dihapus.');
    }

    private function validatedReceipt(Request $request, ?AssetReceipt $receipt = null): array
    {
        return $request->validate([
            'procurement_id' => ['required', Rule::exists('asset_procurements', 'id')],
            'received_date' => ['required', 'date'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'delivery_note_number' => ['nullable', 'string', 'max:255'],
            'invoice_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.procurement_item_id' => ['nullable', 'exists:asset_procurement_items,id'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity_ordered' => ['required', 'numeric', 'min:0.01'],
            'items.*.quantity_received' => ['required', 'numeric', 'min:0'],
            'items.*.condition' => ['nullable', 'string', 'max:255'],
            'items.*.notes' => ['nullable', 'string'],
        ]);
    }

    private function receiptStatus($items): string
    {
        if ($items->contains(fn ($item) => (float) $item['quantity_received'] <= 0)) {
            return 'partial_received';
        }

        return $items->every(fn ($item) => (float) $item['quantity_received'] >= (float) $item['quantity_ordered'])
            ? 'fully_received'
            : 'partial_received';
    }
}
