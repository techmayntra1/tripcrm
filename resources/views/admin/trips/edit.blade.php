@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.app')
@section('title', 'Edit Trip')
@section('currency_symbol', currencySymbol($trip))
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-pencil-square icon-gradient bg-strong-bliss"></i>
            </div>
            <div>Edit Trip - {{ $trip->trip_number }}</div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.trips.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($trip->hasPendingPayments())
<div class="main-card mb-3 card border-warning" id="pending-payments-card">
    <div class="card-header bg-warning text-dark py-2">
        <i class="bi bi-exclamation-triangle me-2"></i> Pending Payments
    </div>
    <div class="card-body py-2 d-flex flex-wrap align-items-center gap-4">
        @if($trip->pending_to_receive > 0)
        <span class="text-danger"><i class="bi bi-arrow-down-circle me-1"></i> To Receive: <strong>{{ formatMoney($trip->pending_to_receive, 2, $trip) }}</strong></span>
        @endif
        @if($trip->pending_to_give > 0)
        <span class="text-primary"><i class="bi bi-arrow-up-circle me-1"></i> To Pay (Vendors): <strong>{{ formatMoney($trip->pending_to_give, 2, $trip) }}</strong></span>
        @endif
        @if($trip->service_pending > 0)
        <span class="text-warning-emphasis"><i class="bi bi-tools me-1"></i> To Pay (Services): <strong>{{ formatMoney($trip->service_pending, 2, $trip) }}</strong></span>
        @endif
        <small class="text-muted ms-auto"><i class="bi bi-info-circle me-1"></i> Clear all pending payments before marking trip as completed.</small>
    </div>
</div>
@endif

<form action="{{ route('admin.trips.update', $trip) }}" method="POST" class="needs-validation" enctype="multipart/form-data" novalidate id="tripForm">
    @csrf
    @method('PUT')
    <div class="main-card mb-3 card">
        <div class="card-header">
            <i class="bi bi-info-circle me-2"></i> Trip Details
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Trip Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $trip->name) }}" required minlength="2" maxlength="40">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@else<div class="invalid-feedback">Please enter trip name</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="planning" {{ old('status', $trip->status) == 'planning' ? 'selected' : '' }}>Planning</option>
                            <option value="in_progress" {{ old('status', $trip->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="on_hold" {{ old('status', $trip->status) == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="completed" {{ old('status', $trip->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $trip->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Client <span class="text-danger">*</span></label>
                        <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                            <option value="">Select Client</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $trip->customer_id) == $customer->id ? 'selected' : '' }}>{{ $customer->name }} - {{ $customer->mobile }}</option>
                            @endforeach
                        </select>
                        @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@else<div class="invalid-feedback">Please select a client</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="work_type" class="form-label">Work Type</label>
                        @php
                            $selectedWorkTypes = old('work_type', $trip->work_type ?? []);
                            if (!is_array($selectedWorkTypes)) $selectedWorkTypes = [];
                        @endphp
                        <select class="form-select select2-multiple @error('work_type') is-invalid @enderror" id="work_type" name="work_type[]" multiple>
                            @foreach($workTypes as $workType)
                                <option value="{{ $workType->name }}" {{ in_array($workType->name, $selectedWorkTypes) ? 'selected' : '' }}>{{ $workType->name }}</option>
                            @endforeach
                        </select>
                        @error('work_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date', $trip->start_date?->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="expected_end_date" class="form-label">Expected End Date</label>
                        <input type="date" class="form-control" id="expected_end_date" name="expected_end_date" value="{{ old('expected_end_date', $trip->expected_end_date?->format('Y-m-d')) }}">
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="site_address" class="form-label">Site Address</label>
                <textarea class="form-control" id="site_address" name="site_address" rows="2" maxlength="150">{{ old('site_address', $trip->site_address) }}</textarea>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Trip Description</label>
                <textarea class="form-control" id="description" name="description" rows="2" maxlength="2000">{{ old('description', $trip->description) }}</textarea>
            </div>
            <div class="mb-3">
                <label for="trip_files" class="form-label">Add More Files</label>
                <input type="file" class="form-control" id="trip_files" name="trip_files[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.dwg,.dxf">
                <small class="text-muted">PDF, DOC, XLS, Images. Max 2MB each.</small>
            </div>
            @if($trip->files->count() > 0)
            <div class="mb-0">
                <label class="form-label">Existing Files</label>
                <div class="list-group">
                    @foreach($trip->files as $file)
                    <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <div>
                            <i class="bi bi-file-earmark me-2"></i>
                            <a href="{{ Storage::url($file->file_path) }}" target="_blank">{{ $file->original_name }}</a>
                            <small class="text-muted ms-2">({{ $file->file_size_formatted }})</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger delete-file-btn" data-file-id="{{ $file->id }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="main-card mb-3 card">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-currency-rupee me-2"></i> Budget & Financials
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="budget" class="form-label">Total Budget <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text js-currency-symbol">₹</span>
                                    <input type="number" class="form-control @error('budget') is-invalid @enderror" id="budget" name="budget" value="{{ old('budget', (int)$trip->budget) }}" min="0" max="999999999" step="1" required inputmode="numeric">
                                    @error('budget')<div class="invalid-feedback">{{ $message }}</div>@else<div class="invalid-feedback">Please enter trip budget</div>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="advance_received" class="form-label">Advance Received</label>
                                <div class="input-group">
                                    <span class="input-group-text js-currency-symbol">₹</span>
                                    <input type="number" class="form-control" id="advance_received" name="advance_received" value="{{ old('advance_received', (int)$trip->advance_received) }}" min="0" max="999999999" step="1" inputmode="numeric">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label for="quotation_id" class="form-label"><i class="bi bi-file-earmark-text me-1"></i> Linked Quotation</label>
                        <select class="form-select" id="quotation_id" name="quotation_id">
                            <option value="">Select Quotation (Optional)</option>
                            @foreach($quotations as $quotation)
                                <option value="{{ $quotation->id }}" {{ old('quotation_id', $trip->quotation_id) == $quotation->id ? 'selected' : '' }}>{{ $quotation->quotation_number }} - {{ $quotation->customer->name ?? 'N/A' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="main-card mb-3 card">
                <div class="card-header">
                    <i class="bi bi-person-badge me-2"></i> Assignment
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="company_id" class="form-label">Assign Company</label>
                                <select class="form-select js-currency-source @error('company_id') is-invalid @enderror" id="company_id" name="company_id" data-currency-default="₹">
                                    <option value="">Select Company (Optional)</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" data-currency="{{ $company->currency_symbol }}" data-has-gst="{{ !empty($company->gst_number) ? '1' : '0' }}" {{ old('company_id', $trip->company_id) == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                    @endforeach
                                </select>
                                @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="assigned_staff_ids" class="form-label">Assign Staff</label>
                                @php
                                    $selectedStaffIds = old('assigned_staff_ids', $trip->assigned_staff_ids ?? []);
                                    if (!is_array($selectedStaffIds)) $selectedStaffIds = [];
                                @endphp
                                <select class="form-select select2-multiple" id="assigned_staff_ids" name="assigned_staff_ids[]" multiple>
                                    @foreach($staff as $member)
                                        <option value="{{ $member->id }}" {{ in_array($member->id, $selectedStaffIds) ? 'selected' : '' }}>{{ $member->name }} - {{ $member->role_display ?? 'Staff' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="assigned_vendor_ids" class="form-label">Select Vendors</label>
                        @php
                            $selectedVendorIds = old('assigned_vendor_ids', $trip->assigned_vendor_ids ?? []);
                            if (!is_array($selectedVendorIds)) $selectedVendorIds = [];
                        @endphp
                        <select class="form-select select2-multiple" id="assigned_vendor_ids" name="assigned_vendor_ids[]" multiple>
                            @foreach($vendors ?? [] as $vendor)
                                <option value="{{ $vendor->id }}" {{ in_array($vendor->id, $selectedVendorIds) ? 'selected' : '' }}>{{ $vendor->name }} - {{ $vendor->category->name ?? 'Vendor' }}</option>
                            @endforeach
                        </select>
                    </div>
                    @include('admin.trips._gst_section', ['gstRates' => $gstRates, 'trip' => $trip])
                </div>
            </div>
        </div>
    </div>

    @include('admin.trips._services_section', ['existingServices' => $trip->tripServices, 'services' => $services, 'banks' => $banks])

    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('admin.trips.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> Update Trip
        </button>
    </div>
</form>
@endsection
@push('scripts')
<script>
$(document).ready(function() {
    $('#work_type, #assigned_staff_ids, #assigned_vendor_ids').each(function() {
        $(this).select2({ allowClear: true, width: '100%', theme: 'bootstrap-5' });
    });

    $('#tripForm').on('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });

    $('input, select, textarea').on('input change', function() {
        var val = $(this).val();
        var hasValue = Array.isArray(val) ? val.length > 0 : (val && String(val).trim());
        if (hasValue) $(this).removeClass('is-invalid');
    });

    @if($trip->hasPendingPayments())
    $('#status').on('change', function() {
        if ($(this).val() === 'completed') {
            window.koAlert.warning('This trip has pending payments. Please clear them before marking as completed.');
        }
    });
    @endif

    $('.delete-file-btn').on('click', function() {
        var fileId = $(this).data('file-id');
        var listItem = $(this).closest('.list-group-item');
        window.koAlert.confirmDelete('Are you sure you want to delete this file?').then(function(ok) {
            if (!ok) return;
            $.ajax({
                url: '{{ url("admin/trip-files") }}/' + fileId,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function() {
                    listItem.fadeOut(300, function() { $(this).remove(); });
                    window.koAlert.successToast('File deleted');
                },
                error: function() { window.koAlert.errorToast('Failed to delete file.'); }
            });
        });
    });
});
</script>
@endpush
