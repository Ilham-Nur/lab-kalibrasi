<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;

class AssetReportController extends Controller
{
    public function index()
    {
        $assets = Asset::with(['category', 'location'])
            ->when(request('asset_category_id'), fn ($query, $value) => $query->where('asset_category_id', $value))
            ->when(request('asset_location_id'), fn ($query, $value) => $query->where('asset_location_id', $value))
            ->when(request('status'), fn ($query, $value) => $query->where('status', $value))
            ->when(request('condition'), fn ($query, $value) => $query->where('condition', $value))
            ->when(request('source_type'), fn ($query, $value) => $query->where('source_type', $value))
            ->when(request('calibration_expired'), fn ($query) => $query->where('requires_calibration', true)->whereDate('next_calibration_date', '<', now()))
            ->when(request('inspection_overdue'), fn ($query) => $query->where('requires_periodic_inspection', true)->whereDate('next_inspection_date', '<', now()))
            ->orderBy('asset_code')
            ->paginate(15)
            ->withQueryString();

        return view('assets.reports.index', [
            'assets' => $assets,
            'categories' => AssetCategory::orderBy('name')->get(),
            'locations' => AssetLocation::orderBy('name')->get(),
            'statuses' => ['active', 'inactive', 'in_calibration', 'in_repair', 'not_usable', 'lost', 'disposed'],
            'conditions' => ['good', 'minor_damage', 'damaged', 'under_repair', 'unknown'],
            'sourceTypes' => ['existing_asset', 'procurement', 'direct_purchase', 'grant', 'mutation', 'other'],
        ]);
    }

    public function exportPdf()
    {
        return back()->with('error', 'Export PDF belum diaktifkan karena project belum memiliki package PDF.');
    }

    public function exportExcel()
    {
        return back()->with('error', 'Export Excel belum diaktifkan karena project belum memiliki package Excel.');
    }
}
