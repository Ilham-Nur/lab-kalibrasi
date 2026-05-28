<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class AssetCalibration extends Model
{
    protected $fillable = [
        'calibration_number',
        'asset_id',
        'calibration_date',
        'next_calibration_date',
        'calibration_type',
        'calibration_provider',
        'certificate_number',
        'result',
        'status',
        'file_certificate',
        'evaluated_by',
        'evaluation_notes',
    ];

    protected function casts(): array
    {
        return [
            'calibration_date' => 'date',
            'next_calibration_date' => 'date',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function evaluatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function results(): HasMany
    {
        return $this->hasMany(AssetCalibrationResult::class, 'calibration_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AssetDocument::class, 'calibration_id');
    }

    public function getCertificateUrlAttribute(): ?string
    {
        return $this->file_certificate ? Storage::url($this->file_certificate) : null;
    }
}
