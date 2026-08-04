<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'categories',
        'contact_person',
        'mobile',
        'email',
        'gst_number',
        'pan_number',
        'opening_balance',
        'address',
        'bank_name',
        'account_number',
        'ifsc_code',
        'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'categories' => 'array',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function getCategoryNamesAttribute(): array
    {
        if (!$this->categories || !is_array($this->categories)) {
            return [];
        }

        return VendorCategory::whereIn('id', $this->categories)
            ->pluck('name')
            ->toArray();
    }

    public function getTotalPurchaseAttribute(): float
    {
        return $this->expenses()->sum('grand_total');
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->expenses()->sum('paid_amount');
    }

    public function getOutstandingAttribute(): float
    {
        return $this->opening_balance + $this->total_purchase - $this->total_paid;
    }
}
