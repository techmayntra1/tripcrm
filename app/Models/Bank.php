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
        'country',
        'account_number',
        'account_holder',
        'ifsc_code',
        'iban',
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

    public function getIsUaeAttribute(): bool
    {
        return $this->country === Company::COUNTRY_UAE;
    }

    public function getCountryLabelAttribute(): string
    {
        return Company::COUNTRIES[$this->country] ?? 'India';
    }

    public function getCurrencyCodeAttribute(): string
    {
        return currencyCode($this->country);
    }

    public function getCurrencySymbolAttribute(): string
    {
        return currencySymbol($this->country);
    }

    // IFSC for Indian accounts, IBAN for UAE accounts.
    public function getBankCodeLabelAttribute(): string
    {
        return $this->is_uae ? 'IBAN' : 'IFSC';
    }

    public function getBankCodeAttribute(): ?string
    {
        return $this->is_uae ? $this->iban : $this->ifsc_code;
    }

    public function scopeActive($query)
    {
        return $query;
    }

    public function scopeForCountry($query, ?string $country)
    {
        return $country ? $query->where('country', $country) : $query;
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('bank_name');
    }
}
