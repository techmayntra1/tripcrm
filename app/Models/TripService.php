<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TripService extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'trip_id',
        'service_ids',
        'amount',
        'advance',
        'advance_bank_id',
        'due_date',
        'note',
    ];

    protected $casts = [
        'service_ids' => 'array',
        'amount' => 'decimal:2',
        'advance' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function advanceBank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'advance_bank_id');
    }

    public function addons(): HasMany
    {
        return $this->hasMany(TripServiceAddon::class);
    }

    public function getServiceNamesAttribute(): array
    {
        if (!$this->service_ids || !is_array($this->service_ids)) {
            return [];
        }
        return Service::whereIn('id', $this->service_ids)->pluck('name')->toArray();
    }

    public function getAddonsTotalAttribute(): float
    {
        return $this->addons()->sum('amount');
    }

    public function getTotalAmountAttribute(): float
    {
        return $this->amount + $this->addons_total;
    }

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->serviceExpenses()->sum('paid_amount');
    }

    public function getBalanceAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function serviceExpenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'trip_service_id');
    }

    public function getPaymentStatusAttribute(): string
    {
        if ($this->paid_amount >= $this->total_amount) {
            return 'paid';
        } elseif ($this->paid_amount > 0) {
            return 'partial';
        }
        return 'unpaid';
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->balance > 0;
    }
}
