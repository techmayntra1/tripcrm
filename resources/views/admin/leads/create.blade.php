@extends('layouts.app')
@section('title', 'Add New Lead')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-person-plus-fill icon-gradient bg-mean-fruit"></i>
            </div>
            <div>
                Add New Lead
                
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.leads.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>
<div class="main-card mb-3 card">
    <div class="card-header">
        <i class="bi bi-person-plus me-2"></i> Lead Information
    </div>
    <div class="card-body">
        <form action="{{ route('admin.leads.store') }}" method="POST" class="needs-validation" novalidate>
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Enter full name" required minlength="2" maxlength="30">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Please enter a valid name</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="mobile" class="form-label">Mobile <span class="text-danger">*</span></label>
                        <div class="input-group has-validation">
                            @include('partials._country_code_select', ['selected' => old('country_code', '+91')])
                            <input type="tel" class="form-control @error('mobile') is-invalid @enderror" id="mobile" name="mobile" value="{{ old('mobile') }}" placeholder="Mobile number" required minlength="7" maxlength="15" inputmode="numeric" pattern="[0-9]{7,15}">
                        @error('mobile')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Please enter a valid mobile number (7-15 digits)</div>
                        @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Enter email address" maxlength="100">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="work_lead" class="form-label">Lead Source</label>
                        <select class="form-select @error('work_lead') is-invalid @enderror" id="work_lead" name="work_lead">
                            <option value="">Select Lead Source</option>
                            @foreach($workLeads as $source)
                                <option value="{{ $source->name }}" {{ old('work_lead') == $source->name ? 'selected' : '' }}>{{ $source->name }}</option>
                            @endforeach
                        </select>
                        @error('work_lead')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
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
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2" placeholder="Enter full address" maxlength="150">{{ old('address') }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" maxlength="150">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.leads.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save Lead
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
$(document).ready(function() {
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
    @if(old('city'))
    var oldCity = '{{ old('city') }}';
    if (oldCity) {
        var exists = indianCities.find(function(c) { return c.id === oldCity || c.text === oldCity; });
        if (!exists) {
            var newOption = new Option(oldCity, oldCity, true, true);
            $('#city').append(newOption);
        }
        $('#city').val(oldCity).trigger('change');
    }
    @endif
    $('#mobile').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15);
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
