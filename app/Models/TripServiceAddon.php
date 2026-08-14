<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripServiceAddon extends Model
{
    protected $fillable = [
        'trip_service_id',
        'service_ids',
        'description',
        'amount',
    ];

    protected $casts = [
        'service_ids' => 'array',
        'amount' => 'decimal:2',
    ];

    public function getServiceNamesAttribute(): array
    {
        if (!$this->service_ids || !is_array($this->service_ids)) {
            return [];
        }
        return Service::whereIn('id', $this->service_ids)->pluck('name')->toArray();
    }

    public function tripService(): BelongsTo
    {
        return $this->belongsTo(TripService::class);
    }
}
