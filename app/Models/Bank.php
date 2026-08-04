<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'company_id',
        'bank_name',
        'account_number',
        'account_holder',
        'ifsc_code',
        'branch',
        'account_type',
        'opening_balance',
        'is_primary',
        'is_protected',
        'sort_order',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'is_primary' => 'boolean',
        'is_protected' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($bank) {
            if ($bank->is_protected) {
                return false;
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function getTotalCreditAttribute(): float
    {
        return $this->incomes()->sum('amount');
    }

    public function getTotalDebitAttribute(): float
    {
        return $this->expenses()->sum('paid_amount');
    }

    public function getBalanceAttribute(): float
    {
        return ($this->opening_balance ?? 0) + $this->total_credit - $this->total_debit;
    }

    public function getCurrentBalanceAttribute(): float
    {
        return $this->balance;
    }

    public function getTotalIncomeAttribute(): float
    {
        return $this->incomes()->sum('amount');
    }

    public function getTotalExpenseAttribute(): float
    {
        return $this->expenses()->sum('paid_amount');
    }

    public function scopeActive($query)
    {
        return $query;
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('bank_name');
    }
}
