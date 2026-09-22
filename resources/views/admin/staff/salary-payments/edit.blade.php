@extends('layouts.app')
@section('title', 'Edit Salary - ' . $staff->name)
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-cash-stack icon-gradient bg-warning"></i>
            </div>
            <div>
                Edit Salary Payment
                <div class="page-title-subheading">{{ $staff->name }} - {{ $staff->position_name }} | {{ $payment->expense_number }}</div>
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.staff.salary-payments.index', $staff) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('admin.staff.salary-payments.update', [$staff, $payment]) }}" method="POST" id="salaryForm" novalidate>
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-lg-8">
            <div class="main-card mb-3 card">
                <div class="card-header">
                    <i class="bi bi-receipt me-2"></i> Salary Details
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ old('payment_date', $payment->expense_date->format('Y-m-d')) }}" required>
                            <div class="invalid-feedback">Please select a payment date</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_mode_id" id="payment_mode_id" class="form-select" required>
                                <option value="">Select Mode</option>
                                @foreach($paymentModes as $mode)
                                <option value="{{ $mode->id }}" data-slug="{{ $mode->slug }}" {{ old('payment_mode_id', $payment->payment_mode_id) == $mode->id ? 'selected' : '' }}>{{ $mode->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a payment mode</div>
                        </div>
                    </div>

                    <div class="row mb-3" id="bank_section">
                        <div class="col-md-6">
                            <label class="form-label">Bank Account <span class="text-danger">*</span></label>
                            <select name="bank_id" id="bank_id" class="form-select js-currency-source" data-currency-default="₹" required>
                                <option value="">Select Bank Account</option>
                                @foreach($banks as $bank)
                                <option value="{{ $bank->id }}" data-currency="{{ $bank->currency_symbol }}" {{ old('bank_id', $payment->bank_id) == $bank->id ? 'selected' : '' }}>{{ $bank->bank_name }} - {{ $bank->account_number }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a bank account</div>
                        </div>
                        <input type="hidden" id="cash_account_id" value="{{ $cashAccount->id ?? '' }}">
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3"><i class="bi bi-plus-circle me-2"></i>Earnings</h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Base Salary <span class="text-danger">*</span></label>
                            <input type="number" name="base_salary" id="base_salary" class="form-control" value="{{ old('base_salary', $baseSalary) }}" step="1" min="0" max="999999999" required inputmode="numeric">
                            <div class="invalid-feedback">Please enter base salary</div>
                            <small class="text-muted">Monthly: {{ formatMoney($staff->salary_amount) }}</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Overtime</label>
                            <input type="number" name="overtime_amount" id="overtime_amount" class="form-control" value="{{ old('overtime_amount', $overtimeAmount) }}" placeholder="0" step="1" min="0" max="999999999" inputmode="numeric">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bonus / Incentive</label>
                            <input type="number" name="bonus" id="bonus" class="form-control" value="{{ old('bonus', $bonus) }}" placeholder="0" step="1" min="0" max="999999999" inputmode="numeric">
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3"><i class="bi bi-dash-circle me-2"></i>Deductions</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Advance Deduction</label>
                            <input type="number" name="advance_deduction" id="advance_deduction" class="form-control" value="{{ old('advance_deduction', $existingDeduction) }}" placeholder="0" step="1" min="0" max="{{ $totalPendingAdvance }}" inputmode="numeric">
                            @if($totalPendingAdvance > 0)
                            <small class="text-warning">Available advance (incl. this payment's deduction): {{ formatMoney($totalPendingAdvance) }}</small>
                            @else
                            <small class="text-muted">No pending advance</small>
                            @endif
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Any additional notes...">{{ old('notes', $payment->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="main-card mb-3 card">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-calculator me-2"></i> Payment Summary
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td>Base Salary</td>
                                <td class="text-end" id="summary_base">{{ formatMoney($baseSalary) }}</td>
                            </tr>
                            <tr>
                                <td>Overtime</td>
                                <td class="text-end" id="summary_overtime">{{ formatMoney($overtimeAmount) }}</td>
                            </tr>
                            <tr>
                                <td>Bonus</td>
                                <td class="text-end" id="summary_bonus">{{ formatMoney($bonus) }}</td>
                            </tr>
                            <tr class="table-secondary">
                                <td><strong>Gross Salary</strong></td>
                                <td class="text-end"><strong id="summary_gross">{{ formatMoney($baseSalary + $overtimeAmount + $bonus) }}</strong></td>
                            </tr>
                            <tr>
                                <td>Advance Deduction</td>
                                <td class="text-end text-danger" id="summary_advance">- {{ formatMoney($existingDeduction) }}</td>
                            </tr>
                            <tr class="table-success">
                                <td><strong>Net Payable</strong></td>
                                <td class="text-end"><strong id="summary_net">{{ formatMoney($baseSalary + $overtimeAmount + $bonus - $existingDeduction) }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="bi bi-check-lg me-1"></i> Update Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMode = document.getElementById('payment_mode_id');
    const bankSection = document.getElementById('bank_section');
    const bankSelect = document.getElementById('bank_id');
    const cashAccountId = document.getElementById('cash_account_id')?.value;

    function toggleBankSection() {
        const selectedOption = paymentMode.options[paymentMode.selectedIndex];
        const slug = selectedOption?.getAttribute('data-slug');

        if (slug === 'cash') {
            bankSection.style.display = 'none';
            bankSelect.removeAttribute('required');
            if (!bankSelect.value || bankSelect.value !== cashAccountId) {
                bankSelect.value = cashAccountId;
            }
        } else {
            bankSection.style.display = '';
            bankSelect.setAttribute('required', 'required');
            if (bankSelect.value === cashAccountId) {
                bankSelect.value = '';
            }
        }
    }

    paymentMode.addEventListener('change', toggleBankSection);
    toggleBankSection();

    const baseSalary = document.getElementById('base_salary');
    const overtimeAmount = document.getElementById('overtime_amount');
    const bonus = document.getElementById('bonus');
    const advanceDeduction = document.getElementById('advance_deduction');

    const summaryBase = document.getElementById('summary_base');
    const summaryOvertime = document.getElementById('summary_overtime');
    const summaryBonus = document.getElementById('summary_bonus');
    const summaryGross = document.getElementById('summary_gross');
    const summaryAdvance = document.getElementById('summary_advance');
    const summaryNet = document.getElementById('summary_net');

    const pendingAdvance = {{ $totalPendingAdvance }};

    function formatMoney(amount) {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }

    function calculateSummary() {
        const base = parseFloat(baseSalary.value) || 0;
        const overtime = parseFloat(overtimeAmount.value) || 0;
        const bonusAmt = parseFloat(bonus.value) || 0;
        const advDeduct = parseFloat(advanceDeduction.value) || 0;

        const gross = base + overtime + bonusAmt;
        const net = gross - advDeduct;

        summaryBase.textContent = formatMoney(base);
        summaryOvertime.textContent = formatMoney(overtime);
        summaryBonus.textContent = formatMoney(bonusAmt);
        summaryGross.textContent = formatMoney(gross);
        summaryAdvance.textContent = '- ' + formatMoney(advDeduct);
        summaryNet.textContent = formatMoney(net);

        if (net < 0) {
            summaryNet.classList.add('text-danger');
        } else {
            summaryNet.classList.remove('text-danger');
        }
    }

    advanceDeduction.addEventListener('input', function() {
        const value = parseFloat(this.value) || 0;
        if (value > pendingAdvance) {
            this.value = pendingAdvance;
        }
        calculateSummary();
    });

    [baseSalary, overtimeAmount, bonus].forEach(function(el) {
        el.addEventListener('input', calculateSummary);
    });

    calculateSummary();

    const form = document.getElementById('salaryForm');

    function validateSalaryForm() {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        const selectedPaymentOption = paymentMode.options[paymentMode.selectedIndex];
        const isCash = selectedPaymentOption?.getAttribute('data-slug') === 'cash';

        let isValid = true;

        const paymentDate = document.getElementById('payment_date');
        if (!paymentDate.value) {
            paymentDate.classList.add('is-invalid');
            isValid = false;
        }

        if (!paymentMode.value) {
            paymentMode.classList.add('is-invalid');
            isValid = false;
        }

        if (!isCash && !bankSelect.value) {
            bankSelect.classList.add('is-invalid');
            isValid = false;
        }

        if (!baseSalary.value || parseFloat(baseSalary.value) < 0) {
            baseSalary.classList.add('is-invalid');
            isValid = false;
        }

        return isValid;
    }

    form.addEventListener('submit', function(e) {
        if (!validateSalaryForm()) {
            e.preventDefault();
            e.stopPropagation();
        }
    });
});
</script>
@endpush
