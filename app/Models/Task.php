<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'project_id',
        'assignee_type',
        'assignee_id',
        'start_at',
        'due_at',
        'status_id',
        'location',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'due_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TaskStatus::class, 'status_id');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(TaskUpdate::class)->orderBy('created_at', 'desc');
    }

    public function getAssigneeAttribute()
    {
        if ($this->assignee_type === 'staff') {
            return Staff::find($this->assignee_id);
        }
        return Vendor::find($this->assignee_id);
    }

    public function getAssigneeNameAttribute(): string
    {
        $assignee = $this->assignee;
        return $assignee ? $assignee->name : '-';
    }

    public function getStatusNameAttribute(): string
    {
        return $this->status ? $this->status->name : '-';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status ? $this->status->color : 'secondary';
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_at', '>=', now())
            ->whereHas('status', function ($q) {
                $q->whereIn('name', ['Pending', 'In Progress']);
            })
            ->orderBy('start_at');
    }

    public function getIsOverdueAttribute(): bool
    {
        if (!$this->due_at) {
            return false;
        }
        return $this->due_at->isPast() && $this->status_name !== 'Completed';
    }
}
