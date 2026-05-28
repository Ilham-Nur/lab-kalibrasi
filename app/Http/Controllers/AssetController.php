<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\NormalizesMoneyInputs;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetDocument;
use App\Models\AssetLocation;
use App\Models\User;
use App\Services\AssetNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    use NormalizesMoneyInputs;

    public function index()
    {
        $assets = Asset::query()
            ->with(['category', 'location', 'responsibleUser'])
            ->when(request('search'), fn ($query, $search) => $query->where(function ($subQuery) use ($search) {
                $subQuery->where('asset_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            }))
            ->when(request('asset_category_id'), fn ($query, $value) => $query->where('asset_category_id', $value))
            ->when(request('asset_location_id'), fn ($query, $value) => $query->where('asset_location_id', $value))
            ->when(request('status'), fn ($query, $value) => $query->where('status', $value))
            ->when(request('condition'), fn ($query, $value) => $query->where('condition', $value))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('assets.index', $this->lookupData() + ['assets' => $assets]);
    }

    public function create(AssetNumberService $numberService)
    {
        return view('assets.create', $this->formData(new Asset([
            'asset_code' => $numberService->assetCode(),
            'source_type' => 'existing_asset',
            'condition' => 'good',
            'status' => 'active',
        ])));
    }

    public function store(Request $request, AssetNumberService $numberService)
    {
        $validated = $this->validatedAsset($request);
        $validated['asset_code'] = $validated['asset_code'] ?: $numberService->assetCode();
        $validated = $this->mergeBooleans($request, $validated);

        $asset = DB::transaction(function () use ($request, $validated) {
            $asset = Asset::create($validated);
            $asset->statusLogs()->create([
                'new_status' => $asset->status,
                'new_condition' => $asset->condition,
                'description' => 'Aset dibuat manual/import.',
                'changed_by' => $request->user()?->id,
            ]);
            $this->storeDocument($request, $asset);

            return $asset;
        });

        return redirect()->route('assets.show', $asset)->with('success', 'Aset berhasil ditambahkan.');
    }

    public function show(Asset $asset)
    {
        $asset->load(['category', 'location', 'responsibleUser', 'documents.uploadedBy', 'statusLogs.changedBy', 'inspections.items', 'calibrations.results']);

        return view('assets.show', ['asset' => $asset]);
    }

    public function edit(Asset $asset)
    {
        return view('assets.edit', $this->formData($asset));
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $this->validatedAsset($request, $asset);
        $validated = $this->mergeBooleans($request, $validated);
        $oldStatus = $asset->status;
        $oldCondition = $asset->condition;

        DB::transaction(function () use ($request, $asset, $validated, $oldStatus, $oldCondition) {
            $asset->update($validated);

            if ($oldStatus !== $asset->status || $oldCondition !== $asset->condition) {
                $asset->statusLogs()->create([
                    'old_status' => $oldStatus,
                    'new_status' => $asset->status,
                    'old_condition' => $oldCondition,
                    'new_condition' => $asset->condition,
                    'description' => 'Perubahan dari form data aset.',
                    'changed_by' => $request->user()?->id,
                ]);
            }

            $this->storeDocument($request, $asset);
        });

        return redirect()->route('assets.show', $asset)->with('success', 'Aset berhasil diperbarui.');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();

        return redirect()->route('assets.index')->with('success', 'Aset berhasil dihapus.');
    }

    public function history(Asset $asset)
    {
        return view('assets.history', [
            'asset' => $asset->load(['category', 'location']),
            'logs' => $asset->statusLogs()->with('changedBy')->latest()->paginate(15),
        ]);
    }

    public function importForm()
    {
        return view('assets.import');
    }

    public function importStore(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt']]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        $header = array_map('trim', fgetcsv($handle) ?: []);
        $imported = 0;

        DB::transaction(function () use ($request, $handle, $header, &$imported) {
            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($header, array_pad($row, count($header), null));
                if (! $data || blank($data['asset_code'] ?? null) || blank($data['name'] ?? null)) {
                    continue;
                }

                $category = filled($data['category'] ?? null)
                    ? AssetCategory::firstOrCreate(['name' => trim($data['category'])])
                    : null;
                $location = filled($data['location'] ?? null)
                    ? AssetLocation::firstOrCreate(['name' => trim($data['location'])])
                    : null;

                $asset = Asset::updateOrCreate(
                    ['asset_code' => trim($data['asset_code'])],
                    [
                        'name' => trim($data['name']),
                        'asset_category_id' => $category?->id,
                        'asset_location_id' => $location?->id,
                        'brand' => $data['brand'] ?? null,
                        'model' => $data['model'] ?? null,
                        'serial_number' => $data['serial_number'] ?? null,
                        'source_type' => $data['source_type'] ?: 'existing_asset',
                        'condition' => $data['condition'] ?: 'unknown',
                        'status' => $data['status'] ?: 'active',
                    ]
                );

                $asset->statusLogs()->create([
                    'new_status' => $asset->status,
                    'new_condition' => $asset->condition,
                    'description' => 'Aset lama diimport dari CSV.',
                    'changed_by' => $request->user()?->id,
                ]);
                $imported++;
            }
        });

        fclose($handle);

        return redirect()->route('assets.index')->with('success', "{$imported} aset berhasil diimport.");
    }

    private function formData(Asset $asset): array
    {
        return $this->lookupData() + ['asset' => $asset];
    }

    private function lookupData(): array
    {
        return [
            'categories' => AssetCategory::orderBy('name')->get(),
            'locations' => AssetLocation::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
            'sourceTypes' => ['existing_asset', 'procurement', 'direct_purchase', 'grant', 'mutation', 'other'],
            'conditions' => ['good', 'minor_damage', 'damaged', 'under_repair', 'unknown'],
            'statuses' => ['active', 'inactive', 'in_calibration', 'in_repair', 'not_usable', 'lost', 'disposed'],
        ];
    }

    private function validatedAsset(Request $request, ?Asset $asset = null): array
    {
        $this->normalizeMoneyInputs($request, ['acquisition_value']);

        return $request->validate([
            'asset_code' => ['nullable', 'string', 'max:255', Rule::unique('assets', 'asset_code')->ignore($asset)],
            'asset_category_id' => ['nullable', 'exists:asset_categories,id'],
            'asset_location_id' => ['nullable', 'exists:asset_locations,id'],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'specification' => ['nullable', 'string'],
            'acquisition_date' => ['nullable', 'date'],
            'acquisition_value' => ['nullable', 'numeric', 'min:0'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'source_type' => ['required', Rule::in(['existing_asset', 'procurement', 'direct_purchase', 'grant', 'mutation', 'other'])],
            'condition' => ['required', Rule::in(['good', 'minor_damage', 'damaged', 'under_repair', 'unknown'])],
            'status' => ['required', Rule::in(['active', 'inactive', 'in_calibration', 'in_repair', 'not_usable', 'lost', 'disposed'])],
            'calibration_interval_months' => ['nullable', 'integer', 'min:1'],
            'last_calibration_date' => ['nullable', 'date'],
            'next_calibration_date' => ['nullable', 'date'],
            'inspection_interval_months' => ['nullable', 'integer', 'min:1'],
            'last_inspection_date' => ['nullable', 'date'],
            'next_inspection_date' => ['nullable', 'date'],
            'responsible_user_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
            'document' => ['nullable', 'file', 'max:5120'],
            'document_type' => ['nullable', 'string', 'max:255'],
            'document_notes' => ['nullable', 'string'],
        ]);
    }

    private function mergeBooleans(Request $request, array $validated): array
    {
        $validated['is_measuring_equipment'] = $request->boolean('is_measuring_equipment');
        $validated['requires_calibration'] = $request->boolean('requires_calibration');
        $validated['requires_periodic_inspection'] = $request->boolean('requires_periodic_inspection');
        unset($validated['document'], $validated['document_type'], $validated['document_notes']);

        return $validated;
    }

    private function storeDocument(Request $request, Asset $asset): void
    {
        if (! $request->hasFile('document')) {
            return;
        }

        $file = $request->file('document');
        AssetDocument::create([
            'asset_id' => $asset->id,
            'document_type' => $request->input('document_type', 'other'),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $file->store('assets/documents', 'public'),
            'notes' => $request->input('document_notes'),
            'uploaded_by' => $request->user()?->id,
        ]);
    }
}
