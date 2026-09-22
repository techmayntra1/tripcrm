@extends('layouts.app')
@section('title', 'Convert Lead to Customer')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-person-check-fill icon-gradient bg-happy-itmeo"></i>
            </div>
            <div>
                Convert Lead to Customer
                
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.leads.show', $lead) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>
<div class="main-card mb-3 card">
    <div class="card-header">
        <i class="bi bi-person-plus me-2"></i> Customer Information
    </div>
    <div class="card-body">
        <form action="{{ route('admin.leads.convert.store', $lead) }}" method="POST" class="needs-validation" novalidate>
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $lead->name) }}" required minlength="2" maxlength="30">
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
                        <div class="input-group has-validation">
                            @include('partials._country_code_select', ['selected' => old('country_code', $lead->country_code ?? '+91')])
                            <input type="tel" class="form-control @error('mobile') is-invalid @enderror" id="mobile" name="mobile" value="{{ old('mobile', $lead->mobile) }}" placeholder="Mobile number" required minlength="7" maxlength="15" inputmode="numeric" pattern="[0-9]{7,15}">
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
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $lead->email) }}" maxlength="100">
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
                                <option value="{{ $source->name }}" {{ old('work_lead', $lead->work_lead) == $source->name ? 'selected' : '' }}>{{ $source->name }}</option>
                            @endforeach
                        </select>
                        @error('work_lead')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name') }}" placeholder="Customer's company" maxlength="100">
                        @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="company_trn" class="form-label">Company TRN</label>
                        <input type="text" class="form-control @error('company_trn') is-invalid @enderror" id="company_trn" name="company_trn" value="{{ old('company_trn') }}" placeholder="e.g., 100123456700003" maxlength="30">
                        @error('company_trn')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="gst_number" class="form-label">GST Number</label>
                        <input type="text" class="form-control @error('gst_number') is-invalid @enderror" id="gst_number" name="gst_number" value="{{ old('gst_number') }}" placeholder="e.g., 24AABCT1234D1ZH" maxlength="15" style="text-transform: uppercase;">
                        @error('gst_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">GST number must not exceed 15 characters</div>
                        @enderror
                    </div>
                </div>
            </div>
            @php
                $selectedCountry = old('country', 'India');
                $countries = \App\Support\Countries::names();
                if ($selectedCountry && !in_array($selectedCountry, $countries)) { $countries[] = $selectedCountry; }
            @endphp
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="country" class="form-label">Country</label>
                        <select class="form-select @error('country') is-invalid @enderror" id="country" name="country">
                            <option value="">Select Country</option>
                            @foreach($countries as $country)
                                <option value="{{ $country }}" {{ $selectedCountry == $country ? 'selected' : '' }}>{{ $country }}</option>
                            @endforeach
                        </select>
                        @error('country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="city" class="form-label">City</label>
                        <select class="form-select @error('city') is-invalid @enderror" id="city" name="city" data-selected="{{ old('city', $lead->city ?? '') }}">
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
                <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2" maxlength="150">{{ old('address', $lead->address) }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" maxlength="150">{{ old('notes', $lead->notes) }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.leads.show', $lead) }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-person-check me-1"></i> Convert to Customer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
$(document).ready(function() {
    // City list depends on the country: Indian cities, UAE cities, or free text for anywhere else.
    var uaeCities = @json(\App\Models\Customer::UAE_CITIES).map(function(n) { return { id: n, text: n }; });
    var dialCodeByCountry = @json(\App\Support\Countries::ALL);

    var $city = $('#city');
    var $country = $('#country');
    var selectedCity = $city.data('selected') || '';

    function cityDataFor(country) {
        if (country === 'India') return (typeof indianCities !== 'undefined') ? indianCities : [];
        if (country === 'United Arab Emirates') return uaeCities;
        return [];
    }

    function initCity(keepValue) {
        var value = keepValue ? ($city.val() || selectedCity) : '';
        if ($city.hasClass('select2-hidden-accessible')) $city.select2('destroy');
        $city.empty().append(new Option('Select City', ''));
        $city.select2({
            placeholder: 'Type to search city...',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5',
            data: cityDataFor($country.val()),
            tags: true,
            createTag: function(params) { return { id: params.term, text: params.term }; }
        });
        if (value) {
            if (!$city.find('option[value="' + value.replace(/"/g, '\\"') + '"]').length) {
                $city.append(new Option(value, value, true, true));
            }
            $city.val(value).trigger('change');
        }
    }

    $('#country').select2({ width: '100%', theme: 'bootstrap-5', placeholder: 'Select Country' });

    initCity(true);

    $country.on('change', function() {
        initCity(false);
        var code = dialCodeByCountry[this.value];
        if (code) $('#country_code').val(code).trigger('change');
    });

    $('#mobile').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15);
    });

    $('#gst_number').on('input', function() {
        this.value = this.value.toUpperCase().slice(0, 15);
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
