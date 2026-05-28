<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\NormalizesMoneyInputs;
use App\Models\AssetProcurement;
use App\Services\AssetNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssetProcurementController extends Controller
{
    use NormalizesMoneyInputs;

    public function index()
    {
        $procurements = AssetProcurement::with('requestedBy')
            ->when(request('search'), fn ($query, $search) => $query->where('procurement_number', 'like', "%{$search}%")->orWhere('department', 'like', "%{$search}%"))
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('assets.procurements.index', [
            'procurements' => $procurements,
            'statuses' => $this->statuses(),
        ]);
    }

    public function create()
    {
        return view('assets.procurements.create', [
            'procurement' => new AssetProcurement(['request_date' => now(), 'status' => 'draft']),
            'items' => collect(),
        ]);
    }

    public function store(Request $request, AssetNumberService $numberService)
    {
        $validated = $this->validatedProcurement($request);

        $procurement = DB::transaction(function () use ($request, $validated, $numberService) {
            $items = collect($validated['items'])->filter(fn ($item) => filled($item['item_name'] ?? null));
            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['items' => 'Minimal satu item pengadaan wajib diisi.']);
            }
            if ($items->contains(fn ($item) => (float) ($item['quantity'] ?? 0) <= 0)) {
                throw ValidationException::withMessages(['items' => 'Quantity item pengadaan harus lebih dari 0.']);
            }

            $procurement = AssetProcurement::create([
                'procurement_number' => $numberService->procurementNumber(),
                'requested_by' => $request->user()?->id,
                'request_date' => $validated['request_date'],
                'department' => $validated['department'] ?? null,
                'purpose' => $validated['purpose'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'draft',
                'total_estimated_cost' => $items->sum(fn ($item) => $this->lineTotal($item)),
            ]);

            $this->syncItems($procurement, $items);

            return $procurement;
        });

        return redirect()->route('assets.procurements.show', $procurement)->with('success', 'Pengadaan berhasil dibuat.');
    }

    public function show(AssetProcurement $procurement)
    {
        $procurement->load(['requestedBy', 'items.receiptItems', 'approvals.approvedBy', 'receipts.items']);

        return view('assets.procurements.show', ['procurement' => $procurement]);
    }

    public function edit(AssetProcurement $procurement)
    {
        $procurement->load('items');

        return view('assets.procurements.edit', [
            'procurement' => $procurement,
            'items' => $procurement->items,
        ]);
    }

    public function update(Request $request, AssetProcurement $procurement)
    {
        if (! in_array($procurement->status, ['draft', 'rejected'], true)) {
            return back()->with('error', 'Pengadaan hanya bisa diedit saat draft atau rejected.');
        }

        $validated = $this->validatedProcurement($request);

        DB::transaction(function () use ($validated, $procurement) {
            $items = collect($validated['items'])->filter(fn ($item) => filled($item['item_name'] ?? null));
            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['items' => 'Minimal satu item pengadaan wajib diisi.']);
            }
            if ($items->contains(fn ($item) => (float) ($item['quantity'] ?? 0) <= 0)) {
                throw ValidationException::withMessages(['items' => 'Quantity item pengadaan harus lebih dari 0.']);
            }

            $procurement->update([
                'request_date' => $validated['request_date'],
                'department' => $validated['department'] ?? null,
                'purpose' => $validated['purpose'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'total_estimated_cost' => $items->sum(fn ($item) => $this->lineTotal($item)),
            ]);

            $procurement->items()->delete();
            $this->syncItems($procurement, $items);
        });

        return redirect()->route('assets.procurements.show', $procurement)->with('success', 'Pengadaan berhasil diperbarui.');
    }

    public function destroy(AssetProcurement $procurement)
    {
        $procurement->delete();

        return redirect()->route('assets.procurements.index')->with('success', 'Pengadaan berhasil dihapus.');
    }

    public function submit(AssetProcurement $procurement)
    {
        if ($procurement->items()->doesntExist()) {
            return back()->with('error', 'Pengadaan belum memiliki item.');
        }

        DB::transaction(function () use ($procurement) {
            $procurement->update([
                'status' => 'waiting_supervisor',
                'current_approval_level' => 1,
                'supervisor_status' => 'pending',
                'finance_status' => 'pending',
                'director_status' => 'pending',
            ]);

            foreach ([1 => 'Supervisor', 2 => 'Keuangan', 3 => 'Direktur'] as $level => $role) {
                $procurement->approvals()->updateOrCreate(
                    ['approval_level' => $level],
                    ['role_name' => $role, 'status' => 'pending', 'approved_by' => null, 'approved_at' => null, 'notes' => null]
                );
            }
        });

        return redirect()->route('assets.procurements.show', $procurement)->with('success', 'Pengadaan dikirim ke approval Supervisor.');
    }

    private function validatedProcurement(Request $request): array
    {
        $this->normalizeMoneyInputs($request, ['items.*.estimated_unit_price']);

        return $request->validate([
            'request_date' => ['required', 'date'],
            'department' => ['nullable', 'string', 'max:255'],
            'purpose' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['nullable', 'string', 'max:255'],
            'items.*.specification' => ['nullable', 'string'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0.01'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.estimated_unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.supplier_candidate' => ['nullable', 'string', 'max:255'],
            'items.*.reason' => ['nullable', 'string'],
        ]);
    }

    private function syncItems(AssetProcurement $procurement, $items): void
    {
        foreach ($items as $item) {
            $quantity = (float) ($item['quantity'] ?? 0);
            $unitPrice = (float) ($item['estimated_unit_price'] ?? 0);
            $procurement->items()->create([
                'item_name' => $item['item_name'],
                'specification' => $item['specification'] ?? null,
                'quantity' => $quantity,
                'unit' => $item['unit'] ?? null,
                'estimated_unit_price' => $unitPrice,
                'estimated_total_price' => $quantity * $unitPrice,
                'supplier_candidate' => $item['supplier_candidate'] ?? null,
                'reason' => $item['reason'] ?? null,
                'status' => $item['status'] ?? null,
            ]);
        }
    }

    private function lineTotal(array $item): float
    {
        return (float) ($item['quantity'] ?? 0) * (float) ($item['estimated_unit_price'] ?? 0);
    }

    private function statuses(): array
    {
        return ['draft', 'submitted', 'waiting_supervisor', 'waiting_finance', 'waiting_director', 'approved', 'rejected', 'purchasing', 'received', 'converted_to_asset', 'cancelled'];
    }
}
