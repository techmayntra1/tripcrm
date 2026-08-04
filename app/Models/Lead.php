<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'mobile',
        'email',
        'work_type',
        'work_lead',
        'budget',
        'final_budget',
        'lost_to',
        'city',
        'address',
        'notes',
        'status',
        'converted_at',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'final_budget' => 'decimal:2',
        'converted_at' => 'datetime',
        'work_type' => 'array',
    ];

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(LeadUpdate::class);
    }

    public function upcomingMeetings()
    {
        return $this->meetings()
            ->where('meeting_at', '>=', now())
            ->whereIn('status', ['scheduled', 'rescheduled'])
            ->orderBy('meeting_at');
    }

    public function isConverted(): bool
    {
        return $this->status === 'converted';
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'new' => 'secondary',
            'contacted' => 'info',
            'qualified' => 'warning',
            'negotiation' => 'primary',
            'won', 'converted' => 'success',
            'lost' => 'danger',
            default => 'secondary',
        };
    }
}
