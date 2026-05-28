<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCalibration;
use App\Models\User;
use App\Services\AssetNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AssetCalibrationController extends Controller
{
    public function index()
    {
        return view('assets.calibrations.index', [
            'calibrations' => AssetCalibration::with(['asset', 'evaluatedBy'])
                ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
                ->when(request('result'), fn ($query, $result) => $query->where('result', $result))
                ->when(request('date_from'), fn ($query, $date) => $query->whereDate('calibration_date', '>=', $date))
                ->when(request('date_to'), fn ($query, $date) => $query->whereDate('calibration_date', '<=', $date))
                ->latest('calibration_date')
                ->paginate(10)
                ->withQueryString(),
            'dueAssets' => Asset::where('requires_calibration', true)->whereDate('next_calibration_date', '<=', now()->addDays(30))->limit(8)->get(),
            'statuses' => ['scheduled', 'in_progress', 'completed', 'expired', 'cancelled'],
            'results' => ['pass', 'failed', 'limited_use', 'need_recalibration'],
        ]);
    }

    public function create()
    {
        return view('assets.calibrations.create', $this->formData(new AssetCalibration(['calibration_date' => now(), 'status' => 'completed', 'calibration_type' => 'internal'])));
    }

    public function store(Request $request, AssetNumberService $numberService)
    {
        $validated = $this->validatedCalibration($request);
        $asset = Asset::findOrFail($validated['asset_id']);
        if (! $asset->requires_calibration) {
            throw ValidationException::withMessages(['asset_id' => 'Hanya aset yang memerlukan kalibrasi yang bisa dibuatkan kalibrasi.']);
        }

        $calibration = DB::transaction(function () use ($request, $validated, $numberService) {
            if ($request->hasFile('file_certificate')) {
                $validated['file_certificate'] = $request->file('file_certificate')->store('assets/calibrations', 'public');
            }

            $calibration = AssetCalibration::create([
                'calibration_number' => $numberService->calibrationNumber(),
                'asset_id' => $validated['asset_id'],
                'calibration_date' => $validated['calibration_date'],
                'next_calibration_date' => $validated['next_calibration_date'] ?? null,
                'calibration_type' => $validated['calibration_type'],
                'calibration_provider' => $validated['calibration_provider'] ?? null,
                'certificate_number' => $validated['certificate_number'] ?? null,
                'result' => $validated['result'],
                'status' => $validated['status'],
                'file_certificate' => $validated['file_certificate'] ?? null,
                'evaluated_by' => $validated['evaluated_by'] ?? $request->user()?->id,
                'evaluation_notes' => $validated['evaluation_notes'] ?? null,
            ]);

            $this->syncResults($calibration, collect($validated['results'] ?? []));
            $this->updateAssetFromCalibration($request, $calibration);

            return $calibration;
        });

        return redirect()->route('assets.calibrations.show', $calibration)->with('success', 'Kalibrasi berhasil disimpan.');
    }

    public function show(AssetCalibration $calibration)
    {
        return view('assets.calibrations.show', ['calibration' => $calibration->load(['asset', 'evaluatedBy', 'results'])]);
    }

    public function edit(AssetCalibration $calibration)
    {
        return view('assets.calibrations.edit', $this->formData($calibration->load('results')));
    }

    public function update(Request $request, AssetCalibration $calibration)
    {
        $validated = $this->validatedCalibration($request, $calibration);
        $asset = Asset::findOrFail($validated['asset_id']);
        if (! $asset->requires_calibration) {
            throw ValidationException::withMessages(['asset_id' => 'Hanya aset yang memerlukan kalibrasi yang bisa dibuatkan kalibrasi.']);
        }

        DB::transaction(function () use ($request, $calibration, $validated) {
            if ($request->hasFile('file_certificate')) {
                $validated['file_certificate'] = $request->file('file_certificate')->store('assets/calibrations', 'public');
            }

            $calibration->update([
                'asset_id' => $validated['asset_id'],
                'calibration_date' => $validated['calibration_date'],
                'next_calibration_date' => $validated['next_calibration_date'] ?? null,
                'calibration_type' => $validated['calibration_type'],
                'calibration_provider' => $validated['calibration_provider'] ?? null,
                'certificate_number' => $validated['certificate_number'] ?? null,
                'result' => $validated['result'],
                'status' => $validated['status'],
                'file_certificate' => $validated['file_certificate'] ?? $calibration->file_certificate,
                'evaluated_by' => $validated['evaluated_by'] ?? $request->user()?->id,
                'evaluation_notes' => $validated['evaluation_notes'] ?? null,
            ]);

            $calibration->results()->delete();
            $this->syncResults($calibration, collect($validated['results'] ?? []));
            $this->updateAssetFromCalibration($request, $calibration);
        });

        return redirect()->route('assets.calibrations.show', $calibration)->with('success', 'Kalibrasi berhasil diperbarui.');
    }

    public function destroy(AssetCalibration $calibration)
    {
        $calibration->delete();

        return redirect()->route('assets.calibrations.index')->with('success', 'Kalibrasi berhasil dihapus.');
    }

    private function formData(AssetCalibration $calibration): array
    {
        return [
            'calibration' => $calibration,
            'assets' => Asset::where('requires_calibration', true)->orderBy('asset_code')->get(),
            'users' => User::orderBy('name')->get(),
            'types' => ['internal', 'external'],
            'statuses' => ['scheduled', 'in_progress', 'completed', 'expired', 'cancelled'],
            'results' => ['pass', 'failed', 'limited_use', 'need_recalibration'],
        ];
    }

    private function validatedCalibration(Request $request): array
    {
        return $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'calibration_date' => ['required', 'date'],
            'next_calibration_date' => ['nullable', 'date'],
            'calibration_type' => ['required', Rule::in(['internal', 'external'])],
            'calibration_provider' => ['nullable', 'string', 'max:255'],
            'certificate_number' => ['nullable', 'string', 'max:255'],
            'result' => ['required', Rule::in(['pass', 'failed', 'limited_use', 'need_recalibration'])],
            'status' => ['required', Rule::in(['scheduled', 'in_progress', 'completed', 'expired', 'cancelled'])],
            'file_certificate' => ['nullable', 'file', 'max:5120'],
            'evaluated_by' => ['nullable', 'exists:users,id'],
            'evaluation_notes' => ['nullable', 'string'],
            'results' => ['nullable', 'array'],
            'results.*.parameter' => ['nullable', 'string', 'max:255'],
            'results.*.nominal_value' => ['nullable', 'string', 'max:255'],
            'results.*.measured_value' => ['nullable', 'string', 'max:255'],
            'results.*.correction' => ['nullable', 'string', 'max:255'],
            'results.*.uncertainty' => ['nullable', 'string', 'max:255'],
            'results.*.tolerance' => ['nullable', 'string', 'max:255'],
            'results.*.result' => ['nullable', 'string', 'max:255'],
            'results.*.notes' => ['nullable', 'string'],
        ]);
    }

    private function syncResults(AssetCalibration $calibration, $results): void
    {
        foreach ($results->filter(fn ($item) => filled($item['parameter'] ?? null)) as $item) {
            $calibration->results()->create($item);
        }
    }

    private function updateAssetFromCalibration(Request $request, AssetCalibration $calibration): void
    {
        $asset = $calibration->asset;
        $oldStatus = $asset->status;
        $newStatus = $asset->status;

        if ($calibration->status === 'in_progress') {
            $newStatus = 'in_calibration';
        } elseif ($calibration->result === 'pass') {
            $newStatus = 'active';
        } elseif ($calibration->result === 'failed') {
            $newStatus = 'not_usable';
        } elseif ($calibration->result === 'need_recalibration') {
            $newStatus = 'in_calibration';
        }

        $asset->update([
            'status' => $newStatus,
            'last_calibration_date' => $calibration->calibration_date,
            'next_calibration_date' => $calibration->next_calibration_date ?: ($asset->calibration_interval_months ? $calibration->calibration_date->copy()->addMonths($asset->calibration_interval_months) : null),
        ]);

        if ($oldStatus !== $newStatus) {
            $asset->statusLogs()->create([
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'old_condition' => $asset->condition,
                'new_condition' => $asset->condition,
                'description' => "Update dari kalibrasi {$calibration->calibration_number}.",
                'changed_by' => $request->user()?->id,
            ]);
        }
    }
}
