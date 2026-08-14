<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trip extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'trip_number',
        'name',
        'customer_id',
        'company_id',
        'work_type',
        'start_date',
        'expected_end_date',
        'actual_end_date',
        'status',
        'site_address',
        'description',
        'budget',
        'gst_percent',
        'gst_inclusive',
        'gst_split',
        'gst_amount',
        'advance_received',
        'quotation_id',
        'assigned_staff_id',
        'assigned_staff_ids',
        'assigned_vendor_ids',
        'notes',
    ];

    protected $casts = [
        'work_type' => 'array',
        'assigned_staff_ids' => 'array',
        'assigned_vendor_ids' => 'array',
        'start_date' => 'date',
        'expected_end_date' => 'date',
        'actual_end_date' => 'date',
        'budget' => 'decimal:2',
        'gst_percent' => 'decimal:2',
        'gst_inclusive' => 'boolean',
        'gst_split' => 'boolean',
        'gst_amount' => 'decimal:2',
        'advance_received' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($trip) {
            if (empty($trip->trip_number)) {
                $trip->trip_number = self::generateTripNumber();
            }
        });
    }

    public static function generateTripNumber(): string
    {
        $year = date('Y');
        $prefix = 'TRP-' . $year . '-';

        $lastTrip = self::withTrashed()
            ->where('trip_number', 'like', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(trip_number, "-", -1) AS UNSIGNED) DESC')
            ->first();

        $sequence = $lastTrip ? (int)substr($lastTrip->trip_number, -4) + 1 : 1;
        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'assigned_staff_id');
    }

    public function getAssignedStaffListAttribute()
    {
        if (empty($this->assigned_staff_ids)) {
            return collect();
        }
        return Staff::whereIn('id', $this->assigned_staff_ids)->get();
    }

    public function getAssignedVendorListAttribute()
    {
        if (empty($this->assigned_vendor_ids)) {
            return collect();
        }
        return Vendor::whereIn('id', $this->assigned_vendor_ids)->get();
    }

    public function addons(): HasMany
    {
        return $this->hasMany(TripAddon::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(TripFile::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function tripServices(): HasMany
    {
        return $this->hasMany(TripService::class);
    }

    public function getAddOnTotalAttribute(): float
    {
        return $this->addons()->sum('amount');
    }

    public function getTotalWithGstAttribute(): float
    {
        // When GST is inclusive the budget already contains the GST; otherwise GST
        // is added on top of the base budget.
        return $this->gst_inclusive
            ? (float) $this->budget
            : (float) $this->budget + (float) $this->gst_amount;
    }

    public function getTotalBudgetAttribute(): float
    {
        // Trip value (what the customer owes) = budget incl. GST + add-ons.
        // Add-ons are treated as GST-free.
        return $this->total_with_gst + $this->add_on_total;
    }

    public function getTotalSpentAttribute(): float
    {
        // "Spent" = money actually paid out (not committed/unpaid amounts).
        //
        // Every money-out is a single Expense row, including service payments
        // (expense_type='service', carrying trip_id). Summing every expense's
        // paid_amount therefore captures the full cash-out with no double counting.
        return (float) $this->expenses()->sum('paid_amount');
    }

    public function getTotalIncomeAttribute(): float
    {
        return $this->incomes()->sum('amount');
    }

    public function getProfitAttribute(): float
    {
        return $this->total_income - $this->total_spent;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'planning' => 'secondary',
            'in_progress' => 'primary',
            'on_hold' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function getPendingToReceiveAttribute(): float
    {
        return $this->invoices()
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->sum('balance_due');
    }

    public function getPendingToGiveAttribute(): float
    {
        $expensesPending = $this->expenses()
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->selectRaw('SUM(grand_total - paid_amount) as pending')
            ->value('pending') ?? 0;

        return $expensesPending;
    }

    public function getServicePendingAttribute(): float
    {
        $total = 0;
        foreach ($this->tripServices as $ps) {
            $total += $ps->balance;
        }
        return $total;
    }

    public function hasPendingPayments(): bool
    {
        return $this->pending_to_receive > 0 || $this->pending_to_give > 0 || $this->service_pending > 0;
    }

    public function getPendingPaymentsSummary(): array
    {
        $pendingInvoices = $this->invoices()
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->get(['invoice_number', 'grand_total', 'balance_due', 'status']);

        $pendingExpenses = $this->expenses()
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->get(['expense_number', 'grand_total', 'paid_amount', 'payment_status']);

        $pendingServices = $this->tripServices()
            ->get()
            ->filter(fn($ps) => $ps->balance > 0);

        return [
            'pending_to_receive' => $this->pending_to_receive,
            'pending_to_give' => $this->pending_to_give,
            'service_pending' => $this->service_pending,
            'invoices' => $pendingInvoices,
            'expenses' => $pendingExpenses,
            'services' => $pendingServices,
        ];
    }
}
