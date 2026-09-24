<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'invoice_number',
        'date',
        'due_date',
        'company_id',
        'customer_id',
        'trip_id',
        'quotation_id',
        'subject',
        'invoice_type',
        'invoice_pdf',
        'pdf_description',
        'items',
        'notes',
        'terms',
        'payment_terms',
        'subtotal',
        'discount',
        'gst_percent',
        'gst_inclusive',
        'gst_split',
        'gst',
        'grand_total',
        'amount_paid',
        'balance_due',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'gst' => 'decimal:2',
        'gst_inclusive' => 'boolean',
        'gst_split' => 'boolean',
        'grand_total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number) || $invoice->invoice_number === 'Auto-generated') {
                $invoice->invoice_number = self::generateInvoiceNumber($invoice->company_id);
            }
        });
    }

    public static function generateInvoiceNumber($companyId = null): string
    {
        if (!$companyId) {
            return 'INV-' . str_pad(self::withTrashed()->count() + 1, 3, '0', STR_PAD_LEFT);
        }

        $company = Company::find($companyId);
        if (!$company) {
            return 'INV-' . str_pad(self::withTrashed()->count() + 1, 3, '0', STR_PAD_LEFT);
        }

        $prefix = strtoupper($company->invoice_number_series ?? 'INV');

        $lastInvoice = self::withTrashed()
            ->where('company_id', $companyId)
            ->where('invoice_number', 'like', $prefix . '-%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(invoice_number, "-", -1) AS UNSIGNED) DESC')
            ->first();

        $sequence = 1;
        if ($lastInvoice) {
            $parts = explode('-', $lastInvoice->invoice_number);
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

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function updatePaymentStatus(): void
    {
        $totalPaid = $this->incomes()->sum('amount');
        $this->amount_paid = $totalPaid;
        $this->balance_due = $this->grand_total - $totalPaid;

        if ($this->grand_total > 0 && $this->balance_due <= 0) {
            $this->status = 'paid';
        } elseif ($totalPaid > 0) {
            $this->status = 'partial';
        } elseif ($this->due_date && $this->due_date->isPast()) {
            $this->status = 'overdue';
        } else {
            $this->status = 'sent';
        }

        $this->save();
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'sent' => 'info',
            'partial' => 'warning',
            'paid' => 'success',
            'overdue' => 'danger',
            'cancelled' => 'dark',
            default => 'secondary',
        };
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->balance_due > 0;
    }
}
