<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetInspection;
use App\Models\AssetProcurement;

class AssetDashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $soon = now()->addDays(30)->toDateString();

        return view('assets.dashboard', [
            'stats' => [
                'total_assets' => Asset::count(),
                'active_assets' => Asset::where('status', 'active')->count(),
                'inactive_assets' => Asset::where('status', 'inactive')->count(),
                'damaged_assets' => Asset::whereIn('condition', ['damaged', 'minor_damage'])->count(),
                'not_usable_assets' => Asset::where('status', 'not_usable')->count(),
                'in_calibration_assets' => Asset::where('status', 'in_calibration')->count(),
                'calibration_due_soon' => Asset::where('requires_calibration', true)->whereBetween('next_calibration_date', [$today, $soon])->count(),
                'calibration_expired' => Asset::where('requires_calibration', true)->whereDate('next_calibration_date', '<', $today)->count(),
                'inspection_overdue' => Asset::where('requires_periodic_inspection', true)->whereDate('next_inspection_date', '<', $today)->count(),
                'waiting_approval' => AssetProcurement::whereIn('status', ['waiting_supervisor', 'waiting_finance', 'waiting_director'])->count(),
            ],
            'calibrationDueAssets' => Asset::with(['category', 'location'])
                ->where('requires_calibration', true)
                ->whereDate('next_calibration_date', '<=', $soon)
                ->orderBy('next_calibration_date')
                ->limit(8)
                ->get(),
            'overdueInspectionAssets' => Asset::with(['category', 'location'])
                ->where('requires_periodic_inspection', true)
                ->whereDate('next_inspection_date', '<', $today)
                ->orderBy('next_inspection_date')
                ->limit(8)
                ->get(),
            'waitingProcurements' => AssetProcurement::with('requestedBy')
                ->whereIn('status', ['waiting_supervisor', 'waiting_finance', 'waiting_director'])
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }
}
