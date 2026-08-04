<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseType extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'sort_order'];

    public function scopeActive($query)
    {
        return $query->orderBy('sort_order');
    }
}
