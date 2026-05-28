<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetReceipt extends Model
{
    protected $fillable = [
        'procurement_id',
        'receipt_number',
        'received_by',
        'received_date',
        'supplier_name',
        'delivery_note_number',
        'invoice_number',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return ['received_date' => 'date'];
    }

    public function procurement(): BelongsTo
    {
        return $this->belongsTo(AssetProcurement::class, 'procurement_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AssetReceiptItem::class, 'receipt_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'receipt_id');
    }
}
