<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'price',
        'admin_price',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'admin_price' => 'decimal:2',
    ];

    /**
     * A line item using a catalogue service may be priced above the service's master
     * price but never below it. Returns validation errors keyed "items.{i}.rate".
     */
    public static function rateFloorErrors(array $items): array
    {
        $ids = collect($items)->pluck('service_id')->filter()->unique();
        if ($ids->isEmpty()) {
            return [];
        }
        $prices = self::whereIn('id', $ids)->pluck('price', 'id');

        $errors = [];
        foreach ($items as $i => $item) {
            $price = $prices[$item['service_id'] ?? null] ?? null;
            if ($price !== null && (float) ($item['rate'] ?? 0) + 0.001 < (float) $price) {
                $errors["items.{$i}.rate"] = 'Rate for "' . ($item['service_name'] ?? 'service') . '" cannot be less than its service price ' . number_format((float) $price, 2) . '.';
            }
        }
        return $errors;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
