@extends('layouts.app')
@section('title', 'Edit Customer')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-pencil-square icon-gradient bg-happy-itmeo"></i>
            </div>
            <div>
                Edit Customer: {{ $customer->name }}
                
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>
<div class="main-card mb-3 card">
    <div class="card-header">
        <i class="bi bi-pencil me-2"></i> Customer Information
    </div>
    <div class="card-body">
        <form action="{{ route('admin.customers.update', $customer) }}" method="POST" class="needs-validation" novalidate>
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $customer->name) }}" required minlength="2" maxlength="30">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Please enter a valid name (at least 2 characters)</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="mobile" class="form-label">Mobile <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control @error('mobile') is-invalid @enderror" id="mobile" name="mobile" value="{{ old('mobile', $customer->mobile) }}" placeholder="Enter 10-digit mobile number" required minlength="10" maxlength="10" inputmode="numeric">
                        @error('mobile')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Please enter a 10-digit mobile number</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $customer->email) }}" maxlength="100">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="work_type" class="form-label">Work Type</label>
                        <select class="form-select select2-multiple @error('work_type') is-invalid @enderror" id="work_type" name="work_type[]" multiple>
                            @foreach($workTypes as $type)
                                <option value="{{ $type->name }}" {{ in_array($type->name, old('work_type', $customer->work_type ?? [])) ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                        @error('work_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="work_lead" class="form-label">Lead Source</label>
                        <select class="form-select @error('work_lead') is-invalid @enderror" id="work_lead" name="work_lead">
                            <option value="">Select Lead Source</option>
                            @foreach($workLeads as $source)
                                <option value="{{ $source->name }}" {{ old('work_lead', $customer->work_lead) == $source->name ? 'selected' : '' }}>{{ $source->name }}</option>
                            @endforeach
                        </select>
                        @error('work_lead')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="budget" class="form-label">Budget</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control @error('budget') is-invalid @enderror" id="budget" name="budget" value="{{ old('budget', $customer->budget) }}" min="0">
                            @error('budget')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="payment_type" class="form-label">Payment Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('payment_type') is-invalid @enderror" id="payment_type" name="payment_type" required>
                            <option value="">Select Payment Type</option>
                            @foreach(['Cash', 'Cheque', 'Bank Transfer', 'UPI', 'Credit'] as $type)
                                <option value="{{ $type }}" {{ old('payment_type', $customer->payment_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('payment_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="gst_number" class="form-label">GST Number</label>
                        <input type="text" class="form-control @error('gst_number') is-invalid @enderror" id="gst_number" name="gst_number" value="{{ old('gst_number', $customer->gst_number) }}" placeholder="e.g., 24AABCT1234D1ZH" maxlength="15" style="text-transform: uppercase;">
                        @error('gst_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">GST number must not exceed 15 characters</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="city" class="form-label">City</label>
                        <select class="form-select select2-city @error('city') is-invalid @enderror" id="city" name="city">
                            <option value="">Select City</option>
                        </select>
                        @error('city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label for="project_ids" class="form-label">Projects</label>
                        <select class="form-select select2-multiple @error('project_ids') is-invalid @enderror" id="project_ids" name="project_ids[]" multiple>
                            @php
                                $customerProjectIds = $customer->projects->pluck('id')->toArray();
                            @endphp
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ in_array($project->id, old('project_ids', $customerProjectIds)) ? 'selected' : '' }}>{{ $project->project_number }} - {{ $project->name }}</option>
                            @endforeach
                        </select>
                        @error('project_ids')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Link projects to this customer</small>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2" maxlength="150">{{ old('address', $customer->address) }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" maxlength="150">{{ old('notes', $customer->notes) }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Update Customer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
$(document).ready(function() {
    $('#work_type').select2({
        placeholder: 'Select Work Types',
        allowClear: true,
        width: '100%',
        theme: 'bootstrap-5'
    });
    $('#project_ids').select2({
        placeholder: 'Select Projects',
        allowClear: true,
        width: '100%',
        theme: 'bootstrap-5'
    });
    $('#city').select2({
        placeholder: 'Type to search city...',
        allowClear: true,
        width: '100%',
        theme: 'bootstrap-5',
        data: indianCities,
        tags: true,
        createTag: function(params) {
            return { id: params.term, text: params.term };
        }
    });
    var currentCity = '{{ old('city', $customer->city ?? $customer->city_other ?? '') }}';
    if (currentCity) {
        var exists = indianCities.find(function(c) { return c.id === currentCity || c.text === currentCity; });
        if (!exists) {
            var newOption = new Option(currentCity, currentCity, true, true);
            $('#city').append(newOption);
        }
        $('#city').val(currentCity).trigger('change');
    }

    $('#mobile').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
    });

    $('#gst_number').on('input', function() {
        this.value = this.value.toUpperCase().slice(0, 15);
    });

    $('#budget').on('input', function() {
        var val = parseFloat($(this).val());
        if (val < 0) $(this).val(0);
    });

    $('.needs-validation').on('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });
});
</script>
@endpush
