<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class JobDescription extends Model
{
    protected $fillable = [
        'division_id', 'position_id', 'title', 'description', 'target_work',
        'direct_supervisor_id', 'status',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function directSupervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'direct_supervisor_id');
    }
}
