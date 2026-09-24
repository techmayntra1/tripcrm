@extends('layouts.app')
@section('title', 'Edit Quotation')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-pencil-square icon-gradient bg-premium-dark"></i>
            </div>
            <div>
                Edit Quotation #{{ $quotation->quotation_number }}

            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.quotations.index') }}" class="btn btn-outline-secondary">
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
<form action="{{ route('admin.quotations.update', $quotation) }}" method="POST" class="needs-validation" novalidate>
    @csrf
    @method('PUT')
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        @foreach($errors->all() as $error)
            {{ $error }}<br>
        @endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    <div class="main-card mb-3 card">
        <div class="card-header">
            <i class="bi bi-info-circle me-2"></i> Quotation Details
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="quotation_number" class="form-label">Quotation Number</label>
                        <input type="text" class="form-control" id="quotation_number" value="{{ $quotation->quotation_number }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date', $quotation->date->format('Y-m-d')) }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="company" class="form-label">Company <span class="text-danger">*</span></label>
                        <select class="form-select js-currency-source @error('company_id') is-invalid @enderror" id="company" name="company_id" data-currency-default="₹" required>
                            <option value="" data-has-gst="0">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" data-currency="{{ $company->currency_symbol }}" data-has-gst="{{ !empty($company->gst_number) ? '1' : '0' }}" {{ old('company_id', $quotation->company_id) == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="customer" class="form-label">Customer <span class="text-danger">*</span></label>
                        <select class="form-select @error('customer_id') is-invalid @enderror" id="customer" name="customer_id" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $quotation->customer_id) == $customer->id ? 'selected' : '' }}>{{ $customer->name }} - {{ $customer->mobile }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="trip" class="form-label">Trip</label>
                        <select class="form-select @error('trip_id') is-invalid @enderror" id="trip" name="trip_id">
                            <option value="">Select Trip (Optional)</option>
                            @foreach($trips as $trip)
                                <option value="{{ $trip->id }}" data-customer="{{ $trip->customer_id }}" {{ old('trip_id', $quotation->trip_id) == $trip->id ? 'selected' : '' }}>{{ $trip->trip_number }} - {{ $trip->name }}</option>
                            @endforeach
                        </select>
                        @error('trip_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject', $quotation->subject) }}" placeholder="e.g., Interior Design Work for 3BHK" maxlength="150">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="sent" {{ old('status', $quotation->status) == 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="accepted" {{ old('status', $quotation->status) == 'accepted' ? 'selected' : '' }}>Accepted</option>
                            <option value="rejected" {{ old('status', $quotation->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="expired" {{ old('status', $quotation->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="main-card mb-3 card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-list-check me-2"></i> Quotation Items
            </div>
            <button type="button" class="btn btn-sm btn-primary" id="addItemBtn">
                <i class="bi bi-plus-lg me-1"></i> Add Item
            </button>
        </div>
        @if($quotation->quotation_type === 'pdf')
        <div class="alert alert-warning rounded-0 mb-0 small">
            <i class="bi bi-info-circle me-1"></i>
            This quotation was created from an uploaded PDF.
            @if($quotation->quotation_pdf)
            <a href="{{ Storage::url($quotation->quotation_pdf) }}" target="_blank">View PDF</a>.
            @endif
            PDF upload is no longer supported — add line items below to save changes.
        </div>
        @endif
        <div class="card-body p-0" id="manualItemsSection">
            @php
                $hasSqft = false;
                if($quotation->items && count($quotation->items) > 0) {
                    foreach($quotation->items as $item) {
                        if(strtolower($item['unit'] ?? '') === 'sqft' || ($item['height'] ?? '') !== '' || ($item['width'] ?? '') !== '' || ($item['total'] ?? '') !== '') {
                            $hasSqft = true;
                            break;
                        }
                    }
                }
            @endphp
            <table class="table table-bordered mb-0" id="itemsTable">
                <thead class="table-light">
                    <tr>
                        <th width="40">#</th>
                        <th width="170">Service</th>
                        <th>Description <span class="text-danger">*</span></th>
                        <th width="130">Passenger Type</th>
                        <th width="95">Qty <span class="text-danger">*</span></th>
                        <th width="100">Rate (<span class="js-currency-symbol">₹</span>)</th>
                        <th width="120">Amount (<span class="js-currency-symbol">₹</span>)</th>
                        <th width="110" class="tax-col" style="display:none;">Tax Type</th>
                        <th width="90" class="tax-col" style="display:none;">Tax %</th>
                        <th></th>
                    </tr>
                </thead>
                    <tbody id="itemsBody">
                        @if($quotation->items && count($quotation->items) > 0)
                            @foreach($quotation->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <select class="form-select form-select-sm service-select" name="items[{{ $index }}][service_id]">
                                        <option value="">— Custom —</option>
                                        @foreach($services as $svc)
                                        <option value="{{ $svc->id }}" data-name="{{ $svc->name }}" data-price="{{ $svc->price }}" data-description="{{ $svc->description }}" {{ ($item['service_id'] ?? '') == $svc->id ? 'selected' : '' }}>{{ $svc->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" class="service-name" name="items[{{ $index }}][service_name]" value="{{ $item['service_name'] ?? '' }}">
                                </td>
                                <td><textarea class="form-control form-control-sm item-description" name="items[{{ $index }}][description]" placeholder="Item description" required minlength="1" maxlength="150" rows="1">{{ $item['description'] ?? '' }}</textarea></td>
                                <td>
                                    <select class="form-select form-select-sm" name="items[{{ $index }}][passenger_type]">
                                        <option value="">—</option>
                                        @foreach($passengerTypes as $pt)
                                        <option value="{{ $pt->name }}" {{ ($item['passenger_type'] ?? '') == $pt->name ? 'selected' : '' }}>{{ $pt->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" class="form-control form-control-sm qty" name="items[{{ $index }}][qty]" value="{{ $item['qty'] ?? 1 }}" min="1" max="99999" step="1" required inputmode="numeric"></td>
                                <td><input type="number" class="form-control form-control-sm rate" name="items[{{ $index }}][rate]" value="{{ $item['rate'] ?? 0 }}" min="0" max="999999999" step="1" inputmode="numeric"></td>
                                <td><input type="number" class="form-control form-control-sm amount" name="items[{{ $index }}][amount]" value="{{ $item['amount'] ?? round(($item['qty'] ?? 0) * ($item['rate'] ?? 0)) }}" min="0" step="1" inputmode="numeric"></td>
                                <td class="tax-col" style="display:none;">
                                    <select class="form-select form-select-sm tax-type" name="items[{{ $index }}][tax_type]">
                                        <option value="none" {{ ($item['tax_type'] ?? 'gst') == 'none' ? 'selected' : '' }}>None</option>
                                        <option value="gst" {{ ($item['tax_type'] ?? 'gst') == 'gst' ? 'selected' : '' }}>GST</option>
                                        <option value="vat" {{ ($item['tax_type'] ?? 'gst') == 'vat' ? 'selected' : '' }}>VAT</option>
                                    </select>
                                </td>
                                <td class="tax-col" style="display:none;"><input type="number" class="form-control form-control-sm tax-rate" name="items[{{ $index }}][tax_rate]" value="{{ $item['tax_rate'] ?? 18 }}" min="0" max="100" step="0.01" inputmode="decimal"></td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td>1</td>
                                <td>
                                    <select class="form-select form-select-sm service-select" name="items[0][service_id]">
                                        <option value="">— Custom —</option>
                                        @foreach($services as $svc)
                                        <option value="{{ $svc->id }}" data-name="{{ $svc->name }}" data-price="{{ $svc->price }}" data-description="{{ $svc->description }}">{{ $svc->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" class="service-name" name="items[0][service_name]" value="">
                                </td>
                                <td><textarea class="form-control form-control-sm item-description" name="items[0][description]" placeholder="Item description" required minlength="1" maxlength="150" rows="1"></textarea></td>
                                <td>
                                    <select class="form-select form-select-sm" name="items[0][passenger_type]">
                                        <option value="">—</option>
                                        @foreach($passengerTypes as $pt)
                                        <option value="{{ $pt->name }}">{{ $pt->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" class="form-control form-control-sm qty" name="items[0][qty]" value="1" min="1" max="99999" step="1" required inputmode="numeric"></td>
                                <td><input type="number" class="form-control form-control-sm rate" name="items[0][rate]" placeholder="0" min="0" max="999999999" step="1" inputmode="numeric"></td>
                                <td><input type="number" class="form-control form-control-sm amount" name="items[0][amount]" placeholder="0" min="0" step="1" inputmode="numeric"></td>
                                <td class="tax-col" style="display:none;">
                                    <select class="form-select form-select-sm tax-type" name="items[0][tax_type]">
                                        <option value="none">None</option>
                                        <option value="gst" selected>GST</option>
                                        <option value="vat">VAT</option>
                                    </select>
                                </td>
                                <td class="tax-col" style="display:none;"><input type="number" class="form-control form-control-sm tax-rate" name="items[0][tax_rate]" value="18" min="0" max="100" step="0.01" inputmode="decimal"></td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            @include('partials._term_fields', ['name' => 'terms', 'label' => 'Terms & Conditions', 'icon' => 'bi-card-text', 'templates' => $termTemplates, 'value' => old('terms', $quotation->terms)])
            @include('partials._term_fields', ['name' => 'payment_terms', 'label' => 'Payment Terms', 'icon' => 'bi-cash-coin', 'templates' => $paymentTermTemplates, 'value' => old('payment_terms', $quotation->payment_terms)])
        </div>
        <div class="col-md-6">
            <div class="main-card mb-3 card">
                <div class="card-header">
                    <i class="bi bi-calculator me-2"></i> Summary
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="py-2">Subtotal</td>
                            <td class="py-2">
                                <div class="input-group">
                                    <span class="input-group-text js-currency-symbol">₹</span>
                                    <input type="number" class="form-control" min="0" step="1" max="999999999" name="subtotal" id="subtotalInput" value="{{ old('subtotal', $quotation->subtotal) }}" inputmode="numeric">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2">Discount</td>
                            <td class="py-2">
                                <div class="input-group">
                                    <span class="input-group-text js-currency-symbol">₹</span>
                                    <input type="number" class="form-control" min="0" step="1" max="999999999" name="discount" id="discountInput" value="{{ old('discount', $quotation->discount) }}" inputmode="numeric">
                                </div>
                            </td>
                        </tr>
                        <tr id="gstRow">
                            <td class="py-2" id="gstLabel">Tax</td>
                            <td class="py-2">
                                <div class="input-group">
                                    <span class="input-group-text" id="gstPerLineNote" style="font-size:12px;">Per line</span>
                                    <select class="form-select d-none" style="max-width: 140px;" name="gst_percent" id="gstPercent">
                                        @foreach($gstRates as $rate)
                                        <option value="{{ $rate->percentage }}" {{ old('gst_percent', $quotation->gst_percent) == $rate->percentage ? 'selected' : '' }}>{{ $rate->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-text {{ old('gst_inclusive', $quotation->gst_inclusive) ? '' : 'text-success' }}" id="gstSign">{{ old('gst_inclusive', $quotation->gst_inclusive) ? '' : '+ ' }}<span class="js-currency-symbol">₹</span></span>
                                    <input type="number" class="form-control" name="gst" id="gstInput" value="{{ old('gst', $quotation->gst) }}" readonly style="background-color: #e9ecef;">
                                </div>
                                <div class="row g-2 mt-2" id="gstSplitDisplay" style="display: {{ old('gst_split', $quotation->gst_split) ? '' : 'none' }};">
                                    <div class="col-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">CGST</span>
                                            <input type="number" class="form-control" id="cgstDisplay" value="{{ round(($quotation->gst ?? 0) / 2) }}" readonly style="background-color: #e9ecef;">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">SGST</span>
                                            <input type="number" class="form-control" id="sgstDisplay" value="{{ round(($quotation->gst ?? 0) / 2) }}" readonly style="background-color: #e9ecef;">
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-3 mt-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="gst_inclusive" id="gstInclusive" value="1" {{ old('gst_inclusive', $quotation->gst_inclusive) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="gstInclusive">GST Inclusive</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="gst_split" id="gstSplit" value="1" {{ old('gst_split', $quotation->gst_split) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="gstSplit">Split SGST + CGST</label>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2"><strong>Grand Total</strong></td>
                            <td class="py-2">
                                <div class="input-group">
                                    <span class="input-group-text js-currency-symbol">₹</span>
                                    <input type="number" class="form-control fw-bold" min="0" step="1" max="999999999" name="grand_total" id="grandTotalInput" value="{{ old('grand_total', $quotation->grand_total) }}" inputmode="numeric">
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('admin.quotations.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> Update Quotation
        </button>
    </div>
</form>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemsBody = document.getElementById('itemsBody');
    const gstRow = document.getElementById('gstRow');
    const companySelect = document.getElementById('company');
    const addItemBtn = document.getElementById('addItemBtn');
    const customerSelect = document.getElementById('customer');
    const tripSelect = document.getElementById('trip');
    let itemIndex = itemsBody.querySelectorAll('tr').length;

    // ----- Customer <-> Trip linking -----
    const tripOptions = Array.from(tripSelect.options);

    function filterTripsByCustomer(customerId) {
        let currentStillValid = false;
        tripOptions.forEach(function(opt) {
            if (!opt.value) { opt.hidden = false; return; }
            const belongs = !customerId || opt.getAttribute('data-customer') === String(customerId);
            opt.hidden = !belongs;
            if (belongs && opt.value === tripSelect.value) currentStillValid = true;
        });
        if (tripSelect.value && !currentStillValid) tripSelect.value = '';
    }

    function lockCustomer(customerId) {
        if (!customerId) return;
        customerSelect.value = String(customerId);
        customerSelect.disabled = true;
        let hidden = document.getElementById('customerHidden');
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'customer_id';
            hidden.id = 'customerHidden';
            customerSelect.parentNode.appendChild(hidden);
        }
        hidden.value = String(customerId);
        customerSelect.classList.remove('is-invalid');
    }

    function unlockCustomer() {
        customerSelect.disabled = false;
        const hidden = document.getElementById('customerHidden');
        if (hidden) hidden.remove();
    }

    customerSelect.addEventListener('change', function() {
        filterTripsByCustomer(this.value);
    });

    tripSelect.addEventListener('change', function() {
        if (this.value) {
            const opt = this.options[this.selectedIndex];
            lockCustomer(opt.getAttribute('data-customer'));
        } else {
            unlockCustomer();
        }
    });

    // Initial customer <-> trip sync
    if (tripSelect.value) {
        const opt = tripSelect.options[tripSelect.selectedIndex];
        lockCustomer(opt.getAttribute('data-customer'));
    } else if (customerSelect.value) {
        filterTripsByCustomer(customerSelect.value);
    }

    function calculateTotals() {
        const subtotalInput = document.getElementById('subtotalInput');
        const grandTotalInput = document.getElementById('grandTotalInput');
        const gstInclusive = document.getElementById('gstInclusive').checked;
        const gstSign = document.getElementById('gstSign');
        const gstVisible = gstRow.style.display !== 'none';
        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        const splitOn = document.getElementById('gstSplit').checked;

        let subtotal = 0;
        let gst = 0;         // total tax shown in the Tax box
        let gstPortion = 0;  // GST-type tax only, used for CGST/SGST split
        let grandTotal;

        // Per-line tax engine: each line has its own tax type + rate.
        let gstTax = 0, vatTax = 0;
        document.querySelectorAll('#itemsBody tr').forEach(function(row) {
            const amtEl = row.querySelector('.amount');
            const amt = amtEl ? (parseFloat(amtEl.value) || 0) : 0;
            subtotal += amt;
            if (!gstVisible) return;
            const typeEl = row.querySelector('.tax-type');
            const rateEl = row.querySelector('.tax-rate');
            const type = typeEl ? typeEl.value : 'none';
            const rate = rateEl ? (parseFloat(rateEl.value) || 0) : 0;
            if (rate <= 0) return;
            if (type === 'gst') {
                gstTax += gstInclusive ? (amt * rate) / (100 + rate) : (amt * rate) / 100;
            } else if (type === 'vat') {
                vatTax += (amt * rate) / 100;
            }
        });
        subtotal = Math.min(Math.round(subtotal), 99999999);
        subtotalInput.value = subtotal;
        gstPortion = gstTax;
        gst = gstTax + vatTax;
        // Inclusive GST is already embedded in the line amounts (subtotal); don't add it again.
        const addTax = vatTax + (gstInclusive ? 0 : gstTax);
        grandTotal = Math.round(subtotal - discount + addTax);
        if (gstInclusive) {
            gstSign.innerHTML = '<span class="js-currency-symbol">' + currencySymbol() + '</span>';
            gstSign.classList.remove('text-success');
        } else {
            gstSign.innerHTML = '+ <span class="js-currency-symbol">' + currencySymbol() + '</span>';
            gstSign.classList.add('text-success');
        }

        document.getElementById('gstInput').value = Math.round(gst);
        grandTotalInput.value = grandTotal;

        document.getElementById('gstSplitDisplay').style.display = splitOn ? '' : 'none';
        if (splitOn) {
            const half = Math.round(gstPortion / 2);
            document.getElementById('cgstDisplay').value = half;
            document.getElementById('sgstDisplay').value = half;
        }

        if (grandTotal > 99999999) {
            grandTotalInput.classList.add('is-invalid');
        } else {
            grandTotalInput.classList.remove('is-invalid');
        }

        if (subtotal > 99999999) {
            subtotalInput.classList.add('is-invalid');
        } else {
            subtotalInput.classList.remove('is-invalid');
        }
    }

    function updateTaxColumns() {
        const show = gstRow.style.display !== 'none';
        document.querySelectorAll('.tax-col').forEach(function(el) {
            el.style.display = show ? '' : 'none';
        });
    }

    function updateGstVisibility() {
        const selectedOption = companySelect.options[companySelect.selectedIndex];
        const hasGst = selectedOption && selectedOption.getAttribute('data-has-gst') === '1';
        gstRow.style.display = hasGst ? '' : 'none';
        updateTaxColumns();
        calculateTotals();
    }

    function reindexRows() {
        const rows = itemsBody.querySelectorAll('tr');
        rows.forEach(function(row, index) {
            row.querySelector('td:first-child').textContent = index + 1;
        });
        itemIndex = rows.length;
    }

    companySelect.addEventListener('change', updateGstVisibility);

    function hasSqftSelected() {
        const unitSelects = itemsBody.querySelectorAll('.unit-select');
        for (let i = 0; i < unitSelects.length; i++) {
            if (unitSelects[i].value.toLowerCase() === 'sqft') return true;
        }
        return false;
    }

    function toggleSqftColumns() {
        const show = hasSqftSelected();
        document.querySelectorAll('.sqft-col').forEach(function(el) {
            el.style.display = show ? '' : 'none';
        });
    }

    function calculateTotalSqft(row) {
        const heightInput = row.querySelector('.item-height');
        const widthInput = row.querySelector('.item-width');
        const totalInput = row.querySelector('.item-total');
        if (heightInput && widthInput && totalInput) {
            const height = parseFloat(heightInput.value) || 0;
            const width = parseFloat(widthInput.value) || 0;
            if (height > 0 && width > 0) {
                totalInput.value = (height * width).toFixed(2);
            }
        }
    }

    function calculateRowAmount(row) {
        const unitSelect = row.querySelector('.unit-select');
        const qty = parseFloat(row.querySelector('.qty').value) || 0;
        const rate = parseFloat(row.querySelector('.rate').value) || 0;
        const amountInput = row.querySelector('.amount');

        let amount;
        if (unitSelect && unitSelect.value.toLowerCase() === 'sqft') {
            const total = parseFloat(row.querySelector('.item-total').value) || 0;
            amount = total * qty * rate;
        } else {
            amount = qty * rate;
        }
        amountInput.value = Math.round(amount);
    }

    addItemBtn.addEventListener('click', function() {
        const showSqft = hasSqftSelected();
        const showTax = gstRow.style.display !== 'none';
        const newRow = `
            <tr>
                <td>${itemIndex + 1}</td>
                <td>
                    <select class="form-select form-select-sm service-select" name="items[${itemIndex}][service_id]">
                        <option value="">— Custom —</option>
                        @foreach($services as $svc)
                        <option value="{{ $svc->id }}" data-name="{{ $svc->name }}" data-price="{{ $svc->price }}" data-description="{{ $svc->description }}">{{ $svc->name }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" class="service-name" name="items[${itemIndex}][service_name]" value="">
                </td>
                <td><textarea class="form-control form-control-sm item-description" name="items[${itemIndex}][description]" placeholder="Item description" required minlength="1" maxlength="150" rows="1"></textarea></td>
                <td>
                    <select class="form-select form-select-sm" name="items[${itemIndex}][passenger_type]">
                        <option value="">—</option>
                        @foreach($passengerTypes as $pt)
                        <option value="{{ $pt->name }}">{{ $pt->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" class="form-control form-control-sm qty" name="items[${itemIndex}][qty]" value="1" min="1" max="99999" step="1" required inputmode="numeric"></td>
                <td><input type="number" class="form-control form-control-sm rate" name="items[${itemIndex}][rate]" placeholder="0" min="0" max="999999999" step="1" inputmode="numeric"></td>
                <td><input type="number" class="form-control form-control-sm amount" name="items[${itemIndex}][amount]" placeholder="0" min="0" step="1" inputmode="numeric"></td>
                <td class="tax-col" style="${showTax ? '' : 'display:none;'}">
                    <select class="form-select form-select-sm tax-type" name="items[${itemIndex}][tax_type]">
                        <option value="none">None</option>
                        <option value="gst" selected>GST</option>
                        <option value="vat">VAT</option>
                    </select>
                </td>
                <td class="tax-col" style="${showTax ? '' : 'display:none;'}"><input type="number" class="form-control form-control-sm tax-rate" name="items[${itemIndex}][tax_rate]" value="18" min="0" max="100" step="0.01" inputmode="decimal"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>
            </tr>
        `;
        itemsBody.insertAdjacentHTML('beforeend', newRow);
        itemIndex++;
        reindexRows();
    });

    itemsBody.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            const rows = itemsBody.querySelectorAll('tr');
            if (rows.length > 1) {
                e.target.closest('tr').remove();
                reindexRows();
                toggleSqftColumns();
                calculateTotals();
            }
        }
    });

    itemsBody.addEventListener('input', function(e) {
        if (e.target.classList.contains('item-height') || e.target.classList.contains('item-width')) {
            const row = e.target.closest('tr');
            calculateTotalSqft(row);
            calculateRowAmount(row);
            calculateTotals();
        }
        if (e.target.classList.contains('qty') || e.target.classList.contains('rate') || e.target.classList.contains('item-total')) {
            const row = e.target.closest('tr');
            calculateRowAmount(row);
            calculateTotals();
        }
        if (e.target.classList.contains('amount') || e.target.classList.contains('tax-rate')) {
            calculateTotals();
        }
    });

    itemsBody.addEventListener('change', function(e) {
        if (e.target.classList.contains('unit-select')) {
            toggleSqftColumns();
            const row = e.target.closest('tr');
            calculateRowAmount(row);
            calculateTotals();
        }
        if (e.target.classList.contains('service-select')) {
            const row = e.target.closest('tr');
            const opt = e.target.options[e.target.selectedIndex];
            const nameInput = row.querySelector('.service-name');
            if (nameInput) nameInput.value = e.target.value ? (opt.getAttribute('data-name') || '') : '';
            if (e.target.value) {
                const price = opt.getAttribute('data-price');
                const desc = opt.getAttribute('data-description');
                if (price !== null && price !== '') {
                    const rateEl = row.querySelector('.rate');
                    if (rateEl) rateEl.value = Math.round(parseFloat(price));
                }
                const descEl = row.querySelector('.item-description');
                if (descEl && desc) descEl.value = desc;
                calculateRowAmount(row);
            }
            calculateTotals();
        }
        if (e.target.classList.contains('tax-type')) {
            calculateTotals();
        }
    });

    document.getElementById('subtotalInput').addEventListener('input', calculateTotals);
    document.getElementById('discountInput').addEventListener('input', calculateTotals);
    document.getElementById('gstPercent').addEventListener('change', calculateTotals);
    document.getElementById('gstInclusive').addEventListener('change', calculateTotals);
    document.getElementById('gstSplit').addEventListener('change', calculateTotals);

    function validateForm() {
        let isValid = true;
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        const dateInput = document.getElementById('date');
        if (!dateInput.value) {
            dateInput.classList.add('is-invalid');
            isValid = false;
        }

        if (companySelect.selectedIndex === 0 || !companySelect.value) {
            companySelect.classList.add('is-invalid');
            isValid = false;
        }

        const customerInput = document.getElementById('customer');
        if (customerInput.selectedIndex === 0 || !customerInput.value) {
            customerInput.classList.add('is-invalid');
            isValid = false;
        }

        const subtotalInput = document.getElementById('subtotalInput');
        const grandTotalInput = document.getElementById('grandTotalInput');
        const subtotal = parseFloat(subtotalInput.value) || 0;
        const grandTotal = parseFloat(grandTotalInput.value) || 0;

        if (subtotal <= 0 || subtotal > 99999999) {
            subtotalInput.classList.add('is-invalid');
            isValid = false;
        }

        if (grandTotal <= 0 || grandTotal > 99999999) {
            grandTotalInput.classList.add('is-invalid');
            isValid = false;
        }

        itemsBody.querySelectorAll('tr').forEach(function(row) {
            const description = row.querySelector('textarea[name*="[description]"]');
            const qty = row.querySelector('input[name*="[qty]"]');
            const rate = row.querySelector('input[name*="[rate]"]');
            const unitSelect = row.querySelector('.unit-select');
            const isSqft = unitSelect && unitSelect.value.toLowerCase() === 'sqft';
            if (description && qty && rate) {
                if (!description.value.trim()) {
                    description.classList.add('is-invalid');
                    isValid = false;
                }
                if ((parseFloat(qty.value) || 0) <= 0) {
                    qty.classList.add('is-invalid');
                    isValid = false;
                }
                if (!isSqft && (parseFloat(rate.value) || 0) <= 0) {
                    rate.classList.add('is-invalid');
                    isValid = false;
                }
            }
        });

        if (!isValid) {
            const firstError = document.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }

        return isValid;
    }

    document.querySelector('form.needs-validation').addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            e.stopPropagation();
        }
    });

    document.querySelectorAll('form.needs-validation input, form.needs-validation select, form.needs-validation textarea').forEach(el => {
        el.addEventListener('input', function() {
            if (this.name === 'subtotal' || this.name === 'grand_total' || this.name === 'discount') {
                const num = parseFloat(this.value) || 0;
                if (this.name === 'discount') {
                    if (num >= 0 && num <= 99999999) this.classList.remove('is-invalid');
                } else {
                    if (num >= 1 && num <= 99999999) this.classList.remove('is-invalid');
                }
            } else if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });
        el.addEventListener('change', function() {
            if (this.name === 'subtotal' || this.name === 'grand_total' || this.name === 'discount') {
                const num = parseFloat(this.value) || 0;
                if (this.name === 'discount') {
                    if (num >= 0 && num <= 99999999) this.classList.remove('is-invalid');
                } else {
                    if (num >= 1 && num <= 99999999) this.classList.remove('is-invalid');
                }
            } else if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });
    });

    ['subtotalInput', 'discountInput', 'grandTotalInput'].forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('keydown', function(e) {
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
            input.addEventListener('input', function() {
                let val = this.value.replace(/[^0-9]/g, '');
                if (val.length > 8) {
                    val = val.substring(0, 8);
                    this.value = val;
                }
            });
        }
    });

    updateGstVisibility();
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
#manualItemsSection {
    overflow-x: auto;
}
#itemsTable {
    min-width: 980px;
}
#itemsTable .sqft-col {
    min-width: 110px;
}
#itemsTable .item-height,
#itemsTable .item-width,
#itemsTable .item-total,
#itemsTable .qty {
    min-width: 85px;
}
</style>
@endpush
