@extends('layouts.app')
@section('title', 'Create Quotation')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-file-earmark-plus-fill icon-gradient bg-premium-dark"></i>
            </div>
            <div>
                Create Quotation
                
            </div>
        </div>
        <div class="page-title-actions">
            @if(str_contains(url()->previous(), '/customers'))
                @if(str_contains(url()->previous(), '/trashed'))
                    <a href="{{ route('admin.customers.trashed') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Customers
                    </a>
                @else
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Customers
                    </a>
                @endif
            @else
                <a href="{{ route('admin.quotations.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            @endif
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
<form action="{{ route('admin.quotations.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf
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
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                        <div class="invalid-feedback">Please select a date</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="company" class="form-label">Company <span class="text-danger">*</span></label>
                        <select class="form-select @error('company_id') is-invalid @enderror" id="company" name="company_id" required>
                            <option value="" data-has-gst="0">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" data-has-gst="{{ !empty($company->gst_number) ? '1' : '0' }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a company</div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="customer" class="form-label">Customer <span class="text-danger">*</span></label>
                        <select class="form-select @error('customer_id') is-invalid @enderror" id="customer" name="customer_id" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }} - {{ $customer->mobile }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a customer</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="project" class="form-label">Project</label>
                        <select class="form-select @error('project_id') is-invalid @enderror" id="project" name="project_id">
                            <option value="">Select Project (Optional)</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" data-customer="{{ $project->customer_id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>{{ $project->project_number }} - {{ $project->name }}</option>
                            @endforeach
                        </select>
                        @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject') }}" placeholder="e.g., Interior Design Work for 3BHK" maxlength="150">
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
            <div class="d-flex align-items-center gap-3">
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="quotation_type" id="typePdf" value="pdf" checked>
                    <label class="btn btn-outline-primary btn-sm" for="typePdf">
                        <i class="bi bi-file-pdf me-1"></i> Upload PDF
                    </label>
                    <input type="radio" class="btn-check" name="quotation_type" id="typeItems" value="items">
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
                    <label for="quotation_pdf" class="form-label">Upload Quotation PDF <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="quotation_pdf" name="quotation_pdf" accept=".pdf" required>
                    <div class="invalid-feedback">Please upload a quotation PDF</div>
                    <small class="text-muted">Max 10MB. Upload your quotation document.</small>
                </div>
                <div class="col-md-7">
                    <label for="pdf_description" class="form-label">Description</label>
                    <input type="text" class="form-control" id="pdf_description" name="pdf_description" placeholder="Brief description of the quotation...">
                </div>
            </div>
        </div>
        <div class="card-body p-0" id="manualItemsSection" style="display: none;">
            <table class="table table-bordered mb-0" id="itemsTable">
                <thead class="table-light">
                    <tr>
                        <th width="40">#</th>
                        <th>Description <span class="text-danger">*</span></th>
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
                    <tbody id="itemsBody">
                        <tr>
                            <td>1</td>
                            <td><textarea class="form-control form-control-sm" name="items[0][description]" placeholder="Item description" required minlength="1" maxlength="150" rows="1"></textarea></td>
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
                            <td><input type="number" class="form-control form-control-sm qty" name="items[0][qty]" value="1" min="1" max="99999" step="1" required inputmode="numeric"></td>
                            <td><input type="number" class="form-control form-control-sm rate" name="items[0][rate]" placeholder="0" min="0" max="999999999" step="1" inputmode="numeric"></td>
                            <td><input type="number" class="form-control form-control-sm amount" name="items[0][amount]" placeholder="0" min="0" step="1" inputmode="numeric"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>
                        </tr>
                    </tbody>
                </table>
        </div>
    </div>
    <div class="row" id="summaryRow">
        <div class="col-md-6">
            <div class="main-card mb-3 card">
                <div class="card-header">
                    <i class="bi bi-card-text me-2"></i> Terms & Conditions
                </div>
                <div class="card-body">
                    <textarea class="form-control" name="terms" rows="4" placeholder="Enter terms and conditions..." maxlength="2000"></textarea>
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
                            <td class="py-2">Subtotal</td>
                            <td class="py-2">
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" min="0" step="1" max="999999999" name="subtotal" id="subtotalInput" placeholder="0" inputmode="numeric">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2">Discount</td>
                            <td class="py-2">
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" min="0" step="1" max="999999999" name="discount" id="discountInput" placeholder="0" inputmode="numeric">
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
                                    <input type="number" class="form-control" name="gst" id="gstInput" placeholder="0" readonly style="background-color: #e9ecef;">
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
                            <td class="py-2"><strong>Grand Total</strong></td>
                            <td class="py-2">
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control fw-bold" min="0" step="1" max="999999999" name="grand_total" id="grandTotalInput" placeholder="0" inputmode="numeric">
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2 mb-4">
        @if(str_contains(url()->previous(), '/customers'))
            @if(str_contains(url()->previous(), '/trashed'))
                <a href="{{ route('admin.customers.trashed') }}" class="btn btn-outline-secondary">Cancel</a>
            @else
                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">Cancel</a>
            @endif
        @else
            <a href="{{ route('admin.quotations.index') }}" class="btn btn-outline-secondary">Cancel</a>
        @endif
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> Save Quotation
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
    const typeItems = document.getElementById('typeItems');
    const typePdf = document.getElementById('typePdf');
    const manualItemsSection = document.getElementById('manualItemsSection');
    const pdfUploadSection = document.getElementById('pdfUploadSection');
    const addItemBtn = document.getElementById('addItemBtn');
    const customerSelect = document.getElementById('customer');
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

    // Initial customer <-> project sync
    if (projectSelect.value) {
        const opt = projectSelect.options[projectSelect.selectedIndex];
        lockCustomer(opt.getAttribute('data-customer'));
    } else if (customerSelect.value) {
        filterProjectsByCustomer(customerSelect.value);
    }

    function toggleItemsRequired(isRequired) {
        const inputs = manualItemsSection.querySelectorAll('textarea[name*="[description]"], input[name*="[qty]"], input[name*="[rate]"]');
        inputs.forEach(function(input) {
            if (isRequired) {
                input.setAttribute('required', 'required');
            } else {
                input.removeAttribute('required');
            }
        });
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

    function updateGstVisibility() {
        const selectedOption = companySelect.options[companySelect.selectedIndex];
        const hasGst = selectedOption && selectedOption.getAttribute('data-has-gst') === '1';
        gstRow.style.display = hasGst ? '' : 'none';
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

    const pdfInput = document.getElementById('quotation_pdf');

    typeItems.addEventListener('change', function() {
        if (this.checked) {
            manualItemsSection.style.display = 'block';
            pdfUploadSection.style.display = 'none';
            addItemBtn.style.display = 'inline-block';
            toggleItemsRequired(true);
            pdfInput.removeAttribute('required');
            pdfInput.classList.remove('is-invalid');
            calculateTotals();
        }
    });

    typePdf.addEventListener('change', function() {
        if (this.checked) {
            manualItemsSection.style.display = 'none';
            pdfUploadSection.style.display = 'block';
            addItemBtn.style.display = 'none';
            toggleItemsRequired(false);
            pdfInput.setAttribute('required', 'required');
        }
    });

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
        const newRow = `
            <tr>
                <td>${itemIndex + 1}</td>
                <td><textarea class="form-control form-control-sm" name="items[${itemIndex}][description]" placeholder="Item description" required minlength="1" maxlength="150" rows="1"></textarea></td>
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
                <td><input type="number" class="form-control form-control-sm qty" name="items[${itemIndex}][qty]" value="1" min="1" max="99999" step="1" required inputmode="numeric"></td>
                <td><input type="number" class="form-control form-control-sm rate" name="items[${itemIndex}][rate]" placeholder="0" min="0" max="999999999" step="1" inputmode="numeric"></td>
                <td><input type="number" class="form-control form-control-sm amount" name="items[${itemIndex}][amount]" placeholder="0" min="0" step="1" inputmode="numeric"></td>
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
        if (e.target.classList.contains('amount')) {
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

        if (typePdf.checked && (!pdfInput.files || pdfInput.files.length === 0)) {
            pdfInput.classList.add('is-invalid');
            isValid = false;
        }

        if (typeItems.checked) {
            const rows = itemsBody.querySelectorAll('tr');
            rows.forEach(function(row) {
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

    toggleItemsRequired(false);
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
