<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskUpdate extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'type',
        'notes',
        'old_value',
        'new_value',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'status_change' => 'bi-arrow-repeat',
            'date_change' => 'bi-calendar-event',
            'created' => 'bi-plus-circle',
            default => 'bi-sticky',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'status_change' => 'info',
            'date_change' => 'warning',
            'created' => 'success',
            default => 'primary',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'status_change' => 'Status Change',
            'date_change' => 'Date Change',
            'created' => 'Created',
            default => 'Note',
        };
    }
}
