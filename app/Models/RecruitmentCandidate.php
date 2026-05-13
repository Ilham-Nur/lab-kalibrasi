<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class RecruitmentCandidate extends Model
{
    protected $fillable = [
        'recruitment_request_id', 'name', 'email', 'no_hp', 'address', 'last_education',
        'experience', 'expected_salary', 'cv_file', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return ['expected_salary' => 'decimal:2'];
    }

    public function recruitmentRequest(): BelongsTo
    {
        return $this->belongsTo(RecruitmentRequest::class);
    }

    public function getCvUrlAttribute(): ?string
    {
        return $this->cv_file ? Storage::url($this->cv_file) : null;
    }
}
