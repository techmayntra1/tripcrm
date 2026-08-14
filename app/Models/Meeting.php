<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Meeting extends Model
{
    use SoftDeletes;

    public static function getTimeConflict($meetingAt, $excludeId = null): ?self
    {
        if (empty($meetingAt)) {
            return null;
        }

        try {
            $newDateTime = Carbon::parse($meetingAt);
            $meetingDate = $newDateTime->toDateString();

            $query = self::whereDate('meeting_at', $meetingDate);

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            foreach ($query->get() as $meeting) {
                $existingDateTime = Carbon::parse($meeting->meeting_at);
                
                if ($newDateTime->toDateString() !== $existingDateTime->toDateString()) {
                    continue;
                }

                $diffInMinutes = abs($newDateTime->diffInMinutes($existingDateTime));

                if ($diffInMinutes < 60) {
                    return $meeting;
                }
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    protected $fillable = [
        'lead_id',
        'customer_id',
        'vendor_id',
        'trip_id',
        'purpose_id',
        'title',
        'meeting_at',
        'location',
        'description',
        'status',
        'outcome',
        'assigned_to',
        'other_attendee',
    ];

    protected $casts = [
        'meeting_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function purpose(): BelongsTo
    {
        return $this->belongsTo(MeetingPurpose::class, 'purpose_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(MeetingUpdate::class)->orderBy('created_at', 'desc');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            'rescheduled' => 'warning',
            default => 'secondary',
        };
    }

    public function scopeUpcoming($query)
    {
        return $query->where('meeting_at', '>=', now())
            ->whereIn('status', ['scheduled', 'rescheduled'])
            ->orderBy('meeting_at');
    }
}
