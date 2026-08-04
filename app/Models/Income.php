<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Income extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'receipt_number',
        'income_type',
        'income_date',
        'project_id',
        'invoice_id',
        'customer_id',
        'payment_mode_id',
        'bank_id',
        'amount',
        'cheque_number',
        'cheque_date',
        'bank_name',
        'description',
        'attachment',
    ];

    protected $casts = [
        'income_date' => 'date',
        'cheque_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($income) {
            if (empty($income->receipt_number)) {
                $income->receipt_number = self::generateReceiptNumber();
            }
        });
    }

    public static function generateReceiptNumber(): string
    {
        $year = date('Y');
        $lastIncome = self::withTrashed()
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastIncome ? (int)substr($lastIncome->receipt_number, -4) + 1 : 1;
        return 'INC-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function paymentMode(): BelongsTo
    {
        return $this->belongsTo(PaymentMode::class);
    }

    public function getIncomeTypeDisplayAttribute(): string
    {
        return match ($this->income_type) {
            'project' => 'Project Income',
            'advance' => 'Advance Payment',
            'other' => 'Other Income',
            default => ucfirst($this->income_type),
        };
    }

    public function getPaymentModeDisplayAttribute(): string
    {
        return $this->paymentMode->name ?? '-';
    }
}
