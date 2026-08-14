<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'expense_number',
        'expense_type',
        'expense_date',
        'payment_mode_id',
        'trip_id',
        'trip_service_id',
        'vendor_id',
        'staff_id',
        'category_id',
        'bank_id',
        'items',
        'sub_total',
        'gst_percentage',
        'gst_amount',
        'grand_total',
        'paid_amount',
        'payment_status',
        'attachment',
        'description',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'items' => 'array',
        'sub_total' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            if (empty($expense->expense_number)) {
                $expense->expense_number = self::generateExpenseNumber();
            }
        });
    }

    public static function generateExpenseNumber(): string
    {
        $year = date('Y');
        $lastExpense = self::withTrashed()
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastExpense ? (int)substr($lastExpense->expense_number, -4) + 1 : 1;
        return 'EXP-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function tripService(): BelongsTo
    {
        return $this->belongsTo(TripService::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function paymentMode(): BelongsTo
    {
        return $this->belongsTo(PaymentMode::class);
    }

    public function getExpenseTypeDisplayAttribute(): string
    {
        return match ($this->expense_type) {
            'trip' => 'Trip Expense',
            'vendor' => 'Vendor Payment',
            'general' => 'General Expense',
            'salary' => 'Salary Payment',
            'service' => 'Service Payment',
            default => ucfirst($this->expense_type),
        };
    }

    public function getPaymentModeDisplayAttribute(): string
    {
        return $this->paymentMode->name ?? '-';
    }

    public function getBalanceAttribute(): float
    {
        return $this->grand_total - $this->paid_amount;
    }

    public function updatePaymentStatus(): void
    {
        if ($this->paid_amount >= $this->grand_total) {
            $this->payment_status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'unpaid';
        }
        $this->save();
    }
}
