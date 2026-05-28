<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\NormalizesMoneyInputs;
use App\Models\AssetCategory;
use App\Models\AssetDocument;
use App\Models\AssetLocation;
use App\Models\AssetProcurement;
use App\Models\AssetReceipt;
use App\Services\AssetNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AssetReceiptController extends Controller
{
    use NormalizesMoneyInputs;

    public function index()
    {
        $receipts = AssetReceipt::with(['procurement', 'receivedBy', 'supplier'])
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
        $procurement = $procurement_id ? AssetProcurement::with(['items.receiptItems', 'supplier'])->find($procurement_id) : null;

        return view('assets.receipts.create', $this->formData([
            'receipt' => new AssetReceipt([
                'received_date' => now(),
                'status' => 'draft',
                'asset_supplier_id' => $procurement?->asset_supplier_id,
                'supplier_name' => $procurement?->supplier?->name,
            ]),
            'procurements' => AssetProcurement::with('supplier')->whereIn('status', ['approved', 'purchasing'])->latest()->get(),
            'procurement' => $procurement,
            'items' => $procurement ? $this->receiptRowsForProcurement($procurement) : collect(),
        ]));
    }

    public function store(Request $request, AssetNumberService $numberService)
    {
        $validated = $this->validatedReceipt($request);
        $procurement = AssetProcurement::with(['items.receiptItems', 'supplier'])->findOrFail($validated['procurement_id']);

        if (! in_array($procurement->status, ['approved', 'purchasing'], true)) {
            throw ValidationException::withMessages(['procurement_id' => 'Procurement harus berstatus approved atau purchasing.']);
        }

        $receipt = DB::transaction(function () use ($request, $validated, $procurement, $numberService) {
            $items = $this->receivedItems($validated['items']);
            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['items' => 'Minimal satu item harus memiliki quantity diterima lebih dari 0.']);
            }

            $items = $this->prepareReceiptItems($items, $procurement);

            $receipt = AssetReceipt::create([
                'procurement_id' => $procurement->id,
                'receipt_number' => $numberService->receiptNumber(),
                'received_by' => $request->user()?->id,
                'received_date' => $validated['received_date'],
                'asset_supplier_id' => $procurement->asset_supplier_id,
                'supplier_name' => $procurement->supplier?->name,
                'delivery_note_number' => $validated['delivery_note_number'] ?? null,
                'invoice_number' => $validated['invoice_number'] ?? null,
                'status' => 'partial_received',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $receipt->items()->create($item);
            }

            $status = $this->syncReceiptAndProcurementStatus($receipt);
            $receipt->update(['status' => $status]);
            $this->storeDocument($request, $receipt);

            return $receipt;
        });

        return redirect()->route('assets.receipts.show', $receipt)->with('success', 'Penerimaan barang berhasil disimpan.');
    }

    public function show(AssetReceipt $receipt)
    {
        $receipt->load(['procurement', 'receivedBy', 'supplier', 'documents.uploadedBy', 'items.procurementItem', 'items.category', 'items.location']);

        return view('assets.receipts.show', ['receipt' => $receipt]);
    }

    public function edit(AssetReceipt $receipt)
    {
        $receipt->load(['procurement.supplier', 'procurement.items.receiptItems', 'items', 'supplier']);

        return view('assets.receipts.edit', $this->formData([
            'receipt' => $receipt,
            'procurements' => AssetProcurement::with('supplier')->whereIn('status', ['approved', 'purchasing', 'received'])->latest()->get(),
            'procurement' => $receipt->procurement,
            'items' => $this->receiptRowsForEdit($receipt),
        ]));
    }

    public function update(Request $request, AssetReceipt $receipt)
    {
        $validated = $this->validatedReceipt($request, $receipt);

        DB::transaction(function () use ($request, $validated, $receipt) {
            $procurement = AssetProcurement::with(['items.receiptItems', 'supplier'])->findOrFail($validated['procurement_id']);
            $items = $this->receivedItems($validated['items']);
            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['items' => 'Minimal satu item harus memiliki quantity diterima lebih dari 0.']);
            }

            $items = $this->prepareReceiptItems($items, $procurement, $receipt);

            $receipt->update([
                'procurement_id' => $validated['procurement_id'],
                'received_date' => $validated['received_date'],
                'asset_supplier_id' => $procurement->asset_supplier_id,
                'supplier_name' => $procurement->supplier?->name,
                'delivery_note_number' => $validated['delivery_note_number'] ?? null,
                'invoice_number' => $validated['invoice_number'] ?? null,
                'status' => 'partial_received',
                'notes' => $validated['notes'] ?? null,
            ]);

            $receipt->items()->delete();
            foreach ($items as $item) {
                $receipt->items()->create($item);
            }

            $status = $this->syncReceiptAndProcurementStatus($receipt);
            $receipt->update(['status' => $status]);
            $this->storeDocument($request, $receipt);
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
        $this->normalizeMoneyInputs($request, ['items.*.acquisition_value']);

        return $request->validate([
            'procurement_id' => ['required', Rule::exists('asset_procurements', 'id')],
            'received_date' => ['required', 'date'],
            'delivery_note_number' => ['nullable', 'string', 'max:255'],
            'invoice_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'document' => ['nullable', 'file', 'max:5120'],
            'document_notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.procurement_item_id' => ['nullable', 'exists:asset_procurement_items,id'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity_ordered' => ['required', 'numeric', 'min:0.01'],
            'items.*.quantity_received' => ['required', 'numeric', 'min:0'],
            'items.*.condition' => ['nullable', 'string', 'max:255'],
            'items.*.asset_category_id' => ['nullable', 'exists:asset_categories,id'],
            'items.*.asset_location_id' => ['nullable', 'exists:asset_locations,id'],
            'items.*.brand' => ['nullable', 'string', 'max:255'],
            'items.*.model' => ['nullable', 'string', 'max:255'],
            'items.*.serial_number' => ['nullable', 'string', 'max:255'],
            'items.*.specification' => ['nullable', 'string'],
            'items.*.acquisition_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);
    }

    private function formData(array $data): array
    {
        return $data + [
            'categories' => AssetCategory::orderBy('name')->get(),
            'locations' => AssetLocation::orderBy('name')->get(),
        ];
    }

    private function receiptRowsForProcurement(AssetProcurement $procurement): \Illuminate\Support\Collection
    {
        return $procurement->items
            ->map(function ($item) {
                $received = (float) $item->receiptItems->sum('quantity_received');
                $remaining = max((float) $item->quantity - $received, 0);

                if ($remaining <= 0) {
                    return null;
                }

                $lastReceiptItem = $this->lastReceivedItemTemplate($item);

                return [
                    'procurement_item_id' => $item->id,
                    'item_name' => $item->item_name,
                    'quantity_ordered' => $item->quantity,
                    'quantity_received' => 0,
                    'previous_received' => $received,
                    'remaining_quantity' => $remaining,
                    'condition' => 'good',
                    'asset_category_id' => $lastReceiptItem?->asset_category_id,
                    'asset_location_id' => $lastReceiptItem?->asset_location_id,
                    'brand' => $lastReceiptItem?->brand,
                    'model' => $lastReceiptItem?->model,
                    'serial_number' => null,
                    'specification' => $lastReceiptItem?->specification ?: $item->specification,
                    'acquisition_value' => $lastReceiptItem?->acquisition_value ?: $item->estimated_unit_price,
                    'notes' => null,
                    'is_prefilled_from_previous' => (bool) $lastReceiptItem,
                ];
            })
            ->filter()
            ->values();
    }

    private function lastReceivedItemTemplate($procurementItem): ?\App\Models\AssetReceiptItem
    {
        return $procurementItem->receiptItems
            ->filter(fn ($item) => (float) $item->quantity_received > 0)
            ->sortByDesc(fn ($item) => $item->created_at?->timestamp ?? $item->id)
            ->first();
    }

    private function receiptRowsForEdit(AssetReceipt $receipt): \Illuminate\Support\Collection
    {
        return $receipt->items->map(function ($item) use ($receipt) {
            $procurementItem = $item->procurementItem;
            $receivedByOtherReceipts = $procurementItem
                ? (float) $procurementItem->receiptItems->reject(fn ($receiptItem) => (int) $receiptItem->receipt_id === (int) $receipt->id)->sum('quantity_received')
                : 0;
            $remaining = $procurementItem
                ? max((float) $procurementItem->quantity - $receivedByOtherReceipts, 0)
                : (float) $item->quantity_ordered;

            return [
                'procurement_item_id' => $item->procurement_item_id,
                'item_name' => $item->item_name,
                'quantity_ordered' => $procurementItem?->quantity ?? $item->quantity_ordered,
                'quantity_received' => $item->quantity_received,
                'previous_received' => $receivedByOtherReceipts,
                'remaining_quantity' => $remaining,
                'condition' => $item->condition ?? 'good',
                'asset_category_id' => $item->asset_category_id,
                'asset_location_id' => $item->asset_location_id,
                'brand' => $item->brand,
                'model' => $item->model,
                'serial_number' => $item->serial_number,
                'specification' => $item->specification,
                'acquisition_value' => $item->acquisition_value,
                'notes' => $item->notes,
            ];
        });
    }

    private function receivedItems(array $items): \Illuminate\Support\Collection
    {
        return collect($items)
            ->filter(fn ($item) => filled($item['item_name'] ?? null) && (float) ($item['quantity_received'] ?? 0) > 0)
            ->values();
    }

    private function prepareReceiptItems(\Illuminate\Support\Collection $items, AssetProcurement $procurement, ?AssetReceipt $receipt = null): \Illuminate\Support\Collection
    {
        $procurementItems = $procurement->items->keyBy('id');

        return $items->map(function ($item) use ($procurementItems, $receipt) {
            $procurementItem = $procurementItems->get((int) ($item['procurement_item_id'] ?? 0));
            $quantityReceived = (float) ($item['quantity_received'] ?? 0);

            if ($procurementItem) {
                $alreadyReceived = $procurementItem->receiptItems
                    ->when($receipt, fn ($items) => $items->reject(fn ($receiptItem) => (int) $receiptItem->receipt_id === (int) $receipt->id))
                    ->sum('quantity_received');
                $remaining = max((float) $procurementItem->quantity - (float) $alreadyReceived, 0);

                if ($quantityReceived > $remaining) {
                    throw ValidationException::withMessages([
                        'items' => "Quantity diterima untuk {$procurementItem->item_name} melebihi sisa {$remaining}.",
                    ]);
                }

                $item['quantity_ordered'] = $procurementItem->quantity;
                $item['item_name'] = $procurementItem->item_name;
            } elseif ($quantityReceived > (float) ($item['quantity_ordered'] ?? 0)) {
                throw ValidationException::withMessages(['items' => 'Quantity diterima tidak boleh melebihi quantity ordered.']);
            }

            unset($item['previous_received'], $item['remaining_quantity']);

            return $item;
        });
    }

    private function syncReceiptAndProcurementStatus(AssetReceipt $receipt): string
    {
        $procurement = $receipt->procurement()->with('items.receiptItems')->first();
        $isFullyReceived = $procurement->items->every(function ($item) {
            return (float) $item->receiptItems->sum('quantity_received') >= (float) $item->quantity;
        });

        $procurement->update(['status' => $isFullyReceived ? 'received' : 'purchasing']);

        return $isFullyReceived ? 'fully_received' : 'partial_received';
    }

    private function storeDocument(Request $request, AssetReceipt $receipt): void
    {
        if (! $request->hasFile('document')) {
            return;
        }

        $file = $request->file('document');
        AssetDocument::create([
            'procurement_id' => $receipt->procurement_id,
            'receipt_id' => $receipt->id,
            'document_type' => 'receipt_document',
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $file->store('assets/receipts', 'public'),
            'notes' => $request->input('document_notes'),
            'uploaded_by' => $request->user()?->id,
        ]);
    }
}
