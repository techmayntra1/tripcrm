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
            @elseif(isset($selectedProjectId) && $selectedProjectId)
                <a href="{{ route('admin.projects.show', $selectedProjectId) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Project
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
                        <select class="form-select" id="company" name="company_id" required>
                            <option value="" data-has-gst="0">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" data-has-gst="{{ !empty($company->gst_number) ? '1' : '0' }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
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
                        <label for="project" class="form-label">Project <span class="text-danger">*</span></label>
                        <select class="form-select" id="project" name="project_id" {{ isset($selectedProjectId) && $selectedProjectId ? 'disabled' : '' }} required>
                            <option value="">Select Project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" data-customer="{{ $project->customer_id }}" {{ (old('project_id', $selectedProjectId ?? '') == $project->id) ? 'selected' : '' }}>{{ $project->project_number }} - {{ $project->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a project</div>
                        @if(isset($selectedProjectId) && $selectedProjectId)
                            <input type="hidden" name="project_id" value="{{ $selectedProjectId }}">
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
                            <th>Description <span class="text-danger">*</span></th>
                            <th width="100">HSN/SAC</th>
                            <th width="110">Unit</th>
                            <th width="110" class="sqft-col" style="display:none;">Height</th>
                            <th width="110" class="sqft-col" style="display:none;">Width</th>
                            <th width="110" class="sqft-col" style="display:none;">Total Sqft</th>
                            <th width="95">Qty <span class="text-danger">*</span></th>
                            <th width="100">Rate (₹)</th>
                            <th width="120">Amount (₹)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">1</td>
                            <td><textarea class="form-control form-control-sm" name="items[0][description]" placeholder="Item description" minlength="3" maxlength="150" rows="1"></textarea></td>
                            <td><input type="text" class="form-control form-control-sm" name="items[0][hsn]" placeholder="HSN" maxlength="8"></td>
                            <td>
                                <select class="form-select form-select-sm unit-select" name="items[0][unit]">
                                    @foreach($units as $unit)
                                    <option value="{{ $unit->short_name }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="sqft-col" style="display:none;"><input type="number" class="form-control form-control-sm item-height" name="items[0][height]" placeholder="0" min="0" step="0.01" inputmode="decimal"></td>
                            <td class="sqft-col" style="display:none;"><input type="number" class="form-control form-control-sm item-width" name="items[0][width]" placeholder="0" min="0" step="0.01" inputmode="decimal"></td>
                            <td class="sqft-col" style="display:none;"><input type="number" class="form-control form-control-sm item-total" name="items[0][total]" placeholder="0" min="0" step="0.01" inputmode="decimal"></td>
                            <td><input type="number" class="form-control form-control-sm qty" name="items[0][qty]" value="1" min="1" max="99999" step="1" inputmode="numeric"></td>
                            <td><input type="number" class="form-control form-control-sm rate" name="items[0][rate]" placeholder="0" min="0" max="999999999" step="1" inputmode="numeric"></td>
                            <td><input type="number" class="form-control form-control-sm amount" name="items[0][amount]" value="0" min="0" step="1" inputmode="numeric"></td>
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
                    <textarea class="form-control" name="notes" rows="4" placeholder="Payment terms, notes, etc." maxlength="150">{{ old('notes') }}</textarea>
                </div>
            </div>
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
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" min="0" step="1" max="999999999" name="subtotal" id="subtotalInput" value="{{ old('subtotal') }}" placeholder="0" required inputmode="numeric">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2">Discount</td>
                            <td class="py-2">
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" min="0" step="1" max="999999999" name="discount" id="discountInput" value="{{ old('discount') }}" placeholder="0" inputmode="numeric">
                                </div>
                            </td>
                        </tr>
                        <tr id="gstRow">
                            <td class="py-2">GST</td>
                            <td class="py-2">
                                <div class="input-group">
                                    <select class="form-select" style="max-width: 140px;" name="gst_percent" id="gstPercent">
                                        @foreach($gstRates as $rate)
                                        <option value="{{ $rate->percentage }}" {{ $rate->percentage == 18 ? 'selected' : '' }}>{{ $rate->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-text text-success" id="gstSign">+ ₹</span>
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
                                <div class="d-flex flex-wrap gap-3 mt-2">
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
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control fw-bold" min="0" step="1" max="999999999" name="grand_total" id="grandTotalInput" value="{{ old('grand_total') }}" placeholder="0" required inputmode="numeric">
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2 mb-4">
        @if(isset($selectedProjectId) && $selectedProjectId)
            <a href="{{ route('admin.projects.show', $selectedProjectId) }}" class="btn btn-outline-secondary">Cancel</a>
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
    const projectSelect = document.getElementById('project');
    let itemIndex = 1;

    // ----- Customer <-> Project linking -----
    const projectOptions = Array.from(projectSelect.options);

    function filterProjectsByCustomer(customerId) {
        let currentStillValid = false;
        projectOptions.forEach(function(opt) {
            if (!opt.value) { opt.hidden = false; return; }
            const belongs = !customerId || opt.getAttribute('data-customer') === String(customerId);
            opt.hidden = !belongs;
            if (belongs && opt.value === projectSelect.value) currentStillValid = true;
        });
        if (projectSelect.value && !currentStillValid) projectSelect.value = '';
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
        filterProjectsByCustomer(this.value);
    });

    projectSelect.addEventListener('change', function() {
        if (this.value) {
            const opt = this.options[this.selectedIndex];
            lockCustomer(opt.getAttribute('data-customer'));
        } else {
            unlockCustomer();
        }
    });

    function updateGstVisibility() {
        const selectedOption = companySelect.options[companySelect.selectedIndex];
        const hasGst = selectedOption && selectedOption.getAttribute('data-has-gst') === '1';
        gstRow.style.display = hasGst ? '' : 'none';
        calculateTotals();
    }

    function calculateTotals() {
        let subtotal = 0;
        const subtotalInput = document.getElementById('subtotalInput');
        const grandTotalInput = document.getElementById('grandTotalInput');
        const gstInclusive = document.getElementById('gstInclusive').checked;
        const gstSign = document.getElementById('gstSign');

        if (typeItems.checked) {
            document.querySelectorAll('#itemsTable .amount').forEach(function(el) {
                subtotal += parseFloat(el.value) || 0;
            });
            subtotal = Math.min(Math.round(subtotal), 99999999);
            subtotalInput.value = subtotal;
        } else {
            subtotal = parseFloat(subtotalInput.value) || 0;
        }
        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        const gstVisible = gstRow.style.display !== 'none';
        const gstPercent = gstVisible ? (parseFloat(document.getElementById('gstPercent').value) || 0) : 0;
        const afterDiscount = subtotal - discount;

        let gst, grandTotal;
        if (gstInclusive && gstPercent > 0) {
            gst = (afterDiscount * gstPercent) / (100 + gstPercent);
            grandTotal = Math.round(afterDiscount);
            gstSign.textContent = '₹';
            gstSign.classList.remove('text-success');
        } else {
            gst = (afterDiscount * gstPercent) / 100;
            grandTotal = Math.round(afterDiscount + gst);
            gstSign.textContent = '+ ₹';
            gstSign.classList.add('text-success');
        }

        document.getElementById('gstInput').value = Math.round(gst);
        grandTotalInput.value = grandTotal;

        const splitOn = document.getElementById('gstSplit').checked;
        document.getElementById('gstSplitDisplay').style.display = splitOn ? '' : 'none';
        if (splitOn) {
            const half = Math.round(gst / 2);
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
        }
    });

    typePdf.addEventListener('change', function() {
        if (this.checked) {
            manualItemsSection.style.display = 'none';
            pdfUploadSection.style.display = 'block';
            addItemBtn.style.display = 'none';
            pdfInput.setAttribute('required', 'required');
        }
    });

    function hasSqftSelected() {
        const unitSelects = itemsTableBody.querySelectorAll('.unit-select');
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
        const newRow = `
            <tr>
                <td class="text-center">${itemIndex + 1}</td>
                <td><textarea class="form-control form-control-sm" name="items[${itemIndex}][description]" placeholder="Item description" maxlength="150" rows="1"></textarea></td>
                <td><input type="text" class="form-control form-control-sm" name="items[${itemIndex}][hsn]" placeholder="HSN"></td>
                <td>
                    <select class="form-select form-select-sm unit-select" name="items[${itemIndex}][unit]">
                        @foreach($units as $unit)
                        <option value="{{ $unit->short_name }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="sqft-col" style="${showSqft ? '' : 'display:none;'}"><input type="number" class="form-control form-control-sm item-height" name="items[${itemIndex}][height]" placeholder="0" min="0" step="0.01" inputmode="decimal"></td>
                <td class="sqft-col" style="${showSqft ? '' : 'display:none;'}"><input type="number" class="form-control form-control-sm item-width" name="items[${itemIndex}][width]" placeholder="0" min="0" step="0.01" inputmode="decimal"></td>
                <td class="sqft-col" style="${showSqft ? '' : 'display:none;'}"><input type="number" class="form-control form-control-sm item-total" name="items[${itemIndex}][total]" placeholder="0" min="0" step="0.01" inputmode="decimal"></td>
                <td><input type="number" class="form-control form-control-sm qty" name="items[${itemIndex}][qty]" value="1" min="1" max="99999" step="1" inputmode="numeric"></td>
                <td><input type="number" class="form-control form-control-sm rate" name="items[${itemIndex}][rate]" placeholder="0" min="0" max="999999999" step="1" inputmode="numeric"></td>
                <td><input type="number" class="form-control form-control-sm amount" name="items[${itemIndex}][amount]" value="0" min="0" step="1" inputmode="numeric"></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>
            </tr>
        `;
        itemsTableBody.insertAdjacentHTML('beforeend', newRow);
        itemIndex++;
        reindexRows();
    });

    itemsTableBody.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            const rows = itemsTableBody.querySelectorAll('tr');
            if (rows.length > 1) {
                e.target.closest('tr').remove();
                reindexRows();
                toggleSqftColumns();
                calculateTotals();
            }
        }
    });

    itemsTableBody.addEventListener('input', function(e) {
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
        if (e.target.classList.contains('amount')) {
            calculateTotals();
        }
    });

    itemsTableBody.addEventListener('change', function(e) {
        if (e.target.classList.contains('unit-select')) {
            toggleSqftColumns();
            const row = e.target.closest('tr');
            calculateRowAmount(row);
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
                filterProjectsByCustomer(customerId);
            }
            if (companyId) companySelect.value = companyId;

            updateGstVisibility();

            if (gstPercent) {
                document.getElementById('gstPercent').value = gstPercent;
            }
            document.getElementById('gstInclusive').checked = gstInclusive;
            document.getElementById('gstSplit').checked = gstSplit;

            if (items && items.length > 0) {
                typeItems.checked = true;
                typeItems.dispatchEvent(new Event('change'));

                let hasSqftItem = items.some(item => (item.unit || '').toLowerCase() === 'sqft' || item.height || item.width || item.total);
                let itemsHtml = '';
                items.forEach((item, index) => {
                    const isSqft = (item.unit || '').toLowerCase() === 'sqft';
                    const total = parseFloat(item.total) || 0;
                    const qty = parseFloat(item.qty) || 0;
                    const rate = parseFloat(item.rate) || 0;
                    const amount = item.amount || (isSqft ? Math.round(total * qty * rate) : Math.round(qty * rate));
                    itemsHtml += `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td><textarea class="form-control form-control-sm" name="items[${index}][description]" placeholder="Item description" maxlength="150" rows="1">${item.description || ''}</textarea></td>
                            <td><input type="text" class="form-control form-control-sm" name="items[${index}][hsn]" value="${item.hsn || ''}" placeholder="HSN"></td>
                            <td>
                                <select class="form-select form-select-sm unit-select" name="items[${index}][unit]">
                                    @foreach($units as $unit)
                                    <option value="{{ $unit->short_name }}" ${(item.unit || '').toLowerCase() === '{{ strtolower($unit->short_name) }}' ? 'selected' : ''}>{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="sqft-col" style="${hasSqftItem ? '' : 'display:none;'}"><input type="number" class="form-control form-control-sm item-height" name="items[${index}][height]" value="${item.height || ''}" placeholder="0" min="0" step="0.01" inputmode="decimal"></td>
                            <td class="sqft-col" style="${hasSqftItem ? '' : 'display:none;'}"><input type="number" class="form-control form-control-sm item-width" name="items[${index}][width]" value="${item.width || ''}" placeholder="0" min="0" step="0.01" inputmode="decimal"></td>
                            <td class="sqft-col" style="${hasSqftItem ? '' : 'display:none;'}"><input type="number" class="form-control form-control-sm item-total" name="items[${index}][total]" value="${item.total || ''}" placeholder="0" min="0" step="0.01" inputmode="decimal"></td>
                            <td><input type="number" class="form-control form-control-sm qty" name="items[${index}][qty]" value="${item.qty || 1}" min="1" max="99999" step="1" inputmode="numeric"></td>
                            <td><input type="number" class="form-control form-control-sm rate" name="items[${index}][rate]" value="${item.rate || 0}" min="0" max="999999999" step="1" inputmode="numeric"></td>
                            <td><input type="number" class="form-control form-control-sm amount" name="items[${index}][amount]" value="${amount}" min="0" step="1" inputmode="numeric"></td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>
                        </tr>
                    `;
                });
                itemsTableBody.innerHTML = itemsHtml;
                itemIndex = items.length;
                toggleSqftColumns();
            }

            document.getElementById('subtotalInput').value = Math.round(subtotal);
            document.getElementById('discountInput').value = Math.round(discount);
                        document.getElementById('gstInput').value = Math.round(gst);
            document.getElementById('grandTotalInput').value = Math.round(grandTotal);
        }
    });

    document.getElementById('subtotalInput').addEventListener('input', calculateTotals);
    document.getElementById('discountInput').addEventListener('input', calculateTotals);
    document.getElementById('gstPercent').addEventListener('change', calculateTotals);
    document.getElementById('gstInclusive').addEventListener('change', calculateTotals);
    document.getElementById('gstSplit').addEventListener('change', calculateTotals);

    updateGstVisibility();
    calculateTotals();

    // Initial customer <-> project sync
    if (projectSelect.disabled && projectSelect.value) {
        // Project preselected & locked (came from a project page)
        const opt = projectSelect.options[projectSelect.selectedIndex];
        lockCustomer(opt.getAttribute('data-customer'));
    } else if (projectSelect.value) {
        const opt = projectSelect.options[projectSelect.selectedIndex];
        lockCustomer(opt.getAttribute('data-customer'));
    } else if (customerSelect.value) {
        filterProjectsByCustomer(customerSelect.value);
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

        const projectInput = document.getElementById('project');
        if ((projectInput.selectedIndex === 0 || !projectInput.value) && !projectInput.disabled) {
            projectInput.classList.add('is-invalid');
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
                const unitSelect = row.querySelector('.unit-select');
                const isSqft = unitSelect && unitSelect.value.toLowerCase() === 'sqft';

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

                    if (!isSqft && rateVal <= 0) {
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
@endsection
