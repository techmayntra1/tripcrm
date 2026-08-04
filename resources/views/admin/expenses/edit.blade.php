@extends('layouts.app')
@section('title', 'Edit Expense')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-pencil-square icon-gradient bg-danger"></i>
            </div>
            <div>
                Edit Expense
                <div class="page-title-subheading">{{ $expense->expense_number }}</div>
            </div>
        </div>
        <div class="page-title-actions">
            @if(isset($fromProject) && $fromProject)
                <a href="{{ route('admin.projects.show', $fromProject) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Project
                </a>
            @elseif($expense->project_id)
                <a href="{{ route('admin.projects.show', $expense->project_id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Project
                </a>
            @elseif($expense->vendor_id)
                <a href="{{ route('admin.vendors.show', $expense->vendor_id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Vendor
                </a>
            @else
                <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary">
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
@php
    $hasItems = is_array($expense->items) && count($expense->items) > 0;
@endphp
<form action="{{ route('admin.expenses.update', $expense) }}" method="POST" enctype="multipart/form-data" id="expenseForm" novalidate>
    @csrf
    @method('PUT')
    @if(isset($fromProject))
    <input type="hidden" name="from_project" value="{{ $fromProject }}">
    @endif
    <div class="main-card mb-3 card">
        <div class="card-header">
            <i class="bi bi-cash-coin me-2"></i> Expense Details
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="expense_type" class="form-label">Expense Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="expense_type" name="expense_type" required>
                            <option value="">Select Type</option>
                            @foreach($expenseTypes as $type)
                            <option value="{{ $type->slug }}" {{ $expense->expense_type == $type->slug ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select expense type</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="expense_date" class="form-label">Expense Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="expense_date" name="expense_date" value="{{ $expense->expense_date->format('Y-m-d') }}" required>
                        <div class="invalid-feedback">Please select date</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="payment_mode_id" class="form-label">Payment Mode <span class="text-danger">*</span></label>
                        <select class="form-select" id="payment_mode_id" name="payment_mode_id" required>
                            <option value="">Select Mode</option>
                            @foreach($paymentModes as $mode)
                            <option value="{{ $mode->id }}" data-slug="{{ $mode->slug }}" {{ $expense->payment_mode_id == $mode->id ? 'selected' : '' }}>{{ $mode->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select payment mode</div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4" id="bank_section">
                    <div class="mb-3">
                        <label for="bank_id" class="form-label">Bank Account <span class="text-danger">*</span></label>
                        <select class="form-select" id="bank_id" name="bank_id" required>
                            <option value="">Select Account</option>
                            @foreach($banks as $bank)
                            <option value="{{ $bank->id }}" {{ $expense->bank_id == $bank->id ? 'selected' : '' }}>{{ $bank->bank_name }} - {{ $bank->account_number }} ({{ formatMoney($bank->balance) }})</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select bank account</div>
                    </div>
                </div>
                <input type="hidden" id="cash_account_id" value="{{ $cashAccount->id ?? '' }}">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $expense->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="payment_status" class="form-label">Payment Status</label>
                        <select class="form-select" id="payment_status" name="payment_status">
                            <option value="unpaid" {{ $expense->payment_status == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="paid" {{ $expense->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row" id="project_vendor_row" style="{{ in_array($expense->expense_type, ['project', 'vendor']) ? '' : 'display: none;' }}">
                <div class="col-md-4" id="project_section">
                    <div class="mb-3">
                        <label for="project_id" class="form-label">Project <span class="text-danger project-required-star" style="{{ in_array($expense->expense_type, ['project', 'vendor']) ? '' : 'display: none;' }}">*</span></label>
                        @if($fromProject && $expense->project_id)
                            <input type="hidden" name="project_id" value="{{ $expense->project_id }}">
                            <select class="form-select" id="project_id" disabled>
                                <option value="{{ $expense->project_id }}" data-vendors="{{ json_encode($expense->project->assigned_vendor_ids ?? []) }}" selected>
                                    {{ $expense->project->project_number }} - {{ $expense->project->name }}
                                </option>
                            </select>
                        @else
                            <select class="form-select" id="project_id" name="project_id" {{ in_array($expense->expense_type, ['project', 'vendor']) ? 'required' : '' }}>
                                <option value="">Select Project</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" data-vendors="{{ json_encode($project->assigned_vendor_ids ?? []) }}" {{ $expense->project_id == $project->id ? 'selected' : '' }}>
                                        {{ $project->project_number }} - {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        <div class="invalid-feedback">Please select a project</div>
                    </div>
                </div>
                <div class="col-md-4" id="vendor_section" style="{{ $expense->expense_type == 'vendor' ? '' : 'display: none;' }}">
                    <div class="mb-3">
                        <label for="vendor_id" class="form-label">Vendor <span class="text-danger">*</span></label>
                        <select class="form-select" id="vendor_id" name="vendor_id">
                            <option value="">Select Vendor</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" data-outstanding="{{ formatMoney($vendor->outstanding) }}" {{ $expense->vendor_id == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }} (Outstanding: {{ formatMoney($vendor->outstanding) }})
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a vendor</div>
                        <small class="text-muted vendor-filter-hint" style="display: none;">Showing vendors assigned to selected project</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="main-card mb-3 card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-list-check me-2"></i> Expense Details
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="entry_type" id="typeBill" value="bill" {{ !$hasItems ? 'checked' : '' }}>
                    <label class="btn btn-outline-warning btn-sm text-dark" for="typeBill">
                        <i class="bi bi-receipt me-1"></i> Upload Bill/Receipt
                    </label>
                    <input type="radio" class="btn-check" name="entry_type" id="typeItems" value="items" {{ $hasItems ? 'checked' : '' }}>
                    <label class="btn btn-outline-warning btn-sm text-dark" for="typeItems">
                        <i class="bi bi-list-ul me-1"></i> Add Items
                    </label>
                </div>
                <button type="button" class="btn btn-sm btn-warning" id="addItemBtn" style="{{ $hasItems ? '' : 'display: none;' }}">
                    <i class="bi bi-plus-lg me-1"></i> Add Item
                </button>
            </div>
        </div>
        <div class="card-body" id="billUploadSection" style="{{ $hasItems ? 'display: none;' : '' }}">
            <div class="row">
                <div class="col-md-6">
                    <label for="attachment" class="form-label">Upload Bill/Receipt</label>
                    @if($expense->attachment)
                        <div class="mb-2">
                            <a href="{{ asset('storage/' . $expense->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i> View Current
                            </a>
                        </div>
                    @endif
                    <input type="file" class="form-control" id="attachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="invalid-feedback">Please upload a bill/receipt file</div>
                    <small class="text-muted">Accepted: PDF, JPG, PNG (Max 2MB)</small>
                </div>
                <div class="col-md-6">
                    <label for="bill_description" class="form-label">Description</label>
                    <input type="text" class="form-control" id="bill_description" name="bill_description" value="{{ $expense->description }}" placeholder="Bill number, reference, or notes..." maxlength="150">
                </div>
            </div>
        </div>
        <div class="card-body p-0" id="manualItemsSection" style="{{ $hasItems ? '' : 'display: none;' }}">
            <div class="table-responsive">
                <table class="table table-bordered mb-0" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="40">#</th>
                            <th width="280">Description / Title</th>
                            <th width="100">Unit</th>
                            <th width="100">Quantity</th>
                            <th width="120">Rate</th>
                            <th width="120">Total</th>
                            <th width="40"></th>
                        </tr>
                    </thead>
                    <tbody id="expenseItemsBody">
                        @if($hasItems)
                            @foreach($expense->items as $index => $item)
                            <tr>
                                <td class="text-center align-middle">{{ $index + 1 }}</td>
                                <td><textarea class="form-control form-control-sm" name="items[{{ $index }}][description]" placeholder="Enter description" maxlength="150" rows="1">{{ $item['description'] ?? '' }}</textarea></td>
                                <td>
                                    <select class="form-select form-select-sm" name="items[{{ $index }}][unit]">
                                        @foreach($units as $unit)
                                        <option value="{{ $unit->short_name }}" {{ ($item['unit'] ?? '') == $unit->short_name ? 'selected' : '' }}>{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" class="form-control form-control-sm exp-qty" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" min="1" max="99999" step="1" inputmode="numeric"></td>
                                <td><input type="number" class="form-control form-control-sm exp-rate" name="items[{{ $index }}][rate]" value="{{ $item['rate'] ?? '' }}" placeholder="0" min="0" max="999999999" step="1" inputmode="numeric"></td>
                                <td><input type="number" class="form-control form-control-sm exp-total" name="items[{{ $index }}][total]" value="{{ $item['total'] ?? '' }}" readonly style="background-color: #f8f9fa;"></td>
                                <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td class="text-center align-middle">1</td>
                                <td><textarea class="form-control form-control-sm" name="items[0][description]" placeholder="Enter description" maxlength="150" rows="1"></textarea></td>
                                <td>
                                    <select class="form-select form-select-sm" name="items[0][unit]">
                                        @foreach($units as $unit)
                                        <option value="{{ $unit->short_name }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" class="form-control form-control-sm exp-qty" name="items[0][quantity]" value="1" min="1" max="99999" step="1" inputmode="numeric"></td>
                                <td><input type="number" class="form-control form-control-sm exp-rate" name="items[0][rate]" placeholder="0" min="0" max="999999999" step="1" inputmode="numeric"></td>
                                <td><input type="number" class="form-control form-control-sm exp-total" name="items[0][total]" readonly style="background-color: #f8f9fa;"></td>
                                <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6"></div>
        <div class="col-md-6">
            <div class="main-card mb-3 card">
                <div class="card-header">
                    <i class="bi bi-calculator me-2"></i> Summary
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="py-2">Sub Total <span class="text-danger">*</span></td>
                            <td class="py-2">
                                <div class="input-group has-validation">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" min="1" step="1" max="999999999" name="sub_total" id="exp_sub_total" value="{{ $expense->sub_total }}" placeholder="0" required inputmode="numeric">
                                    <div class="invalid-feedback">Please enter a valid amount (1 - 99999999)</div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2"><strong>Grand Total <span class="text-danger">*</span></strong></td>
                            <td class="py-2">
                                <div class="input-group has-validation">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control fw-bold" min="1" step="1" max="999999999" name="grand_total" id="exp_grand_total" value="{{ $expense->grand_total }}" placeholder="0" required inputmode="numeric">
                                    <div class="invalid-feedback">Please enter a valid amount (1 - 99999999)</div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2 mb-4">
        @if($expense->vendor_id)
            <a href="{{ route('admin.vendors.show', $expense->vendor_id) }}" class="btn btn-outline-secondary">Cancel</a>
        @else
            <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary">Cancel</a>
        @endif
        <button type="submit" class="btn btn-warning">
            <i class="bi bi-check-lg me-1"></i> Update Expense
        </button>
    </div>
</form>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeItems = document.getElementById('typeItems');
    const typeBill = document.getElementById('typeBill');
    const billUploadSection = document.getElementById('billUploadSection');
    const manualItemsSection = document.getElementById('manualItemsSection');
    const addItemBtn = document.getElementById('addItemBtn');
    const itemsBody = document.getElementById('expenseItemsBody');
    let itemIndex = {{ $hasItems ? count($expense->items) : 1 }};

    const attachmentInput = document.getElementById('attachment');
    const hasExistingAttachment = {{ $expense->attachment ? 'true' : 'false' }};

    typeItems.addEventListener('change', function() {
        if (this.checked) {
            manualItemsSection.style.display = 'block';
            billUploadSection.style.display = 'none';
            addItemBtn.style.display = 'inline-block';
            attachmentInput.removeAttribute('required');
            attachmentInput.classList.remove('is-invalid');
        }
    });

    typeBill.addEventListener('change', function() {
        if (this.checked) {
            manualItemsSection.style.display = 'none';
            billUploadSection.style.display = 'block';
            addItemBtn.style.display = 'none';
        }
    });

    addItemBtn.addEventListener('click', function() {
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td class="text-center align-middle">${itemIndex + 1}</td>
            <td><textarea class="form-control form-control-sm" name="items[${itemIndex}][description]" placeholder="Enter description" maxlength="150" rows="1"></textarea></td>
            <td>
                <select class="form-select form-select-sm" name="items[${itemIndex}][unit]">
                    @foreach($units as $unit)<option value="{{ $unit->short_name }}">{{ $unit->name }}</option>@endforeach
                </select>
            </td>
            <td><input type="number" class="form-control form-control-sm exp-qty" name="items[${itemIndex}][quantity]" value="1" min="1" max="99999" step="1" inputmode="numeric"></td>
            <td><input type="number" class="form-control form-control-sm exp-rate" name="items[${itemIndex}][rate]" placeholder="0" min="0" max="999999999" step="1" inputmode="numeric"></td>
            <td><input type="number" class="form-control form-control-sm exp-total" name="items[${itemIndex}][total]" readonly style="background-color: #f8f9fa;"></td>
            <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>
        `;
        itemsBody.appendChild(newRow);
        itemIndex++;
        reindexRows();
    });

    itemsBody.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            const rows = itemsBody.querySelectorAll('tr');
            if (rows.length > 1) {
                e.target.closest('tr').remove();
                reindexRows();
                calcExpenseTotal();
            }
        }
    });

    itemsBody.addEventListener('input', function(e) {
        if (e.target.classList.contains('exp-qty') || e.target.classList.contains('exp-rate')) {
            const row = e.target.closest('tr');
            const qty = parseFloat(row.querySelector('.exp-qty').value) || 0;
            const rate = parseFloat(row.querySelector('.exp-rate').value) || 0;
            row.querySelector('.exp-total').value = Math.trunc(qty * rate);
            calcExpenseTotal();
        }
    });

    function reindexRows() {
        const rows = itemsBody.querySelectorAll('tr');
        rows.forEach(function(row, index) {
            row.querySelector('td:first-child').textContent = index + 1;
        });
        itemIndex = rows.length;
    }

    // Store all vendor options for filtering
    const allVendorOptions = Array.from(document.getElementById('vendor_id').querySelectorAll('option')).map(opt => ({
        value: opt.value,
        text: opt.textContent.trim(),
        outstanding: opt.dataset.outstanding
    }));
    const currentVendorId = '{{ $expense->vendor_id }}';

    document.getElementById('expense_type').addEventListener('change', function() {
        var projectVendorRow = document.getElementById('project_vendor_row');
        var vendorSection = document.getElementById('vendor_section');
        var projectSelect = document.getElementById('project_id');
        var projectRequiredStar = document.querySelector('.project-required-star');
        var vendorFilterHint = document.querySelector('.vendor-filter-hint');

        if (projectVendorRow) projectVendorRow.style.display = 'none';
        if (vendorSection) vendorSection.style.display = 'none';
        if (projectRequiredStar) projectRequiredStar.style.display = 'none';
        if (vendorFilterHint) vendorFilterHint.style.display = 'none';
        if (projectSelect) projectSelect.removeAttribute('required');

        if (this.value === 'project') {
            if (projectVendorRow) projectVendorRow.style.display = 'flex';
            if (projectRequiredStar) projectRequiredStar.style.display = 'inline';
            if (projectSelect) projectSelect.setAttribute('required', 'required');
        } else if (this.value === 'vendor') {
            if (projectVendorRow) projectVendorRow.style.display = 'flex';
            if (vendorSection) vendorSection.style.display = 'block';
            if (projectRequiredStar) projectRequiredStar.style.display = 'inline';
            if (projectSelect) projectSelect.setAttribute('required', 'required');
            filterVendorsByProject();
        }
    });

    // Filter vendors based on selected project
    document.getElementById('project_id').addEventListener('change', function() {
        var expenseType = document.getElementById('expense_type').value;
        if (expenseType === 'vendor') {
            filterVendorsByProject();
        }
    });

    function filterVendorsByProject() {
        var projectSelect = document.getElementById('project_id');
        var vendorSelect = document.getElementById('vendor_id');
        var vendorFilterHint = document.querySelector('.vendor-filter-hint');
        var selectedOption = projectSelect.options[projectSelect.selectedIndex];

        // Clear current options
        vendorSelect.innerHTML = '<option value="">Select Vendor</option>';

        if (!projectSelect.value || !selectedOption) {
            if (vendorFilterHint) vendorFilterHint.style.display = 'none';
            return;
        }

        // Get assigned vendor IDs from data attribute
        var assignedVendorIds = [];
        try {
            assignedVendorIds = JSON.parse(selectedOption.dataset.vendors || '[]');
        } catch (e) {
            assignedVendorIds = [];
        }

        if (assignedVendorIds && assignedVendorIds.length > 0) {
            // Convert all IDs to strings for comparison
            var vendorIdsAsStrings = assignedVendorIds.map(function(id) { return String(id); });
            // Filter to show only assigned vendors
            allVendorOptions.forEach(function(opt) {
                if (opt.value && vendorIdsAsStrings.includes(String(opt.value))) {
                    var option = document.createElement('option');
                    option.value = opt.value;
                    option.textContent = opt.text;
                    if (opt.value == currentVendorId) option.selected = true;
                    vendorSelect.appendChild(option);
                }
            });
            if (vendorFilterHint) vendorFilterHint.style.display = 'block';
        } else {
            // No vendors assigned to this project - show ALL as fallback
            allVendorOptions.forEach(function(opt) {
                if (opt.value) {
                    var option = document.createElement('option');
                    option.value = opt.value;
                    option.textContent = opt.text;
                    if (opt.value == currentVendorId) option.selected = true;
                    vendorSelect.appendChild(option);
                }
            });
            if (vendorFilterHint) vendorFilterHint.style.display = 'none';
        }
    }

    const paymentModeSelect = document.getElementById('payment_mode_id');
    const bankSection = document.getElementById('bank_section');
    const bankSelect = document.getElementById('bank_id');
    const cashAccountId = document.getElementById('cash_account_id')?.value;

    function toggleBankSection() {
        const selectedOption = paymentModeSelect.options[paymentModeSelect.selectedIndex];
        const slug = selectedOption ? selectedOption.getAttribute('data-slug') : '';

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

    paymentModeSelect.addEventListener('change', toggleBankSection);
    toggleBankSection();

    // Initialize filtering based on current expense type on page load
    var initialExpenseType = document.getElementById('expense_type').value;
    if (initialExpenseType === 'vendor') {
        filterVendorsByProject();
    }

    document.getElementById('exp_sub_total').addEventListener('input', calcExpenseTotal);
});

function calcExpenseTotal() {
    var itemsTotal = 0;
    var typeItems = document.getElementById('typeItems');
    var subTotalInput = document.getElementById('exp_sub_total');
    var grandTotalInput = document.getElementById('exp_grand_total');

    if (typeItems.checked) {
        document.querySelectorAll('#expenseItemsBody .exp-total').forEach(function(input) {
            itemsTotal += parseFloat(input.value) || 0;
        });
        itemsTotal = Math.min(Math.trunc(itemsTotal), 99999999);
        subTotalInput.value = itemsTotal;
    }

    var subTotal = Math.trunc(parseFloat(subTotalInput.value) || 0);
    var grandTotal = Math.min(subTotal, 99999999);
    grandTotalInput.value = grandTotal;

    if (subTotal > 99999999) {
        subTotalInput.classList.add('is-invalid');
    } else {
        subTotalInput.classList.remove('is-invalid');
    }

    if (grandTotal > 99999999) {
        grandTotalInput.classList.add('is-invalid');
    } else {
        grandTotalInput.classList.remove('is-invalid');
    }
}

const expenseMaxLengths = { bill_description: 150 };

document.querySelectorAll('#expenseForm input, #expenseForm textarea').forEach(input => {
    input.addEventListener('input', function() {
        const maxLen = expenseMaxLengths[this.name];
        if (maxLen && this.value.length > maxLen) {
            this.value = this.value.substring(0, maxLen);
        }
    });
});

function validateExpenseForm(form) {
    let isValid = true;
    let errors = [];
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    const paymentModeSelect = document.getElementById('payment_mode_id');
    const selectedPaymentOption = paymentModeSelect.options[paymentModeSelect.selectedIndex];
    const isCash = selectedPaymentOption?.getAttribute('data-slug') === 'cash';

    const requiredSelectIds = isCash ? ['expense_type', 'payment_mode_id'] : ['expense_type', 'payment_mode_id', 'bank_id'];
    requiredSelectIds.forEach(id => {
        const input = document.getElementById(id);
        if (input && !input.disabled && (input.selectedIndex === 0 || !input.value)) {
            input.classList.add('is-invalid');
            errors.push(id + ' is required');
            isValid = false;
        }
    });

    // Check project required for 'project' and 'vendor' expense types
    const expenseType = document.getElementById('expense_type').value;
    const projectSelect = document.getElementById('project_id');
    if ((expenseType === 'project' || expenseType === 'vendor') && projectSelect && !projectSelect.disabled && !projectSelect.value) {
        projectSelect.classList.add('is-invalid');
        errors.push('project_id is required');
        isValid = false;
    }

    // Check vendor required for 'vendor' expense type
    const vendorSelect = document.getElementById('vendor_id');
    if (expenseType === 'vendor' && vendorSelect && !vendorSelect.disabled && !vendorSelect.value) {
        vendorSelect.classList.add('is-invalid');
        errors.push('vendor_id is required for vendor payment');
        isValid = false;
    }

    const dateInput = form.querySelector('input[name="expense_date"]');
    if (dateInput && !dateInput.value) {
        dateInput.classList.add('is-invalid');
        errors.push('expense_date is required');
        isValid = false;
    }


    ['sub_total', 'grand_total'].forEach(name => {
        const input = form.querySelector(`input[name="${name}"]`);
        if (input) {
            const val = input.value.trim();
            const num = parseFloat(val);
            if (!val || isNaN(num) || num < 1 || num > 99999999) {
                input.classList.add('is-invalid');
                errors.push(name + ' must be between 1 and 99999999');
                isValid = false;
            }
        }
    });

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

document.querySelectorAll('#expenseForm input, #expenseForm select, #expenseForm textarea').forEach(el => {
    el.addEventListener('input', function() {
        if (this.name === 'sub_total' || this.name === 'grand_total') {
            const num = parseFloat(this.value) || 0;
            if (num >= 1 && num <= 99999999) this.classList.remove('is-invalid');
        } else if (this.value.trim()) {
            this.classList.remove('is-invalid');
        }
    });
    el.addEventListener('change', function() {
        if (this.name === 'sub_total' || this.name === 'grand_total') {
            const num = parseFloat(this.value) || 0;
            if (num >= 1 && num <= 99999999) this.classList.remove('is-invalid');
        } else if (this.value.trim()) {
            this.classList.remove('is-invalid');
        }
    });
});

['exp_sub_total', 'exp_grand_total'].forEach(id => {
    const input = document.getElementById(id);
    if (input) {
        input.addEventListener('keydown', function(e) {
            if (e.keyCode === 38 || e.keyCode === 40) {
                e.preventDefault();
                return;
            }
            const val = this.value.replace(/[^0-9]/g, '');
            if ([8, 9, 13, 27, 46, 37, 39].includes(e.keyCode)) return;
            if ((e.ctrlKey || e.metaKey) && [65, 67, 86, 88].includes(e.keyCode)) return;
            if (val.length >= 8 && e.keyCode >= 48 && e.keyCode <= 57) {
                e.preventDefault();
            }
            if (val.length >= 8 && e.keyCode >= 96 && e.keyCode <= 105) {
                e.preventDefault();
            }
        });
        input.addEventListener('wheel', function(e) {
            if (document.activeElement === this) {
                e.preventDefault();
            }
        }, { passive: false });
        input.addEventListener('input', function() {
            let val = this.value.replace(/[^0-9]/g, '');
            if (val.length > 8) {
                val = val.substring(0, 8);
                this.value = val;
            }
        });
    }
});

document.addEventListener('keydown', function(e) {
    if ((e.keyCode === 38 || e.keyCode === 40) && e.target.matches('#expenseForm input[type="number"]')) {
        e.preventDefault();
    }
}, true);
document.addEventListener('wheel', function(e) {
    if (e.target.matches('#expenseForm input[type="number"]') && document.activeElement === e.target) {
        e.preventDefault();
    }
}, { passive: false, capture: true });

document.getElementById('expenseForm').addEventListener('submit', function(e) {
    if (!validateExpenseForm(this)) {
        e.preventDefault();
        e.stopPropagation();
    }
});
</script>
@endpush
@push('styles')
<style>
.btn-check:checked + .btn-outline-warning {
    color: #000 !important;
}
.input-group .is-invalid ~ .invalid-feedback,
.input-group .is-invalid ~ .invalid-tooltip {
    display: block;
}
.input-group.has-validation .invalid-feedback {
    width: 100%;
}
</style>
@endpush
