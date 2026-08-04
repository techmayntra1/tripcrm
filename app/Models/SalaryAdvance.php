<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'amount',
        'remaining_amount',
        'advance_date',
        'payment_mode_id',
        'bank_id',
        'reference_number',
        'reason',
        'status',
    ];

    protected $casts = [
        'advance_date' => 'date',
        'amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function paymentMode()
    {
        return $this->belongsTo(PaymentMode::class);
    }

    public function deductions()
    {
        return $this->hasMany(SalaryAdvanceDeduction::class);
    }

    public function getTotalDeductedAttribute()
    {
        return $this->amount - $this->remaining_amount;
    }

    public function getPaymentModeDisplayAttribute()
    {
        return $this->paymentMode->name ?? '-';
    }

    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'pending' => 'Pending',
            'active' => 'Active',
            'completed' => 'Completed',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'active' => 'info',
            'completed' => 'success',
            default => 'secondary',
        };
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function deduct($amount, $expenseId)
    {
        $deductAmount = min($amount, $this->remaining_amount);

        if ($deductAmount > 0) {
            SalaryAdvanceDeduction::create([
                'salary_advance_id' => $this->id,
                'expense_id' => $expenseId,
                'deduction_amount' => $deductAmount,
                'deduction_date' => now()->toDateString(),
            ]);

            $this->remaining_amount -= $deductAmount;

            if ($this->remaining_amount <= 0) {
                $this->remaining_amount = 0;
                $this->status = 'completed';
            }

            $this->save();
        }

        return $deductAmount;
    }
}
