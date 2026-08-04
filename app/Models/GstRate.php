<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GstRate extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'percentage', 'sort_order'];

    protected $casts = [
        'percentage' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->orderBy('sort_order');
    }
}
