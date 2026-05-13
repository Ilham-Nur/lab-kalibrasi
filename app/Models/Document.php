<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'category_id',
    'standard_id',
    'section_id',
    'title',
    'document_code',
    'description',
    'revision',
    'effective_date',
    'status',
    'file_path',
    'original_file_path',
    'preview_file_path',
    'original_file_type',
])]
class Document extends Model
{
    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    public function standard(): BelongsTo
    {
        return $this->belongsTo(DocumentStandard::class, 'standard_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(DocumentSection::class, 'section_id');
    }

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(DocumentSection::class, 'document_document_section')
            ->withTimestamps()
            ->orderBy('order_number');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(DocumentRevision::class)->orderByDesc('revision_number');
    }

    public function latestRevision(): HasOne
    {
        return $this->hasOne(DocumentRevision::class)->latestOfMany('revision_number');
    }

    public function getOriginalUrlAttribute(): ?string
    {
        return $this->original_file_path ? Storage::url($this->original_file_path) : null;
    }

    public function getPdfUrlAttribute(): ?string
    {
        return $this->preview_file_path ? Storage::url($this->preview_file_path) : null;
    }
}
