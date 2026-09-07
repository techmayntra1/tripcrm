@extends('layouts.app')
@section('title', 'Edit Company - ' . $company->name)
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-pencil-square icon-gradient bg-amy-crisp"></i>
            </div>
            <div>
                Edit Company
                <div class="page-title-subheading">{{ $company->name }}</div>
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Details
            </a>
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

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('admin.companies.update', $company) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-md-8">
            
            <div class="main-card mb-3 card">
                <div class="card-header">
                    <i class="bi bi-building me-2"></i> Basic Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Company Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="{{ old('name', $company->name) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Contact Person</label>
                                <input type="text" class="form-control" name="contact_person" value="{{ old('contact_person', $company->contact_person) }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="{{ old('email', $company->email) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Mobile</label>
                                <input type="tel" class="form-control" name="phone" value="{{ old('phone', $company->phone) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="main-card mb-3 card">
                <div class="card-header">
                    <i class="bi bi-receipt me-2"></i> Tax Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">GST Number</label>
                                <input type="text" class="form-control" name="gst_number" value="{{ old('gst_number', $company->gst_number) }}" maxlength="15">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">PAN Number</label>
                                <input type="text" class="form-control" name="pan_number" value="{{ old('pan_number', $company->pan_number) }}" maxlength="10">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="main-card mb-3 card">
                <div class="card-header">
                    <i class="bi bi-geo-alt me-2"></i> Address Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="2">{{ old('address', $company->address) }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="city" value="{{ old('city', $company->city) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">State</label>
                                <select class="form-select" name="state">
                                    <option value="">Select State</option>
                                    @php $currentState = old('state', $company->state); @endphp
                                    <option value="Andhra Pradesh" {{ $currentState == 'Andhra Pradesh' ? 'selected' : '' }}>Andhra Pradesh</option>
                                    <option value="Delhi" {{ $currentState == 'Delhi' ? 'selected' : '' }}>Delhi</option>
                                    <option value="Gujarat" {{ $currentState == 'Gujarat' ? 'selected' : '' }}>Gujarat</option>
                                    <option value="Karnataka" {{ $currentState == 'Karnataka' ? 'selected' : '' }}>Karnataka</option>
                                    <option value="Maharashtra" {{ $currentState == 'Maharashtra' ? 'selected' : '' }}>Maharashtra</option>
                                    <option value="Rajasthan" {{ $currentState == 'Rajasthan' ? 'selected' : '' }}>Rajasthan</option>
                                    <option value="Tamil Nadu" {{ $currentState == 'Tamil Nadu' ? 'selected' : '' }}>Tamil Nadu</option>
                                    <option value="Uttar Pradesh" {{ $currentState == 'Uttar Pradesh' ? 'selected' : '' }}>Uttar Pradesh</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Pincode</label>
                                <input type="text" class="form-control" name="pincode" value="{{ old('pincode', $company->pincode) }}" maxlength="6">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">

            
            <div class="main-card mb-3 card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-bank me-2"></i> Bank Accounts</span>
                    <a href="{{ route('admin.banks.index') }}" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="bi bi-plus-lg me-1"></i> Manage Banks
                    </a>
                </div>
                <div class="card-body">
                    @if($availableBanks->count() > 0)
                    <div class="alert alert-info py-2 mb-3">
                        <small><i class="bi bi-info-circle me-1"></i> Select bank accounts to assign to this company</small>
                    </div>
                    @php
                        $companyBankIds = $company->banks->pluck('id')->toArray();
                    @endphp
                    <div class="mb-3">
                        <label class="form-label">Select Bank Accounts</label>
                        @foreach($availableBanks as $bank)
                        <div class="form-check mb-2">
                            <input class="form-check-input bank-checkbox" type="checkbox" name="bank_ids[]" value="{{ $bank->id }}" id="bank{{ $bank->id }}" {{ in_array($bank->id, old('bank_ids', $companyBankIds)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="bank{{ $bank->id }}">
                                <strong>{{ $bank->bank_name }}</strong>
                                @if($bank->company_id == $company->id)
                                <span class="badge bg-success ms-1">Assigned</span>
                                @endif
                                <br><small class="text-muted">A/C: {{ $bank->account_number }} | {{ ucfirst($bank->account_type) }}</small>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="alert alert-warning py-2">
                        <i class="bi bi-exclamation-triangle me-1"></i> No bank accounts available.
                        <a href="{{ route('admin.banks.index') }}" target="_blank">Add new bank accounts</a> first.
                    </div>
                    @endif
                </div>
            </div>

            
            <div class="main-card mb-3 card">
                <div class="card-header">
                    <i class="bi bi-123 me-2"></i> Document Number Series
                </div>
                <div class="card-body">
                    <div class="alert alert-info py-2 mb-3">
                        <small><i class="bi bi-info-circle me-1"></i> These prefixes will be used for auto-generating quotation and invoice numbers (e.g., QT-001, INV-001)</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Quotation Number Series <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="quotation_number_series" value="{{ old('quotation_number_series', $company->quotation_number_series) }}" maxlength="20" placeholder="e.g., QT, EST, QUOTE" required>
                                <small class="text-muted">Example: QT will generate QT-001, QT-002, etc.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Invoice Number Series <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="invoice_number_series" value="{{ old('invoice_number_series', $company->invoice_number_series) }}" maxlength="20" placeholder="e.g., INV, BILL" required>
                                <small class="text-muted">Example: INV will generate INV-001, INV-002, etc.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row">
        <div class="col-12">
            <div class="main-card card">
                <div class="card-body d-flex justify-content-between">
                    <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection
