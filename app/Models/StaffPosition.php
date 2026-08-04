<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffPosition extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'sort_order',
    ];

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class, 'position_id');
    }

    public function scopeActive($query)
    {
        return $query;
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
