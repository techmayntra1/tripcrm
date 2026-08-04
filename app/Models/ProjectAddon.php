<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectAddon extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'amount',
        'status',
        'requested_date',
        'approved_date',
        'completed_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requested_date' => 'date',
        'approved_date' => 'date',
        'completed_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'in_progress' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }
}
