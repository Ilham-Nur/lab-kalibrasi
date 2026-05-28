<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AssetDocument extends Model
{
    protected $fillable = [
        'asset_id',
        'procurement_id',
        'receipt_id',
        'calibration_id',
        'inspection_id',
        'document_type',
        'file_name',
        'file_path',
        'notes',
        'uploaded_by',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function procurement(): BelongsTo
    {
        return $this->belongsTo(AssetProcurement::class, 'procurement_id');
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(AssetReceipt::class, 'receipt_id');
    }

    public function calibration(): BelongsTo
    {
        return $this->belongsTo(AssetCalibration::class, 'calibration_id');
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(AssetInspection::class, 'inspection_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
