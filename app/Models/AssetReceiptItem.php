<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetReceiptItem extends Model
{
    protected $fillable = [
        'receipt_id',
        'procurement_item_id',
        'item_name',
        'quantity_ordered',
        'quantity_received',
        'condition',
        'notes',
        'is_converted_to_asset',
    ];

    protected function casts(): array
    {
        return [
            'quantity_ordered' => 'decimal:2',
            'quantity_received' => 'decimal:2',
            'is_converted_to_asset' => 'boolean',
        ];
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(AssetReceipt::class, 'receipt_id');
    }

    public function procurementItem(): BelongsTo
    {
        return $this->belongsTo(AssetProcurementItem::class, 'procurement_item_id');
    }
}
