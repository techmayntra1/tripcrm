<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TermTemplate extends Model
{
    use SoftDeletes;

    const TYPE_TERMS = 'terms';
    const TYPE_PAYMENT = 'payment';

    protected $fillable = [
        'type',
        'name',
        'content',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Active templates of a type, default first.
     */
    public static function optionsFor(string $type)
    {
        return static::ofType($type)->orderByDesc('is_default')->ordered()->get();
    }

    public static function defaultContent(string $type): ?string
    {
        return static::ofType($type)->where('is_default', true)->value('content');
    }
}
