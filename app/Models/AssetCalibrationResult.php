<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetCalibrationResult extends Model
{
    protected $fillable = [
        'calibration_id',
        'parameter',
        'nominal_value',
        'measured_value',
        'correction',
        'uncertainty',
        'tolerance',
        'result',
        'notes',
    ];

    public function calibration(): BelongsTo
    {
        return $this->belongsTo(AssetCalibration::class, 'calibration_id');
    }
}
