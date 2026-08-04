<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectServiceAddon extends Model
{
    protected $fillable = [
        'project_service_id',
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

    public function projectService(): BelongsTo
    {
        return $this->belongsTo(ProjectService::class);
    }
}
