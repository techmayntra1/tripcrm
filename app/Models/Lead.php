<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;
    // Dialling codes offered on the lead form. India and UAE first, then the common Gulf/overseas ones.
    public const COUNTRY_CODES = [
        '+91' => 'India (+91)',
        '+971' => 'UAE (+971)',
        '+966' => 'Saudi Arabia (+966)',
        '+974' => 'Qatar (+974)',
        '+965' => 'Kuwait (+965)',
        '+968' => 'Oman (+968)',
        '+973' => 'Bahrain (+973)',
        '+44' => 'UK (+44)',
        '+1' => 'USA / Canada (+1)',
    ];

    protected $fillable = [
        'name',
        'country_code',
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

    public function getFullMobileAttribute(): string
    {
        return trim(($this->country_code ?: '+91') . ' ' . $this->mobile);
    }

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
