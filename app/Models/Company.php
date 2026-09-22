<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    public const COUNTRY_INDIA = 'india';
    public const COUNTRY_UAE = 'uae';

    public const COUNTRIES = [
        self::COUNTRY_INDIA => 'India',
        self::COUNTRY_UAE => 'UAE',
    ];

    protected $fillable = [
        'name',
        'contact_person',
        'country',
        'gst_number',
        'pan_number',
        'vat_number',
        'address',
        'city',
        'state',
        'pincode',
        'phone',
        'email',
        'website',
        'logo',
        'quotation_number_series',
        'invoice_number_series',
    ];

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function banks(): HasMany
    {
        return $this->hasMany(Bank::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function activeBanks(): HasMany
    {
        return $this->hasMany(Bank::class);
    }

    public function primaryBank()
    {
        return $this->hasOne(Bank::class)->where('is_primary', true);
    }

    public function getTotalBankBalanceAttribute(): float
    {
        return $this->banks->sum('balance');
    }

    public function getIsUaeAttribute(): bool
    {
        return $this->country === self::COUNTRY_UAE;
    }

    public function getCountryLabelAttribute(): string
    {
        return self::COUNTRIES[$this->country] ?? 'India';
    }

    public function getCurrencyCodeAttribute(): string
    {
        return currencyCode($this->country);
    }

    public function getCurrencySymbolAttribute(): string
    {
        return currencySymbol($this->country);
    }

}
