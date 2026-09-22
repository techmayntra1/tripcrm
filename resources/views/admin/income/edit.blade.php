@extends('layouts.app')
@section('title', 'Edit Income')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-pencil-square icon-gradient bg-success"></i>
            </div>
            <div>
                Edit Income
                <div class="page-title-subheading">{{ $income->receipt_number }}</div>
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.income.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>
<style>
    select.form-select.is-invalid,
    input.form-control.is-invalid,
    textarea.form-control.is-invalid,
    .form-select.is-invalid,
    .form-control.is-invalid {
        border-color: #dc3545 !important;
    }
    select.form-select.is-invalid:focus,
    input.form-control.is-invalid:focus,
    textarea.form-control.is-invalid:focus,
    .form-select.is-invalid:focus,
    .form-control.is-invalid:focus {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-graph-up-arrow me-2"></i> Income Details
            </div>
            <div class="card-body">
                <form action="{{ route('admin.income.update', $income) }}" method="POST" enctype="multipart/form-data" id="incomeForm" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="income_type" class="form-label">Income Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="income_type" name="income_type" required>
                                    <option value="">Select Type</option>
                                    <option value="trip" {{ $income->income_type == 'trip' ? 'selected' : '' }}>Trip Payment</option>
                                    <option value="advance" {{ $income->income_type == 'advance' ? 'selected' : '' }}>Advance Payment</option>
                                    <option value="other" {{ $income->income_type == 'other' ? 'selected' : '' }}>Other Income</option>
                                </select>
                                <div class="invalid-feedback">Please select income type</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="income_date" class="form-label">Income Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="income_date" name="income_date" value="{{ $income->income_date ? $income->income_date->format('Y-m-d') : '' }}" required>
                                <div class="invalid-feedback">Please select date</div>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="trip_section" style="{{ in_array($income->income_type, ['trip', 'advance']) ? 'display: flex;' : 'display: none;' }}">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="trip_id" class="form-label">Trip <span class="text-danger">*</span></label>
                                <select class="form-select" id="trip_id" name="trip_id">
                                    <option value="">Select Trip</option>
                                    @foreach($trips as $trip)
                                        <option value="{{ $trip->id }}" {{ $income->trip_id == $trip->id ? 'selected' : '' }}>
                                            {{ $trip->trip_number }} - {{ $trip->name }}{{ $trip->customer ? ' - '.$trip->customer->name : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="invoice_id" class="form-label">Against Invoice</label>
                                <select class="form-select" id="invoice_id" name="invoice_id">
                                    <option value="">Select Invoice (Optional)</option>
                                    @foreach($invoices as $invoice)
                                        <option value="{{ $invoice->id }}" {{ $income->invoice_id == $invoice->id ? 'selected' : '' }}>
                                            {{ $invoice->invoice_number }} - {{ formatMoney($invoice->grand_total, 0, $invoice) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="client_section" style="{{ $income->income_type == 'advance' ? 'display: flex;' : 'display: none;' }}">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                                <select class="form-select" id="customer_id" name="customer_id">
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ $income->customer_id == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }}{{ $customer->mobile ? ' - '.$customer->mobile : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_mode_id" class="form-label">Payment Mode <span class="text-danger">*</span></label>
                                <select class="form-select" id="payment_mode_id" name="payment_mode_id" required>
                                    <option value="">Select Mode</option>
                                    @foreach($paymentModes as $mode)
                                    <option value="{{ $mode->id }}" data-slug="{{ $mode->slug }}" {{ $income->payment_mode_id == $mode->id ? 'selected' : '' }}>{{ $mode->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please select payment mode</div>
                            </div>
                        </div>
                        <div class="col-md-6" id="bank_section" style="{{ optional($income->paymentMode)->slug == 'cash' ? 'display: none;' : '' }}">
                            <div class="mb-3">
                                <label for="bank_id" class="form-label">Bank Account <span class="text-danger">*</span></label>
                                <select class="form-select js-currency-source" id="bank_id" name="bank_id" data-currency-default="₹" {{ optional($income->paymentMode)->slug == 'cash' ? '' : 'required' }}>
                                    <option value="">Select Bank Account</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}" data-currency="{{ $bank->currency_symbol }}" {{ $income->bank_id == $bank->id ? 'selected' : '' }}>{{ $bank->bank_name }} - {{ $bank->account_number }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please select a bank account</div>
                            </div>
                        </div>
                        <input type="hidden" id="cash_account_id" value="{{ $cashAccount->id ?? '' }}">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text js-currency-symbol">₹</span>
                                    <input type="number" class="form-control" id="amount" name="amount" value="{{ $income->amount }}" min="1" step="1" max="999999999" required inputmode="numeric">
                                    <div class="invalid-feedback">Please enter a valid amount (1 - 99999999)</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="attachment" class="form-label">Upload Bill/Receipt</label>
                                @if($income->attachment)
                                    <div class="mb-2 p-2 bg-light rounded d-flex align-items-center justify-content-between">
                                        <div>
                                            <i class="bi bi-file-earmark me-1"></i>
                                            <a href="{{ asset('storage/' . $income->attachment) }}" target="_blank">View Current File</a>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="remove_attachment" value="1" id="remove_attachment">
                                            <label class="form-check-label text-danger" for="remove_attachment">Remove</label>
                                        </div>
                                    </div>
                                @endif
                                <input type="file" class="form-control" id="attachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Accepted: PDF, JPG, PNG (Max 2MB)</small>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="cheque_section" style="{{ optional($income->paymentMode)->slug == 'cheque' ? 'display: flex;' : 'display: none;' }}">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cheque_number" class="form-label">Cheque Number</label>
                                <input type="text" class="form-control" id="cheque_number" name="cheque_number" value="{{ $income->cheque_number }}" maxlength="20">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cheque_date" class="form-label">Cheque Date</label>
                                <input type="date" class="form-control" id="cheque_date" name="cheque_date" value="{{ $income->cheque_date ? $income->cheque_date->format('Y-m-d') : '' }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="bank_name" class="form-label">Bank Name</label>
                                <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{ $income->bank_name }}" maxlength="40">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2" placeholder="Add payment details, transaction ID, bank reference, or any other notes..." maxlength="150">{{ $income->description }}</textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.income.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i> Update Income
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const incomeType = document.getElementById('income_type');
    const paymentMode = document.getElementById('payment_mode_id');
    const tripSection = document.getElementById('trip_section');
    const clientSection = document.getElementById('client_section');
    const chequeSection = document.getElementById('cheque_section');
    const bankSection = document.getElementById('bank_section');
    const bankSelect = document.getElementById('bank_id');
    const cashAccountId = document.getElementById('cash_account_id')?.value;

    function toggleIncomeTypeSections() {
        tripSection.style.display = 'none';
        clientSection.style.display = 'none';
        document.getElementById('trip_id').removeAttribute('required');
        document.getElementById('customer_id').removeAttribute('required');
        const type = incomeType.value;
        if (type === 'trip') {
            tripSection.style.display = 'flex';
            document.getElementById('trip_id').setAttribute('required', 'required');
        } else if (type === 'advance') {
            tripSection.style.display = 'flex';
            clientSection.style.display = 'flex';
            document.getElementById('trip_id').setAttribute('required', 'required');
            document.getElementById('customer_id').setAttribute('required', 'required');
        }
    }

    function togglePaymentModeSections() {
        chequeSection.style.display = 'none';
        const selectedOption = paymentMode.options[paymentMode.selectedIndex];
        const slug = selectedOption ? selectedOption.getAttribute('data-slug') : '';
        if (slug === 'cheque') {
            chequeSection.style.display = 'flex';
        }

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

    incomeType.addEventListener('change', toggleIncomeTypeSections);
    paymentMode.addEventListener('change', togglePaymentModeSections);
    togglePaymentModeSections();

    const incomeMaxLengths = { cheque_number: 20, bank_name: 40, description: 150 };

    document.querySelectorAll('#incomeForm input, #incomeForm textarea').forEach(input => {
        input.addEventListener('input', function() {
            const maxLen = incomeMaxLengths[this.name];
            if (maxLen && this.value.length > maxLen) {
                this.value = this.value.substring(0, maxLen);
            }
        });
    });

    function validateIncomeForm(form) {
        let isValid = true;
        let errors = [];
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        const selectedPaymentOption = paymentMode.options[paymentMode.selectedIndex];
        const isCash = selectedPaymentOption?.getAttribute('data-slug') === 'cash';
        const requiredSelectIds = isCash ? ['income_type', 'payment_mode_id'] : ['income_type', 'payment_mode_id', 'bank_id'];
        requiredSelectIds.forEach(id => {
            const input = document.getElementById(id);
            if (input && (input.selectedIndex === 0 || !input.value)) {
                input.classList.add('is-invalid');
                errors.push(id + ' is required');
                isValid = false;
            }
        });

        const dateInput = form.querySelector('input[name="income_date"]');
        if (dateInput && !dateInput.value) {
            dateInput.classList.add('is-invalid');
            errors.push('income_date is required');
            isValid = false;
        }

        const amountInput = form.querySelector('input[name="amount"]');
        if (amountInput) {
            const val = amountInput.value.trim();
            const num = parseFloat(val);
            if (!val || isNaN(num) || num < 1 || num > 99999999) {
                amountInput.classList.add('is-invalid');
                errors.push('Amount must be between 1 and 99999999');
                isValid = false;
            }
        }

        if (!isValid) {
            console.log('Validation errors:', errors);
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }

        return isValid;
    }

    document.querySelectorAll('#incomeForm input, #incomeForm select, #incomeForm textarea').forEach(el => {
        el.addEventListener('input', function() {
            if (this.name === 'amount') {
                const num = parseFloat(this.value) || 0;
                if (num >= 1 && num <= 99999999) this.classList.remove('is-invalid');
            } else if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });
        el.addEventListener('change', function() {
            if (this.name === 'amount') {
                const num = parseFloat(this.value) || 0;
                if (num >= 1 && num <= 99999999) this.classList.remove('is-invalid');
            } else if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });
    });

    const amountInput = document.getElementById('amount');
    if (amountInput) {
        amountInput.addEventListener('keydown', function(e) {
            const val = this.value.replace(/[^0-9]/g, '');
            if ([8, 9, 13, 27, 46, 37, 38, 39, 40].includes(e.keyCode)) return;
            if ((e.ctrlKey || e.metaKey) && [65, 67, 86, 88].includes(e.keyCode)) return;
            if (val.length >= 8 && e.keyCode >= 48 && e.keyCode <= 57) {
                e.preventDefault();
            }
            if (val.length >= 8 && e.keyCode >= 96 && e.keyCode <= 105) {
                e.preventDefault();
            }
        });
        amountInput.addEventListener('input', function() {
            let val = this.value.replace(/[^0-9]/g, '');
            if (val.length > 8) {
                val = val.substring(0, 8);
                this.value = val;
            }
        });
    }

    document.getElementById('incomeForm').addEventListener('submit', function(e) {
        if (!validateIncomeForm(this)) {
            e.preventDefault();
            e.stopPropagation();
        }
    });
});
</script>
@endpush
@push('styles')
<style>
.input-group .is-invalid ~ .invalid-feedback,
.input-group .is-invalid ~ .invalid-tooltip {
    display: block;
}
.input-group.has-validation .invalid-feedback {
    width: 100%;
}
</style>
@endpush
