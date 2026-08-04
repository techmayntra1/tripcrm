<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryAdvanceDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_advance_id',
        'expense_id',
        'deduction_amount',
        'deduction_date',
    ];

    protected $casts = [
        'deduction_date' => 'date',
        'deduction_amount' => 'decimal:2',
    ];

    public function salaryAdvance()
    {
        return $this->belongsTo(SalaryAdvance::class);
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }
}
