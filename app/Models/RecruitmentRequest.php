<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class RecruitmentRequest extends Model
{
    protected $fillable = [
        'request_number', 'division_id', 'position_id', 'needed_count', 'reason',
        'employment_type', 'requested_by', 'request_date', 'status', 'description',
    ];

    protected function casts(): array
    {
        return ['request_date' => 'date'];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(RecruitmentCandidate::class);
    }
}
