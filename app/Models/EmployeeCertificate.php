<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EmployeeCertificate extends Model
{
    protected $fillable = [
        'employee_id', 'certificate_title', 'certificate_number', 'issuer', 'execution_date',
        'expired_date', 'certificate_type', 'file_path', 'description',
    ];

    protected function casts(): array
    {
        return [
            'execution_date' => 'date',
            'expired_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? Storage::url($this->file_path) : null;
    }
}
