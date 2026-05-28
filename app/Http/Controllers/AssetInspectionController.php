<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetInspection;
use App\Models\User;
use App\Services\AssetNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AssetInspectionController extends Controller
{
    public function index()
    {
        return view('assets.inspections.index', [
            'inspections' => AssetInspection::with(['asset', 'inspectedBy'])
                ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
                ->when(request('result'), fn ($query, $result) => $query->where('result', $result))
                ->when(request('date_from'), fn ($query, $date) => $query->whereDate('inspection_date', '>=', $date))
                ->when(request('date_to'), fn ($query, $date) => $query->whereDate('inspection_date', '<=', $date))
                ->latest('inspection_date')
                ->paginate(10)
                ->withQueryString(),
            'overdueAssets' => Asset::where('requires_periodic_inspection', true)->whereDate('next_inspection_date', '<', now())->limit(8)->get(),
            'statuses' => ['scheduled', 'completed', 'cancelled', 'overdue'],
            'results' => ['pass', 'failed', 'need_repair', 'not_usable'],
        ]);
    }

    public function create()
    {
        return view('assets.inspections.create', $this->formData(new AssetInspection(['inspection_date' => now(), 'status' => 'completed'])));
    }

    public function store(Request $request, AssetNumberService $numberService)
    {
        $validated = $this->validatedInspection($request);

        $inspection = DB::transaction(function () use ($request, $validated, $numberService) {
            $inspection = AssetInspection::create([
                'inspection_number' => $numberService->inspectionNumber(),
                'asset_id' => $validated['asset_id'],
                'inspection_date' => $validated['inspection_date'],
                'next_inspection_date' => $validated['next_inspection_date'] ?? null,
                'inspected_by' => $validated['inspected_by'] ?? $request->user()?->id,
                'result' => $validated['result'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->syncItems($inspection, collect($validated['items'] ?? []));
            $this->updateAssetFromInspection($request, $inspection);

            return $inspection;
        });

        return redirect()->route('assets.inspections.show', $inspection)->with('success', 'Pemeriksaan berhasil disimpan.');
    }

    public function show(AssetInspection $inspection)
    {
        return view('assets.inspections.show', ['inspection' => $inspection->load(['asset', 'inspectedBy', 'items'])]);
    }

    public function edit(AssetInspection $inspection)
    {
        return view('assets.inspections.edit', $this->formData($inspection->load('items')));
    }

    public function update(Request $request, AssetInspection $inspection)
    {
        $validated = $this->validatedInspection($request);

        DB::transaction(function () use ($request, $inspection, $validated) {
            $inspection->update([
                'asset_id' => $validated['asset_id'],
                'inspection_date' => $validated['inspection_date'],
                'next_inspection_date' => $validated['next_inspection_date'] ?? null,
                'inspected_by' => $validated['inspected_by'] ?? $request->user()?->id,
                'result' => $validated['result'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);
            $inspection->items()->delete();
            $this->syncItems($inspection, collect($validated['items'] ?? []));
            $this->updateAssetFromInspection($request, $inspection);
        });

        return redirect()->route('assets.inspections.show', $inspection)->with('success', 'Pemeriksaan berhasil diperbarui.');
    }

    public function destroy(AssetInspection $inspection)
    {
        $inspection->delete();

        return redirect()->route('assets.inspections.index')->with('success', 'Pemeriksaan berhasil dihapus.');
    }

    private function formData(AssetInspection $inspection): array
    {
        return [
            'inspection' => $inspection,
            'assets' => Asset::orderBy('asset_code')->get(),
            'users' => User::orderBy('name')->get(),
            'statuses' => ['scheduled', 'completed', 'cancelled', 'overdue'],
            'results' => ['pass', 'failed', 'need_repair', 'not_usable'],
            'itemResults' => ['ok', 'not_ok', 'not_applicable'],
            'defaultChecklist' => [
                'Kondisi fisik baik',
                'Label aset tersedia',
                'Alat dapat digunakan',
                'Penyimpanan sesuai',
                'Kebersihan alat baik',
                'Dokumen/manual tersedia',
                'Tidak ada kerusakan visual',
            ],
        ];
    }

    private function validatedInspection(Request $request): array
    {
        return $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'inspection_date' => ['required', 'date'],
            'next_inspection_date' => ['nullable', 'date'],
            'inspected_by' => ['nullable', 'exists:users,id'],
            'result' => ['required', Rule::in(['pass', 'failed', 'need_repair', 'not_usable'])],
            'status' => ['required', Rule::in(['scheduled', 'completed', 'cancelled', 'overdue'])],
            'notes' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.checklist_name' => ['nullable', 'string', 'max:255'],
            'items.*.result' => ['nullable', Rule::in(['ok', 'not_ok', 'not_applicable'])],
            'items.*.notes' => ['nullable', 'string'],
        ]);
    }

    private function syncItems(AssetInspection $inspection, $items): void
    {
        foreach ($items->filter(fn ($item) => filled($item['checklist_name'] ?? null)) as $item) {
            $inspection->items()->create([
                'checklist_name' => $item['checklist_name'],
                'result' => $item['result'] ?? 'ok',
                'notes' => $item['notes'] ?? null,
            ]);
        }
    }

    private function updateAssetFromInspection(Request $request, AssetInspection $inspection): void
    {
        $asset = $inspection->asset;
        $oldStatus = $asset->status;
        $oldCondition = $asset->condition;
        $newStatus = $asset->status;
        $newCondition = $asset->condition;

        if ($inspection->result === 'pass') {
            $newStatus = 'active';
            $newCondition = 'good';
        } elseif ($inspection->result === 'need_repair') {
            $newStatus = 'in_repair';
            $newCondition = 'under_repair';
        } elseif (in_array($inspection->result, ['failed', 'not_usable'], true)) {
            $newStatus = 'not_usable';
            $newCondition = 'damaged';
        }

        $asset->update([
            'status' => $newStatus,
            'condition' => $newCondition,
            'last_inspection_date' => $inspection->inspection_date,
            'next_inspection_date' => $inspection->next_inspection_date ?: ($asset->inspection_interval_months ? $inspection->inspection_date->copy()->addMonths($asset->inspection_interval_months) : null),
        ]);

        if ($oldStatus !== $newStatus || $oldCondition !== $newCondition) {
            $asset->statusLogs()->create([
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'old_condition' => $oldCondition,
                'new_condition' => $newCondition,
                'description' => "Update dari pemeriksaan {$inspection->inspection_number}.",
                'changed_by' => $request->user()?->id,
            ]);
        }
    }
}
