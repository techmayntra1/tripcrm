<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadStatus extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'color', 'sort_order'];

    public function scopeActive($query)
    {
        return $query->orderBy('sort_order');
    }

    public static function getOrderByName(string $statusName): int
    {
        $status = self::whereRaw('LOWER(name) = ?', [strtolower($statusName)])->first();
        return $status?->sort_order ?? 0;
    }

    public static function canProgressTo(string $currentStatus, string $newStatus): bool
    {
        $currentOrder = self::getOrderByName($currentStatus);
        $newOrder = self::getOrderByName($newStatus);

        return $newOrder >= $currentOrder;
    }
}
