<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use SoftDeletes;
    protected $table = 'staff';

    protected $fillable = [
        'name',
        'mobile',
        'email',
        'role',
        'position_id',
        'joining_date',
        'aadhar_number',
        'pan_number',
        'address',
        'salary_type',
        'salary_amount',
        'overtime_rate',
        'bank_name',
        'account_number',
        'ifsc_code',
        'photo',
        'pan_card',
        'aadhar_front',
        'aadhar_back',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'salary_amount' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'assigned_staff_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(StaffPosition::class, 'position_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function salaryAdvances(): HasMany
    {
        return $this->hasMany(SalaryAdvance::class);
    }

    public function activeAdvances(): HasMany
    {
        return $this->hasMany(SalaryAdvance::class)->where('status', 'active');
    }

    public function getTotalPendingAdvanceAttribute(): float
    {
        return $this->salaryAdvances()->where('status', 'active')->sum('remaining_amount');
    }

    public function getPositionNameAttribute(): string
    {
        return $this->position?->name ?? '-';
    }

    public function getTotalSalaryPaidAttribute(): float
    {
        return $this->expenses()->where('expense_type', 'salary')->sum('grand_total');
    }

}
