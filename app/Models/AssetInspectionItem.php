<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetInspectionItem extends Model
{
    protected $fillable = ['inspection_id', 'checklist_name', 'result', 'notes'];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(AssetInspection::class, 'inspection_id');
    }
}
