<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetInspection extends Model
{
    protected $fillable = [
        'inspection_number',
        'asset_id',
        'inspection_date',
        'next_inspection_date',
        'inspected_by',
        'result',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'inspection_date' => 'date',
            'next_inspection_date' => 'date',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function inspectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AssetInspectionItem::class, 'inspection_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AssetDocument::class, 'inspection_id');
    }
}
