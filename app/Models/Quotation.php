<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'quotation_number',
        'date',
        'valid_until',
        'company_id',
        'customer_id',
        'project_id',
        'subject',
        'quotation_type',
        'quotation_pdf',
        'pdf_description',
        'items',
        'subtotal',
        'discount',
        'gst_percent',
        'gst_inclusive',
        'gst_split',
        'gst',
        'grand_total',
        'terms',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'valid_until' => 'date',
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'gst' => 'decimal:2',
        'gst_inclusive' => 'boolean',
        'gst_split' => 'boolean',
        'grand_total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quotation) {
            if (empty($quotation->quotation_number) || $quotation->quotation_number === 'Auto-generated') {
                $quotation->quotation_number = self::generateQuotationNumber($quotation->company_id);
            }
        });
    }

    public static function generateQuotationNumber($companyId = null): string
    {
        if (!$companyId) {
            return 'QT-' . str_pad(self::withTrashed()->count() + 1, 3, '0', STR_PAD_LEFT);
        }

        $company = Company::find($companyId);
        if (!$company) {
            return 'QT-' . str_pad(self::withTrashed()->count() + 1, 3, '0', STR_PAD_LEFT);
        }

        $prefix = strtoupper($company->quotation_number_series ?? 'QT');

        $lastQuotation = self::withTrashed()
            ->where('company_id', $companyId)
            ->where('quotation_number', 'like', $prefix . '-%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(quotation_number, "-", -1) AS UNSIGNED) DESC')
            ->first();

        $sequence = 1;
        if ($lastQuotation) {
            $parts = explode('-', $lastQuotation->quotation_number);
            $sequence = (int)end($parts) + 1;
        }

        return $prefix . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class);
    }

    /**
     * The project this quotation was raised for (chosen on the form).
     * Distinct from project() above, which is the project created FROM this quotation.
     */
    public function selectedProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'sent' => 'info',
            'accepted' => 'success',
            'rejected' => 'danger',
            'expired' => 'warning',
            default => 'secondary',
        };
    }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    public function hasInvoice(): bool
    {
        if ($this->relationLoaded('invoices')) {
            return $this->invoices->isNotEmpty();
        }
        return $this->invoices()->exists();
    }
}
