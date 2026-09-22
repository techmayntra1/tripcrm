@extends('layouts.app')
@section('title', 'Make Advance - ' . $staff->name)
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-cash icon-gradient bg-warning"></i>
            </div>
            <div>
                Make Salary Advance
                <div class="page-title-subheading">{{ $staff->name }} - {{ $staff->position_name }}</div>
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

<form action="{{ route('admin.staff.advances.store', $staff) }}" method="POST" id="advanceForm" novalidate>
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <div class="main-card mb-3 card">
                <div class="card-header">
                    <i class="bi bi-cash me-2"></i> Advance Details
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Advance Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="amount" class="form-control" value="{{ old('amount') }}" step="1" min="1" max="999999999" required inputmode="numeric">
                            <div class="invalid-feedback">Please enter advance amount</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Advance Date <span class="text-danger">*</span></label>
                            <input type="date" name="advance_date" id="advance_date" class="form-control" value="{{ old('advance_date', date('Y-m-d')) }}" required>
                            <div class="invalid-feedback">Please select advance date</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_mode_id" id="payment_mode_id" class="form-select" required>
                                <option value="">Select Mode</option>
                                @foreach($paymentModes as $mode)
                                <option value="{{ $mode->id }}" data-slug="{{ $mode->slug }}" {{ old('payment_mode_id') == $mode->id ? 'selected' : '' }}>{{ $mode->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a payment mode</div>
                        </div>
                        <div class="col-md-6" id="bank_section">
                            <label class="form-label">Bank Account <span class="text-danger">*</span></label>
                            <select name="bank_id" id="bank_id" class="form-select js-currency-source" data-currency-default="₹" required>
                                <option value="">Select Bank Account</option>
                                @foreach($banks as $bank)
                                <option value="{{ $bank->id }}" data-currency="{{ $bank->currency_symbol }}" {{ old('bank_id') == $bank->id ? 'selected' : '' }}>{{ $bank->bank_name }} - {{ $bank->account_number }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a bank account</div>
                        </div>
                        <input type="hidden" id="cash_account_id" value="{{ $cashAccount->id ?? '' }}">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="Add description">{{ old('reason') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-lg me-1"></i> Make Advance
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="main-card mb-3 card">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-info-circle me-2"></i> Staff Info
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Name</span>
                        <strong>{{ $staff->name }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Role</span>
                        <span>{{ $staff->position_name }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Monthly Salary</span>
                        <span>{{ formatMoney($staff->salary_amount) }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Current Pending Advance</span>
                        <strong class="text-warning">{{ formatMoney($staff->total_pending_advance) }}</strong>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Note:</strong> This advance can be deducted from future salary payments.
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
            bankSelect.value = cashAccountId;
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

    // Form validation
    const form = document.getElementById('advanceForm');

    function validateAdvanceForm() {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        const selectedPaymentOption = paymentMode.options[paymentMode.selectedIndex];
        const isCash = selectedPaymentOption?.getAttribute('data-slug') === 'cash';

        let isValid = true;

        // Amount
        const amount = document.getElementById('amount');
        if (!amount.value || parseFloat(amount.value) < 1) {
            amount.classList.add('is-invalid');
            isValid = false;
        }

        // Advance date
        const advanceDate = document.getElementById('advance_date');
        if (!advanceDate.value) {
            advanceDate.classList.add('is-invalid');
            isValid = false;
        }

        // Payment mode
        if (!paymentMode.value) {
            paymentMode.classList.add('is-invalid');
            isValid = false;
        }

        // Bank (only if not cash)
        if (!isCash && !bankSelect.value) {
            bankSelect.classList.add('is-invalid');
            isValid = false;
        }

        return isValid;
    }

    form.addEventListener('submit', function(e) {
        if (!validateAdvanceForm()) {
            e.preventDefault();
            e.stopPropagation();
        }
    });
});
</script>
@endpush
