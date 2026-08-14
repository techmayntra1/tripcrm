@extends('layouts.app')
@section('title', 'Create Trip')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-kanban icon-gradient bg-strong-bliss"></i>
            </div>
            <div>Create Trip</div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.trips.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('admin.trips.store') }}" method="POST" class="needs-validation" enctype="multipart/form-data" novalidate id="tripForm">
    @csrf
    <div class="main-card mb-3 card">
        <div class="card-header">
            <i class="bi bi-info-circle me-2"></i> Trip Details
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Trip Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g., 3BHK Interior Work" required maxlength="40">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@else<div class="invalid-feedback">Please enter trip name</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="planning" {{ old('status') == 'planning' ? 'selected' : '' }}>Planning</option>
                            <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="on_hold" {{ old('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
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
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }} - {{ $customer->mobile }}</option>
                            @endforeach
                        </select>
                        @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@else<div class="invalid-feedback">Please select a client</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="work_type" class="form-label">Work Type</label>
                        <select class="form-select select2-multiple @error('work_type') is-invalid @enderror" id="work_type" name="work_type[]" multiple>
                            @foreach($workTypes as $workType)
                                <option value="{{ $workType->name }}" {{ in_array($workType->name, old('work_type', [])) ? 'selected' : '' }}>{{ $workType->name }}</option>
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
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="expected_end_date" class="form-label">Expected End Date</label>
                        <input type="date" class="form-control" id="expected_end_date" name="expected_end_date" value="{{ old('expected_end_date') }}">
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="site_address" class="form-label">Site Address</label>
                <textarea class="form-control" id="site_address" name="site_address" rows="2" placeholder="Enter site/trip address" maxlength="150">{{ old('site_address') }}</textarea>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Trip Description</label>
                <textarea class="form-control" id="description" name="description" rows="2" placeholder="Describe the trip scope and details..." maxlength="2000">{{ old('description') }}</textarea>
            </div>
            <div class="mb-3">
                <label for="trip_files" class="form-label">Trip Files</label>
                <input type="file" class="form-control" id="trip_files" name="trip_files[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.dwg,.dxf">
                <small class="text-muted">PDF, DOC, XLS, Images. Max 2MB each.</small>
            </div>
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
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control @error('budget') is-invalid @enderror" id="budget" name="budget" value="{{ old('budget', 0) }}" placeholder="0" min="0" max="999999999" step="1" required inputmode="numeric">
                                    @error('budget')<div class="invalid-feedback">{{ $message }}</div>@else<div class="invalid-feedback">Please enter trip budget</div>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="advance_received" class="form-label">Advance Received</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="advance_received" name="advance_received" value="{{ old('advance_received', 0) }}" placeholder="0" min="0" max="999999999" step="1" inputmode="numeric">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label for="quotation_id" class="form-label"><i class="bi bi-file-earmark-text me-1"></i> Linked Quotation</label>
                        <select class="form-select" id="quotation_id" name="quotation_id">
                            <option value="">Select Quotation (Optional)</option>
                            @foreach($quotations as $quotation)
                                <option value="{{ $quotation->id }}" {{ old('quotation_id') == $quotation->id ? 'selected' : '' }}>{{ $quotation->quotation_number }} - {{ $quotation->customer->name ?? 'N/A' }}</option>
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
                                <select class="form-select @error('company_id') is-invalid @enderror" id="company_id" name="company_id">
                                    <option value="">Select Company (Optional)</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" data-has-gst="{{ !empty($company->gst_number) ? '1' : '0' }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                    @endforeach
                                </select>
                                @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="assigned_staff_ids" class="form-label">Assign Staff</label>
                                <select class="form-select select2-multiple" id="assigned_staff_ids" name="assigned_staff_ids[]" multiple>
                                    @foreach($staff as $member)
                                        <option value="{{ $member->id }}" {{ in_array($member->id, old('assigned_staff_ids', [])) ? 'selected' : '' }}>{{ $member->name }} - {{ $member->role_display ?? 'Staff' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="assigned_vendor_ids" class="form-label">Select Vendors</label>
                        <select class="form-select select2-multiple" id="assigned_vendor_ids" name="assigned_vendor_ids[]" multiple>
                            @foreach($vendors ?? [] as $vendor)
                                <option value="{{ $vendor->id }}" {{ in_array($vendor->id, old('assigned_vendor_ids', [])) ? 'selected' : '' }}>{{ $vendor->name }} - {{ $vendor->category->name ?? 'Vendor' }}</option>
                            @endforeach
                        </select>
                    </div>
                    @include('admin.trips._gst_section', ['gstRates' => $gstRates, 'trip' => null])
                </div>
            </div>
        </div>
    </div>

    @include('admin.trips._services_section', ['existingServices' => collect(), 'services' => $services, 'banks' => $banks])

    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('admin.trips.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> Create Trip
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
});
</script>
@endpush
