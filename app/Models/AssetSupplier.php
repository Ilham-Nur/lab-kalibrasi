<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetSupplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'notes',
        'status',
    ];

    public function receipts(): HasMany
    {
        return $this->hasMany(AssetReceipt::class, 'asset_supplier_id');
    }

    public function procurements(): HasMany
    {
        return $this->hasMany(AssetProcurement::class, 'asset_supplier_id');
    }
}
