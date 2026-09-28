@extends('layouts.app')
@section('title', 'Create Invoice')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-receipt icon-gradient bg-grow-early"></i>
            </div>
            <div>
                Create Invoice
            </div>
        </div>
        <div class="page-title-actions">
            @if(isset($selectedQuotationId) && $selectedQuotationId)
                <a href="{{ route('admin.quotations.show', $selectedQuotationId) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Quotation
                </a>
            @elseif(isset($selectedTripId) && $selectedTripId)
                <a href="{{ route('admin.trips.show', $selectedTripId) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Trip
                </a>
            @elseif(isset($selectedCustomerId) && $selectedCustomerId)
                <a href="{{ route('admin.customers.show', $selectedCustomerId) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Customer
                </a>
            @else
                <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            @endif
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
<form action="{{ route('admin.invoices.store') }}" method="POST" enctype="multipart/form-data" id="invoiceForm" novalidate>
    @csrf
    <div class="main-card mb-3 card">
        <div class="card-header">
            <i class="bi bi-info-circle me-2"></i> Invoice Details
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="date" class="form-label">Invoice Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                        <div class="invalid-feedback">Please select invoice date</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" class="form-control" id="due_date" name="due_date" value="{{ old('due_date') }}" placeholder="Select due date">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="company" class="form-label">Company <span class="text-danger">*</span></label>
                        <select class="form-select js-currency-source" id="company" name="company_id" data-currency-default="₹" required>
                            <option value="" data-has-gst="0" data-tax-mode="none">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" data-currency="{{ $company->currency_symbol }}" data-has-gst="{{ !empty($company->gst_number) ? '1' : '0' }}" data-tax-mode="{{ $company->tax_mode }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a company</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="customer" class="form-label">Customer <span class="text-danger">*</span></label>
                        <select class="form-select" id="customer" name="customer_id" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ (old('customer_id', $selectedCustomerId ?? '') == $customer->id) ? 'selected' : '' }}>{{ $customer->name }}{{ $customer->mobile ? ' - '.$customer->mobile : '' }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a customer</div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="trip" class="form-label">Trip</label>
                        <select class="form-select" id="trip" name="trip_id" {{ isset($selectedTripId) && $selectedTripId ? 'disabled' : '' }}>
                            <option value="">Select Trip (Optional)</option>
                            @foreach($trips as $trip)
                                <option value="{{ $trip->id }}" data-customer="{{ $trip->customer_id }}" {{ (old('trip_id', $selectedTripId ?? '') == $trip->id) ? 'selected' : '' }}>{{ $trip->trip_number }} - {{ $trip->name }}</option>
                            @endforeach
                        </select>
                        @if(isset($selectedTripId) && $selectedTripId)
                            <input type="hidden" name="trip_id" value="{{ $selectedTripId }}">
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="quotation" class="form-label">From Quotation</label>
                        <select class="form-select" id="quotation" name="quotation_id">
                            <option value="">Select Quotation (Optional)</option>
                            @foreach($quotations as $quotation)
                                <option value="{{ $quotation->id }}"
                                    data-customer="{{ $quotation->customer_id }}"
                                    data-company="{{ $quotation->company_id }}"
                                    data-items="{{ json_encode($quotation->items) }}"
                                    data-subtotal="{{ $quotation->subtotal }}"
                                    data-discount="{{ $quotation->discount }}"
                                    data-gst-percent="{{ $quotation->gst_percent }}"
                                    data-gst-inclusive="{{ $quotation->gst_inclusive ? '1' : '0' }}"
                                    data-gst-split="{{ $quotation->gst_split ? '1' : '0' }}"
                                    data-gst="{{ $quotation->gst }}"
                                    data-grand-total="{{ $quotation->grand_total }}"
                                    data-terms="{{ $quotation->terms }}"
                                    data-payment-terms="{{ $quotation->payment_terms }}"
                                    {{ (old('quotation_id', $selectedQuotationId ?? '') == $quotation->id) ? 'selected' : '' }}>
                                    {{ $quotation->quotation_number }}{{ $quotation->customer ? ' - '.$quotation->customer->name : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" value="{{ old('subject') }}" placeholder="Invoice subject" maxlength="200">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="main-card mb-3 card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-list-check me-2"></i> Invoice Items
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="invoice_type" id="typePdf" value="pdf" checked>
                    <label class="btn btn-outline-primary btn-sm" for="typePdf">
                        <i class="bi bi-file-pdf me-1"></i> Upload PDF
                    </label>
                    <input type="radio" class="btn-check" name="invoice_type" id="typeItems" value="items">
                    <label class="btn btn-outline-primary btn-sm" for="typeItems">
                        <i class="bi bi-list-ul me-1"></i> Add Items
                    </label>
                </div>
                <button type="button" class="btn btn-sm btn-primary" id="addItemBtn" style="display: none;">
                    <i class="bi bi-plus-lg me-1"></i> Add Item
                </button>
            </div>
        </div>
        <div class="card-body" id="pdfUploadSection">
            <div class="row">
                <div class="col-md-5">
                    <label for="invoice_pdf" class="form-label">Upload Invoice PDF <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="invoice_pdf" name="invoice_pdf" accept=".pdf" required>
                    <div class="invalid-feedback">Please upload an invoice PDF</div>
                    <small class="text-muted">Max 10MB. Upload your invoice document.</small>
                </div>
                <div class="col-md-7">
                    <label for="pdf_description" class="form-label">Description</label>
                    <input type="text" class="form-control" id="pdf_description" name="pdf_description" value="{{ old('pdf_description') }}" placeholder="Brief description of the invoice...">
                </div>
            </div>
        </div>
        <div class="card-body p-0" id="manualItemsSection" style="display: none;">
            <div class="table-responsive">
                <table class="table table-bordered mb-0" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="40">#</th>
                            <th width="160">Service</th>
                            <th>Description <span class="text-danger">*</span></th>
                            <th width="95">Qty <span class="text-danger">*</span></th>
                            <th width="100">Rate (<span class="js-currency-symbol">₹</span>)</th>
                            <th width="120">Amount (<span class="js-currency-symbol">₹</span>)</th>
                        <th width="110" class="fee-col" style="display:none;">Service Fee (<span class="js-currency-symbol">₹</span>)</th>
                            <th width="110" class="tax-col" style="display:none;">Tax Type</th>
                            <th width="90" class="tax-col" style="display:none;">Tax %</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">1</td>
                            <td>
                                <select class="form-select form-select-sm service-select" name="items[0][service_id]">
                                    <option value="">— None —</option>
                                    @foreach($services as $svc)
                                    <option value="{{ $svc->id }}" data-name="{{ $svc->name }}" data-price="{{ $svc->price }}" data-description="{{ $svc->description }}">{{ $svc->name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="service-name" name="items[0][service_name]" value="">
                            </td>
                            <td><textarea class="form-control form-control-sm item-description" name="items[0][description]" placeholder="Item description" minlength="3" maxlength="150" rows="1"></textarea></td>
                            <td><input type="number" class="form-control form-control-sm qty" name="items[0][qty]" value="1" min="1" max="99999" step="1" inputmode="numeric"></td>
                            <td><input type="number" class="form-control form-control-sm rate" name="items[0][rate]" placeholder="0" min="0" max="999999999" step="1" inputmode="numeric"></td>
                            <td><input type="number" class="form-control form-control-sm amount" name="items[0][amount]" value="0" min="0" step="1" inputmode="numeric"></td>
                            <td class="fee-col" style="display:none;"><input type="number" class="form-control form-control-sm service-fee" name="items[0][service_fee]" value="0" min="0" max="999999999" step="1" inputmode="numeric"></td>
                            <td class="tax-col" style="display:none;">
                                <select class="form-select form-select-sm tax-type" name="items[0][tax_type]">
                                    <option value="none">None</option>
                                    <option value="gst" selected>GST</option>
                                    <option value="vat">VAT</option>
                                </select>
                            </td>
                            <td class="tax-col" style="display:none;"><input type="number" class="form-control form-control-sm tax-rate" name="items[0][tax_rate]" value="18" min="0" max="100" step="0.01" inputmode="decimal"></td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="row" id="summaryRow">
        <div class="col-md-6">
            <div class="main-card mb-3 card">
                <div class="card-header">
                    <i class="bi bi-card-text me-2"></i> Notes
                </div>
                <div class="card-body">
                    <textarea class="form-control" name="notes" rows="3" placeholder="Notes..." maxlength="150">{{ old('notes') }}</textarea>
                </div>
            </div>
            @include('partials._term_fields', ['name' => 'terms', 'label' => 'Terms & Conditions', 'icon' => 'bi-file-text', 'templates' => $termTemplates, 'value' => old('terms'), 'useDefault' => !session()->hasOldInput()])
            @include('partials._term_fields', ['name' => 'payment_terms', 'label' => 'Payment Terms', 'icon' => 'bi-cash-coin', 'templates' => $paymentTermTemplates, 'value' => old('payment_terms'), 'useDefault' => !session()->hasOldInput()])
        </div>
        <div class="col-md-6">
            <div class="main-card mb-3 card">
                <div class="card-header">
                    <i class="bi bi-calculator me-2"></i> Summary
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="py-2">Subtotal <span class="text-danger">*</span></td>
                            <td class="py-2">
                                <div class="input-group">
                                    <span class="input-group-text js-currency-symbol">₹</span>
                                    <input type="number" class="form-control" min="0" step="1" max="999999999" name="subtotal" id="subtotalInput" value="{{ old('subtotal') }}" placeholder="0" required inputmode="numeric">
                                </div>
                            </td>
                        </tr>
                        <tr id="serviceFeeRow" style="display:none;">
                            <td class="py-2">Service Fee</td>
                            <td class="py-2">
                                <div class="input-group">
                                    <span class="input-group-text js-currency-symbol">₹</span>
                                    <input type="number" class="form-control" min="0" step="1" max="999999999" name="service_fee" id="serviceFeeInput" value="{{ old('service_fee') }}" placeholder="0" inputmode="numeric" disabled>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2">Discount</td>
                            <td class="py-2">
                                <div class="input-group">
                                    <span class="input-group-text js-currency-symbol">₹</span>
                                    <input type="number" class="form-control" min="0" step="1" max="999999999" name="discount" id="discountInput" value="{{ old('discount') }}" placeholder="0" inputmode="numeric">
                                </div>
                            </td>
                        </tr>
                        <tr id="gstRow">
                            <td class="py-2" id="gstLabel">GST</td>
                            <td class="py-2">
                                <div class="input-group">
                                    <span class="input-group-text d-none" id="gstPerLineNote" style="font-size:12px;">Per line</span>
                                    <select class="form-select" style="max-width: 140px;" name="gst_percent" id="gstPercent">
                                        @foreach($gstRates as $rate)
                                        <option value="{{ $rate->percentage }}" {{ $rate->percentage == 18 ? 'selected' : '' }}>{{ $rate->name }}</option>
                                        @endforeach
                                    </select>
                                    <select class="form-select d-none" style="max-width: 140px;" name="vat_percent" id="vatPercent" title="VAT on service fee" disabled>
                                        <option value="5">VAT 5%</option>
                                        <option value="0" {{ old('vat_percent') === '0' ? 'selected' : '' }}>VAT 0%</option>
                                    </select>
                                    <span class="input-group-text text-success" id="gstSign">+ <span class="js-currency-symbol">₹</span></span>
                                    <input type="number" class="form-control" name="gst" id="gstInput" value="0" readonly style="background-color: #e9ecef;">
                                </div>
                                <div class="row g-2 mt-2" id="gstSplitDisplay" style="display: none;">
                                    <div class="col-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">CGST</span>
                                            <input type="number" class="form-control" id="cgstDisplay" readonly style="background-color: #e9ecef;">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">SGST</span>
                                            <input type="number" class="form-control" id="sgstDisplay" readonly style="background-color: #e9ecef;">
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-3 mt-2" id="gstToggles">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="gst_inclusive" id="gstInclusive" value="1">
                                        <label class="form-check-label small" for="gstInclusive">GST Inclusive</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="gst_split" id="gstSplit" value="1">
                                        <label class="form-check-label small" for="gstSplit">Split SGST + CGST</label>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2"><strong>Grand Total <span class="text-danger">*</span></strong></td>
                            <td class="py-2">
                                <div class="input-group">
                                    <span class="input-group-text js-currency-symbol">₹</span>
                                    <input type="number" class="form-control fw-bold" min="0" step="1" max="999999999" name="grand_total" id="grandTotalInput" value="{{ old('grand_total') }}" placeholder="0" required inputmode="numeric">
                                </div>
                            </td>
                        </tr>
                        <tr class="border-top">
                            <td class="py-2">Agent</td>
                            <td class="py-2">
                                <select class="form-select" name="agent_id" id="agentSelect">
                                    <option value="">No Agent</option>
                                    @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}" {{ old('agent_id') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2">Agent Commission<div class="small text-muted">Not added to grand total</div></td>
                            <td class="py-2">
                                <div class="input-group">
                                    <span class="input-group-text js-currency-symbol">₹</span>
                                    <input type="number" class="form-control" min="0" step="1" max="999999999" name="agent_commission" id="agentCommissionInput" value="{{ old('agent_commission') }}" placeholder="0" inputmode="numeric">
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2 mb-4">
        @if(isset($selectedTripId) && $selectedTripId)
            <a href="{{ route('admin.trips.show', $selectedTripId) }}" class="btn btn-outline-secondary">Cancel</a>
        @elseif(isset($selectedCustomerId) && $selectedCustomerId)
            <a href="{{ route('admin.customers.show', $selectedCustomerId) }}" class="btn btn-outline-secondary">Cancel</a>
        @else
            <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary">Cancel</a>
        @endif
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> Create Invoice
        </button>
    </div>
</form>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeItems = document.getElementById('typeItems');
    const typePdf = document.getElementById('typePdf');
    const quotationSelect = document.getElementById('quotation');
    const customerSelect = document.getElementById('customer');
    const companySelect = document.getElementById('company');
    const gstRow = document.getElementById('gstRow');
    const itemsTableBody = document.querySelector('#itemsTable tbody');
    const manualItemsSection = document.getElementById('manualItemsSection');
    const pdfUploadSection = document.getElementById('pdfUploadSection');
    const addItemBtn = document.getElementById('addItemBtn');
    const tripSelect = document.getElementById('trip');
    let itemIndex = 1;

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

    const serviceFeeInput = document.getElementById('serviceFeeInput');
    const vatPercentSelect = document.getElementById('vatPercent');
    const agentSelect = document.getElementById('agentSelect');
    const agentCommissionInput = document.getElementById('agentCommissionInput');

    // 'vat' (UAE: VAT on service fee only), 'gst' (India: per-line tax) or 'none'.
    function taxMode() {
        const opt = companySelect.options[companySelect.selectedIndex];
        return (opt && opt.getAttribute('data-tax-mode')) || 'none';
    }

    // A catalogue service can be sold above its master price, never below it.
    function servicePriceFloor(row) {
        const sel = row.querySelector('.service-select');
        if (!sel || !sel.value) return 0;
        return parseFloat(sel.options[sel.selectedIndex].getAttribute('data-price')) || 0;
    }

    function applyServiceMin(row) {
        const rateEl = row.querySelector('.rate');
        if (!rateEl) return;
        const floor = servicePriceFloor(row);
        rateEl.min = floor;
        rateEl.title = floor > 0 ? 'Cannot be less than service price ' + currencySymbol() + ' ' + floor : '';
        rateEl.classList.toggle('is-invalid', floor > 0 && (parseFloat(rateEl.value) || 0) < floor);
    }

    // Commission is only recorded against an agent
    function updateAgentCommission() {
        agentCommissionInput.disabled = !agentSelect.value;
    }

    function updateGstVisibility() {
        const mode = taxMode();
        const vatMode = mode === 'vat';
        gstRow.style.display = mode === 'none' ? 'none' : '';
        document.getElementById('gstToggles').classList.toggle('d-none', vatMode);
        document.getElementById('serviceFeeRow').style.display = vatMode ? '' : 'none';
        serviceFeeInput.disabled = !vatMode;
        vatPercentSelect.disabled = !vatMode;
        if (vatMode) {
            document.getElementById('gstInclusive').checked = false;
            document.getElementById('gstSplit').checked = false;
        }
        updateTaxColumns();
        updateSummaryTaxMode();
        calculateTotals();
    }

    function calculateTotals() {
        let subtotal = 0;
        const subtotalInput = document.getElementById('subtotalInput');
        const grandTotalInput = document.getElementById('grandTotalInput');
        const gstInclusive = document.getElementById('gstInclusive').checked;
        const gstSign = document.getElementById('gstSign');
        const gstVisible = gstRow.style.display !== 'none';

        let gst, grandTotal, gstPortion;

        if (taxMode() === 'vat') {
            // UAE: VAT (5% or 0%) on the service fee only; the fee is added to the total
            const discount = parseFloat(document.getElementById('discountInput').value) || 0;
            let fees = 0;
            if (typeItems.checked) {
                itemsTableBody.querySelectorAll('tr').forEach(function(row) {
                    subtotal += parseFloat((row.querySelector('.amount') || {}).value) || 0;
                    fees += parseFloat((row.querySelector('.service-fee') || {}).value) || 0;
                });
                subtotal = Math.min(Math.round(subtotal), 99999999);
                subtotalInput.value = subtotal;
                serviceFeeInput.value = Math.round(fees);
            } else {
                subtotal = parseFloat(subtotalInput.value) || 0;
                fees = parseFloat(serviceFeeInput.value) || 0;
            }
            const vat = fees * (parseFloat(vatPercentSelect.value) || 0) / 100;
            grandTotal = Math.round(subtotal + fees - discount + vat);
            document.getElementById('gstInput').value = Math.round(vat);
            gstSign.innerHTML = '+ <span class="js-currency-symbol">' + currencySymbol() + '</span>';
            gstSign.classList.add('text-success');
            document.getElementById('gstSplitDisplay').style.display = 'none';
            grandTotalInput.value = grandTotal;
            grandTotalInput.classList.toggle('is-invalid', grandTotal > 99999999);
            subtotalInput.classList.toggle('is-invalid', subtotal > 99999999);
            return;
        }

        if (typeItems.checked) {
            // Items mode: per-line tax (GST/VAT summed across lines).
            let gstTax = 0, vatTax = 0;
            document.querySelectorAll('#itemsTable tbody tr').forEach(function(row) {
                const amt = parseFloat((row.querySelector('.amount') || {}).value) || 0;
                subtotal += amt;
                if (!gstVisible) return;
                const typeEl = row.querySelector('.tax-type');
                const rateEl = row.querySelector('.tax-rate');
                if (!typeEl || !rateEl) return;
                const type = typeEl.value;
                const rate = parseFloat(rateEl.value) || 0;
                if (rate <= 0 || type === 'none') return;
                if (type === 'gst') {
                    gstTax += gstInclusive ? (amt * rate) / (100 + rate) : (amt * rate) / 100;
                } else if (type === 'vat') {
                    vatTax += (amt * rate) / 100;
                }
            });
            subtotal = Math.min(Math.round(subtotal), 99999999);
            subtotalInput.value = subtotal;

            const discount = parseFloat(document.getElementById('discountInput').value) || 0;
            gstPortion = gstTax;
            gst = gstTax + vatTax;
            // Inclusive applies to GST-type lines only; VAT and exclusive GST add on top.
            const addTax = vatTax + (gstInclusive ? 0 : gstTax);
            grandTotal = Math.round(subtotal - discount + addTax);
            gstSign.innerHTML = (gstInclusive ? '' : '+ ') + '<span class="js-currency-symbol">' + currencySymbol() + '</span>';
            gstSign.classList.toggle('text-success', !gstInclusive);
        } else {
            // PDF mode: document-level GST engine.
            subtotal = parseFloat(subtotalInput.value) || 0;
            const discount = parseFloat(document.getElementById('discountInput').value) || 0;
            const gstPercent = gstVisible ? (parseFloat(document.getElementById('gstPercent').value) || 0) : 0;
            const afterDiscount = subtotal - discount;

            if (gstInclusive && gstPercent > 0) {
                gst = (afterDiscount * gstPercent) / (100 + gstPercent);
                grandTotal = Math.round(afterDiscount);
                gstSign.innerHTML = '<span class="js-currency-symbol">' + currencySymbol() + '</span>';
                gstSign.classList.remove('text-success');
            } else {
                gst = (afterDiscount * gstPercent) / 100;
                grandTotal = Math.round(afterDiscount + gst);
                gstSign.innerHTML = '+ <span class="js-currency-symbol">' + currencySymbol() + '</span>';
                gstSign.classList.add('text-success');
            }
            gstPortion = gst;
        }

        document.getElementById('gstInput').value = Math.round(gst);
        grandTotalInput.value = grandTotal;

        const splitOn = document.getElementById('gstSplit').checked;
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

    function reindexRows() {
        const rows = itemsTableBody.querySelectorAll('tr');
        rows.forEach(function(row, index) {
            row.querySelector('td:first-child').textContent = index + 1;
        });
        itemIndex = rows.length;
    }

    companySelect.addEventListener('change', updateGstVisibility);

    const pdfInput = document.getElementById('invoice_pdf');

    typeItems.addEventListener('change', function() {
        if (this.checked) {
            manualItemsSection.style.display = 'block';
            pdfUploadSection.style.display = 'none';
            addItemBtn.style.display = 'inline-block';
            pdfInput.removeAttribute('required');
            pdfInput.classList.remove('is-invalid');
            updateTaxColumns();
            updateSummaryTaxMode();
            calculateTotals();
        }
    });

    typePdf.addEventListener('change', function() {
        if (this.checked) {
            manualItemsSection.style.display = 'none';
            pdfUploadSection.style.display = 'block';
            addItemBtn.style.display = 'none';
            pdfInput.setAttribute('required', 'required');
            updateTaxColumns();
            updateSummaryTaxMode();
            calculateTotals();
        }
    });

    function updateTaxColumns() {
        const mode = taxMode();
        document.querySelectorAll('.tax-col').forEach(function(el) {
            el.style.display = typeItems.checked && mode === 'gst' ? '' : 'none';
            // VAT invoices tax the service fee only, so line-level tax is not submitted
            el.querySelectorAll('input, select').forEach(function(input) { input.disabled = mode === 'vat'; });
        });
        document.querySelectorAll('.fee-col').forEach(function(el) {
            el.style.display = typeItems.checked && mode === 'vat' ? '' : 'none';
            el.querySelectorAll('input').forEach(function(input) { input.disabled = mode !== 'vat'; });
        });
    }

    function updateSummaryTaxMode() {
        const vatMode = taxMode() === 'vat';
        const perLine = typeItems.checked && taxMode() === 'gst';
        const gstPercent = document.getElementById('gstPercent');
        const gstPerLineNote = document.getElementById('gstPerLineNote');
        const gstLabel = document.getElementById('gstLabel');
        if (gstPercent) {
            gstPercent.classList.toggle('d-none', perLine || vatMode);
            gstPercent.disabled = vatMode;
        }
        vatPercentSelect.classList.toggle('d-none', !vatMode);
        if (gstPerLineNote) gstPerLineNote.classList.toggle('d-none', !perLine);
        if (gstLabel) gstLabel.textContent = vatMode ? 'Total VAT' : (perLine ? 'Tax' : 'GST');
        // Item mode sums the per-line fees; an uploaded PDF takes the fee directly
        serviceFeeInput.readOnly = typeItems.checked;
    }

    function calculateRowAmount(row) {
        const qty = parseFloat(row.querySelector('.qty').value) || 0;
        const rate = parseFloat(row.querySelector('.rate').value) || 0;
        row.querySelector('.amount').value = Math.round(qty * rate);
    }

    addItemBtn.addEventListener('click', function() {
        const showTax = gstRow.style.display !== 'none';
        const newRow = `
            <tr>
                <td class="text-center">${itemIndex + 1}</td>
                <td>
                    <select class="form-select form-select-sm service-select" name="items[${itemIndex}][service_id]">
                        <option value="">— None —</option>
                        @foreach($services as $svc)
                        <option value="{{ $svc->id }}" data-name="{{ $svc->name }}" data-price="{{ $svc->price }}" data-description="{{ $svc->description }}">{{ $svc->name }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" class="service-name" name="items[${itemIndex}][service_name]" value="">
                </td>
                <td><textarea class="form-control form-control-sm item-description" name="items[${itemIndex}][description]" placeholder="Item description" maxlength="150" rows="1"></textarea></td>
                <td><input type="number" class="form-control form-control-sm qty" name="items[${itemIndex}][qty]" value="1" min="1" max="99999" step="1" inputmode="numeric"></td>
                <td><input type="number" class="form-control form-control-sm rate" name="items[${itemIndex}][rate]" placeholder="0" min="0" max="999999999" step="1" inputmode="numeric"></td>
                <td><input type="number" class="form-control form-control-sm amount" name="items[${itemIndex}][amount]" value="0" min="0" step="1" inputmode="numeric"></td>
                <td class="fee-col" style="display:none;"><input type="number" class="form-control form-control-sm service-fee" name="items[${itemIndex}][service_fee]" value="0" min="0" max="999999999" step="1" inputmode="numeric"></td>
                <td class="tax-col" style="${showTax ? '' : 'display:none;'}">
                    <select class="form-select form-select-sm tax-type" name="items[${itemIndex}][tax_type]">
                        <option value="none">None</option>
                        <option value="gst" selected>GST</option>
                        <option value="vat">VAT</option>
                    </select>
                </td>
                <td class="tax-col" style="${showTax ? '' : 'display:none;'}"><input type="number" class="form-control form-control-sm tax-rate" name="items[${itemIndex}][tax_rate]" value="18" min="0" max="100" step="0.01" inputmode="decimal"></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>
            </tr>
        `;
        itemsTableBody.insertAdjacentHTML('beforeend', newRow);
        itemIndex++;
        reindexRows();
        updateTaxColumns();
    });

    itemsTableBody.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            const rows = itemsTableBody.querySelectorAll('tr');
            if (rows.length > 1) {
                e.target.closest('tr').remove();
                reindexRows();
                calculateTotals();
            }
        }
    });

    itemsTableBody.addEventListener('input', function(e) {
        if (e.target.classList.contains('qty') || e.target.classList.contains('rate')) {
            const row = e.target.closest('tr');
            calculateRowAmount(row);
            calculateTotals();
        }
        if (e.target.classList.contains('rate')) {
            applyServiceMin(e.target.closest('tr'));
        }
        if (e.target.classList.contains('amount') || e.target.classList.contains('service-fee')) {
            calculateTotals();
        }
        if (e.target.classList.contains('tax-rate')) {
            calculateTotals();
        }
    });

    itemsTableBody.addEventListener('change', function(e) {
        // A rate below the service price is raised back to the price
        if (e.target.classList.contains('rate')) {
            const row = e.target.closest('tr');
            const floor = servicePriceFloor(row);
            if (floor > 0 && (parseFloat(e.target.value) || 0) < floor) {
                e.target.value = Math.ceil(floor);
                calculateRowAmount(row);
                calculateTotals();
            }
            applyServiceMin(row);
        }
        if (e.target.classList.contains('tax-type')) {
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
            applyServiceMin(row);
            calculateTotals();
        }
    });

    quotationSelect.addEventListener('change', function() {
        if (this.value) {
            const option = this.options[this.selectedIndex];
            const customerId = option.getAttribute('data-customer');
            const companyId = option.getAttribute('data-company');
            const items = JSON.parse(option.getAttribute('data-items') || '[]');
            const subtotal = parseFloat(option.getAttribute('data-subtotal')) || 0;
            const discount = parseFloat(option.getAttribute('data-discount')) || 0;
            const gstPercent = option.getAttribute('data-gst-percent');
            const gstInclusive = option.getAttribute('data-gst-inclusive') === '1';
            const gstSplit = option.getAttribute('data-gst-split') === '1';
            const gst = parseFloat(option.getAttribute('data-gst')) || 0;
            const grandTotal = parseFloat(option.getAttribute('data-grand-total')) || 0;

            if (customerId) {
                unlockCustomer();
                customerSelect.value = customerId;
                filterTripsByCustomer(customerId);
            }
            if (companyId) companySelect.value = companyId;

            updateGstVisibility();

            if (gstPercent) {
                document.getElementById('gstPercent').value = gstPercent;
            }
            document.getElementById('gstInclusive').checked = gstInclusive;
            document.getElementById('gstSplit').checked = gstSplit;

            // Carry the quotation's terms over (keeps current text when the quotation has none)
            [['terms_input', 'data-terms'], ['payment_terms_input', 'data-payment-terms']].forEach(function(pair) {
                const text = option.getAttribute(pair[1]);
                const field = document.getElementById(pair[0]);
                if (field && text) field.value = text;
            });

            if (items && items.length > 0) {
                typeItems.checked = true;
                typeItems.dispatchEvent(new Event('change'));

                const showTaxItem = gstRow.style.display !== 'none';
                let itemsHtml = '';
                items.forEach((item, index) => {
                    const qty = parseFloat(item.qty) || 0;
                    const rate = parseFloat(item.rate) || 0;
                    const amount = item.amount || Math.round(qty * rate);
                    itemsHtml += `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td>
                                <select class="form-select form-select-sm service-select" name="items[${index}][service_id]">
                                    <option value="">— None —</option>
                                    @foreach($services as $svc)
                                    <option value="{{ $svc->id }}" data-name="{{ $svc->name }}" data-price="{{ $svc->price }}" data-description="{{ $svc->description }}" ${String(item.service_id || '') === '{{ $svc->id }}' ? 'selected' : ''}>{{ $svc->name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="service-name" name="items[${index}][service_name]" value="${item.service_name || ''}">
                            </td>
                            <td><textarea class="form-control form-control-sm item-description" name="items[${index}][description]" placeholder="Item description" maxlength="150" rows="1">${item.description || ''}</textarea></td>
                            <td><input type="number" class="form-control form-control-sm qty" name="items[${index}][qty]" value="${item.qty || 1}" min="1" max="99999" step="1" inputmode="numeric"></td>
                            <td><input type="number" class="form-control form-control-sm rate" name="items[${index}][rate]" value="${item.rate || 0}" min="0" max="999999999" step="1" inputmode="numeric"></td>
                            <td><input type="number" class="form-control form-control-sm amount" name="items[${index}][amount]" value="${amount}" min="0" step="1" inputmode="numeric">${item.passenger_type ? `<input type="hidden" name="items[${index}][passenger_type]" value="${item.passenger_type}">` : ''}</td>
                            <td class="fee-col" style="display:none;"><input type="number" class="form-control form-control-sm service-fee" name="items[${index}][service_fee]" value="${item.service_fee || 0}" min="0" max="999999999" step="1" inputmode="numeric"></td>
                            <td class="tax-col" style="${showTaxItem ? '' : 'display:none;'}">
                                <select class="form-select form-select-sm tax-type" name="items[${index}][tax_type]">
                                    <option value="none" ${(item.tax_type || 'gst') === 'none' ? 'selected' : ''}>None</option>
                                    <option value="gst" ${(item.tax_type || 'gst') === 'gst' ? 'selected' : ''}>GST</option>
                                    <option value="vat" ${(item.tax_type || 'gst') === 'vat' ? 'selected' : ''}>VAT</option>
                                </select>
                            </td>
                            <td class="tax-col" style="${showTaxItem ? '' : 'display:none;'}"><input type="number" class="form-control form-control-sm tax-rate" name="items[${index}][tax_rate]" value="${item.tax_rate != null ? item.tax_rate : 18}" min="0" max="100" step="0.01" inputmode="decimal"></td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>
                        </tr>
                    `;
                });
                itemsTableBody.innerHTML = itemsHtml;
                itemIndex = items.length;
                itemsTableBody.querySelectorAll('tr').forEach(applyServiceMin);
            }

            document.getElementById('subtotalInput').value = Math.round(subtotal);
            document.getElementById('discountInput').value = Math.round(discount);
            document.getElementById('gstInput').value = Math.round(gst);
            document.getElementById('grandTotalInput').value = Math.round(grandTotal);
            updateTaxColumns();
            updateSummaryTaxMode();
            calculateTotals();
        }
    });

    document.getElementById('subtotalInput').addEventListener('input', calculateTotals);
    document.getElementById('discountInput').addEventListener('input', calculateTotals);
    document.getElementById('gstPercent').addEventListener('change', calculateTotals);
    document.getElementById('gstInclusive').addEventListener('change', calculateTotals);
    document.getElementById('gstSplit').addEventListener('change', calculateTotals);
    vatPercentSelect.addEventListener('change', calculateTotals);
    serviceFeeInput.addEventListener('input', calculateTotals);
    agentSelect.addEventListener('change', updateAgentCommission);

    itemsTableBody.querySelectorAll('tr').forEach(applyServiceMin);
    updateAgentCommission();
    updateSummaryTaxMode();
    updateGstVisibility();
    calculateTotals();

    // Initial customer <-> trip sync
    if (tripSelect.disabled && tripSelect.value) {
        // Trip preselected & locked (came from a trip page)
        const opt = tripSelect.options[tripSelect.selectedIndex];
        lockCustomer(opt.getAttribute('data-customer'));
    } else if (tripSelect.value) {
        const opt = tripSelect.options[tripSelect.selectedIndex];
        lockCustomer(opt.getAttribute('data-customer'));
    } else if (customerSelect.value) {
        filterTripsByCustomer(customerSelect.value);
    }

    function validateForm() {
        let isValid = true;

        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        const dateInput = document.getElementById('date');
        if (!dateInput.value) {
            dateInput.classList.add('is-invalid');
            isValid = false;
        }

        const companyInput = document.getElementById('company');
        if (companyInput.selectedIndex === 0 || !companyInput.value) {
            companyInput.classList.add('is-invalid');
            companyInput.style.borderColor = '#dc3545';
            const companyFeedback = companyInput.parentElement.querySelector('.invalid-feedback');
            if (companyFeedback) companyFeedback.style.display = 'block';
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

        if (typePdf.checked) {
            const pdfInput = document.getElementById('invoice_pdf');
            if (!pdfInput.files || pdfInput.files.length === 0) {
                pdfInput.classList.add('is-invalid');
                isValid = false;
            }
        } else if (typeItems.checked) {
            const rows = itemsTableBody.querySelectorAll('tr');

            rows.forEach(function(row) {
                const description = row.querySelector('textarea[name$="[description]"]');
                const qty = row.querySelector('input[name$="[qty]"]');
                const rate = row.querySelector('input[name$="[rate]"]');
                if (rate && (parseFloat(rate.value) || 0) < servicePriceFloor(row)) {
                    rate.classList.add('is-invalid');
                    isValid = false;
                }

                if (description && qty && rate) {
                    const descVal = description.value.trim();
                    const qtyVal = parseFloat(qty.value) || 0;
                    const rateVal = parseFloat(rate.value) || 0;

                    if (!descVal) {
                        description.classList.add('is-invalid');
                        isValid = false;
                    }

                    if (qtyVal <= 0) {
                        qty.classList.add('is-invalid');
                        isValid = false;
                    }

                    if (rateVal <= 0) {
                        rate.classList.add('is-invalid');
                        isValid = false;
                    }
                }
            });
        }

        if (!isValid) {
            const firstError = document.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }

        return isValid;
    }

    document.querySelectorAll('#invoiceForm input, #invoiceForm select, #invoiceForm textarea').forEach(el => {
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
                if (this.id === 'company') {
                    this.style.borderColor = '';
                    const feedback = this.parentElement.querySelector('.invalid-feedback');
                    if (feedback) feedback.style.display = '';
                }
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

    document.getElementById('invoiceForm').onsubmit = function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return false;
        }
        return true;
    };

    @if(isset($selectedQuotationId) && $selectedQuotationId)
    quotationSelect.dispatchEvent(new Event('change'));
    @endif
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
#itemsTable {
    min-width: 1080px;
}
#itemsTable .qty {
    min-width: 85px;
}
</style>
@endpush
@endsection
