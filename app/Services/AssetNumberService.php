<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class AssetNumberService
{
    public function assetCode(): string
    {
        return $this->generate(\App\Models\Asset::class, 'asset_code', 'AST');
    }

    public function procurementNumber(): string
    {
        return $this->generate(\App\Models\AssetProcurement::class, 'procurement_number', 'PRC');
    }

    public function receiptNumber(): string
    {
        return $this->generate(\App\Models\AssetReceipt::class, 'receipt_number', 'RCV');
    }

    public function inspectionNumber(): string
    {
        return $this->generate(\App\Models\AssetInspection::class, 'inspection_number', 'INS');
    }

    public function calibrationNumber(): string
    {
        return $this->generate(\App\Models\AssetCalibration::class, 'calibration_number', 'CAL');
    }

    /**
     * @param  class-string<Model>  $model
     */
    private function generate(string $model, string $column, string $prefix): string
    {
        $year = now()->format('Y');
        $latest = $model::query()
            ->where($column, 'like', "{$prefix}-{$year}-%")
            ->orderByDesc($column)
            ->value($column);

        $next = $latest ? ((int) substr($latest, -4)) + 1 : 1;

        return sprintf('%s-%s-%04d', $prefix, $year, $next);
    }
}
