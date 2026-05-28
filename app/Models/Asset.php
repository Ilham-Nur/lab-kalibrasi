<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Asset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'asset_code',
        'asset_category_id',
        'asset_location_id',
        'procurement_id',
        'receipt_id',
        'name',
        'brand',
        'model',
        'serial_number',
        'specification',
        'acquisition_date',
        'acquisition_value',
        'supplier_name',
        'source_type',
        'condition',
        'status',
        'is_measuring_equipment',
        'requires_calibration',
        'calibration_interval_months',
        'last_calibration_date',
        'next_calibration_date',
        'requires_periodic_inspection',
        'inspection_interval_months',
        'last_inspection_date',
        'next_inspection_date',
        'responsible_user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'acquisition_value' => 'decimal:2',
            'is_measuring_equipment' => 'boolean',
            'requires_calibration' => 'boolean',
            'calibration_interval_months' => 'integer',
            'last_calibration_date' => 'date',
            'next_calibration_date' => 'date',
            'requires_periodic_inspection' => 'boolean',
            'inspection_interval_months' => 'integer',
            'last_inspection_date' => 'date',
            'next_inspection_date' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(AssetLocation::class, 'asset_location_id');
    }

    public function procurement(): BelongsTo
    {
        return $this->belongsTo(AssetProcurement::class, 'procurement_id');
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(AssetReceipt::class, 'receipt_id');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AssetDocument::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(AssetStatusLog::class);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(AssetInspection::class);
    }

    public function calibrations(): HasMany
    {
        return $this->hasMany(AssetCalibration::class);
    }

    protected function photoUrl(): Attribute
    {
        return Attribute::get(function () {
            $photo = $this->documents()->where('document_type', 'asset_photo')->latest()->first();

            return $photo ? Storage::url($photo->file_path) : null;
        });
    }
}
