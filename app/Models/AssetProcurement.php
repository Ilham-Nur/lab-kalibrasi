<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetProcurement extends Model
{
    protected $fillable = [
        'procurement_number',
        'requested_by',
        'request_date',
        'department',
        'purpose',
        'total_estimated_cost',
        'status',
        'current_approval_level',
        'supervisor_status',
        'finance_status',
        'director_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'request_date' => 'date',
            'total_estimated_cost' => 'decimal:2',
            'current_approval_level' => 'integer',
        ];
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AssetProcurementItem::class, 'procurement_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(AssetProcurementApproval::class, 'procurement_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(AssetReceipt::class, 'procurement_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'procurement_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AssetDocument::class, 'procurement_id');
    }
}
