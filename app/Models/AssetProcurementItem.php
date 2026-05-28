<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetProcurementItem extends Model
{
    protected $fillable = [
        'procurement_id',
        'item_name',
        'specification',
        'quantity',
        'unit',
        'estimated_unit_price',
        'estimated_total_price',
        'supplier_candidate',
        'reason',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'estimated_unit_price' => 'decimal:2',
            'estimated_total_price' => 'decimal:2',
        ];
    }

    public function procurement(): BelongsTo
    {
        return $this->belongsTo(AssetProcurement::class, 'procurement_id');
    }

    public function receiptItems(): HasMany
    {
        return $this->hasMany(AssetReceiptItem::class, 'procurement_item_id');
    }
}
