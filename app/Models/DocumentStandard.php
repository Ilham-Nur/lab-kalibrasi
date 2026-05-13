<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'order_number'])]
class DocumentStandard extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'standard_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(DocumentSection::class, 'standard_id');
    }
}
