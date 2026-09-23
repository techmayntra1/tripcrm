<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Company extends Model
{
    use SoftDeletes;

    public const COUNTRY_INDIA = 'india';
    public const COUNTRY_UAE = 'uae';

    public const COUNTRIES = [
        self::COUNTRY_INDIA => 'India',
        self::COUNTRY_UAE => 'UAE',
    ];

    /** All 28 Indian states, alphabetical. */
    public const INDIAN_STATES = [
        'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Goa', 'Gujarat',
        'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh',
        'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab',
        'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh',
        'Uttarakhand', 'West Bengal',
    ];

    /** All 8 Indian union territories, alphabetical. */
    public const INDIAN_UNION_TERRITORIES = [
        'Andaman and Nicobar Islands', 'Chandigarh',
        'Dadra and Nagar Haveli and Daman and Diu', 'Delhi',
        'Jammu and Kashmir', 'Ladakh', 'Lakshadweep', 'Puducherry',
    ];

    /** All 7 emirates of the UAE. */
    public const UAE_EMIRATES = [
        'Abu Dhabi', 'Ajman', 'Dubai', 'Fujairah', 'Ras Al Khaimah', 'Sharjah', 'Umm Al Quwain',
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

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    // Inlined as base64 so dompdf renders it without remote fetches or the storage symlink.
    public function getLogoDataUriAttribute(): ?string
    {
        if (!$this->logo || !Storage::disk('public')->exists($this->logo)) {
            return null;
        }

        $mime = Storage::disk('public')->mimeType($this->logo) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode(Storage::disk('public')->get($this->logo));
    }

}
