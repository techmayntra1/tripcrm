<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'lead_id',
        'name',
        'mobile',
        'email',
        'work_type',
        'work_lead',
        'budget',
        'payment_type',
        'gst_number',
        'city_id',
        'city_other',
        'address',
        'notes',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'work_type' => 'array',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->orderBy('created_at', 'desc');
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(CustomerUpdate::class);
    }

    public function upcomingMeetings()
    {
        return $this->meetings()
            ->where('meeting_at', '>=', now())
            ->whereIn('status', ['scheduled', 'rescheduled'])
            ->orderBy('meeting_at');
    }

    public function getCityNameAttribute(): string
    {
        return $this->city?->name ?? $this->city_other ?? '-';
    }

    /**
     * Total value billed to the customer, taken from the budget of their projects.
     * Used for the "Total Invoiced" card. Falls back to invoice totals only where
     * no projects exist is intentionally not applied — projects are the source of truth.
     */
    public function getTotalInvoicedAttribute(): float
    {
        return (float) $this->projects()->sum('budget');
    }

    /**
     * Money actually received, taken from the Income ledger (the real money-in record),
     * reached via the customer's projects as well as any income booked directly to the
     * customer. Distinct income rows so a row carrying both links is not counted twice.
     */
    public function getTotalReceivedAttribute(): float
    {
        $projectIds = $this->projects()->pluck('id');

        return (float) Income::where(function ($query) use ($projectIds) {
            $query->whereIn('project_id', $projectIds)
                ->orWhere('customer_id', $this->id);
        })->sum('amount');
    }

    /**
     * Outstanding balance: total billed minus what has been received. Clamped at 0 so an
     * over-received customer reads as nothing receivable rather than a negative figure.
     */
    public function getReceivableAttribute(): float
    {
        return max($this->total_invoiced - $this->total_received, 0);
    }

    /**
     * Total money actually received from this customer, taken from the Income
     * table (matched by customer_id OR by the customer's projects). Lifetime.
     */
    public function getTotalIncomeAttribute(): float
    {
        $projectIds = $this->projects()->pluck('id');

        return Income::where('customer_id', $this->id)
            ->orWhereIn('project_id', $projectIds)
            ->sum('amount');
    }

    /**
     * Agreed value of all the customer's projects (budget + add-ons). Lifetime.
     */
    public function getProjectValueAttribute(): float
    {
        return $this->projects->sum(fn($project) => $project->total_budget);
    }

    /**
     * Still to be collected: project value minus income received.
     */
    public function getIncomeReceivableAttribute(): float
    {
        return $this->project_value - $this->total_income;
    }
}
