<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    protected $fillable = [
        'employee_id', 'salary_period', 'basic_salary', 'allowance', 'overtime',
        'deduction', 'total_salary', 'payment_status', 'payment_date', 'note', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'allowance' => 'decimal:2',
            'overtime' => 'decimal:2',
            'deduction' => 'decimal:2',
            'total_salary' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
