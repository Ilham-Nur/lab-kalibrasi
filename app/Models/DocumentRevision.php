<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'document_id',
    'document_code',
    'title',
    'description',
    'status',
    'section_ids',
    'revision_number',
    'effective_date',
    'original_file_path',
    'pdf_file_path',
    'original_file_type',
    'conversion_status',
    'notes',
    'created_by',
])]
class DocumentRevision extends Model
{
    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'section_ids' => 'array',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getOriginalUrlAttribute(): ?string
    {
        return $this->original_file_path ? Storage::url($this->original_file_path) : null;
    }

    public function getPdfUrlAttribute(): ?string
    {
        return $this->pdf_file_path ? Storage::url($this->pdf_file_path) : null;
    }
}
