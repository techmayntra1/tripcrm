@extends('layouts.app')
@section('title', 'Add Company')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-building-add icon-gradient bg-amy-crisp"></i>
            </div>
            <div>
                Add New Company
                <div class="page-title-subheading">Create a new company profile</div>
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Companies
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

<form action="{{ route('admin.companies.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

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
                                <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Enter company name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Contact Person</label>
                                <input type="text" class="form-control" name="contact_person" value="{{ old('contact_person') }}" placeholder="Owner/Contact person name">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="company@example.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Mobile</label>
                                <input type="tel" class="form-control" name="phone" value="{{ old('phone') }}" placeholder="+91 98765 43210">
                            </div>
                        </div>
                    </div>
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
                                <input type="text" class="form-control" name="quotation_number_series" value="{{ old('quotation_number_series') }}" maxlength="20" placeholder="e.g., QT, EST, QUOTE" required>
                                <small class="text-muted">Example: QT will generate QT-001, QT-002, etc.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Invoice Number Series <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="invoice_number_series" value="{{ old('invoice_number_series') }}" maxlength="20" placeholder="e.g., INV, BILL" required>
                                <small class="text-muted">Example: INV will generate INV-001, INV-002, etc.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            @include('admin.companies._region_tax')

            
            @include('admin.companies._address')
        </div>

        <div class="col-md-4">
            @include('admin.companies._logo')

            <div class="main-card mb-3 card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-bank me-2"></i> Bank Accounts</span>
                    <a href="{{ route('admin.banks.index') }}" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="bi bi-plus-lg me-1"></i> Manage Banks
                    </a>
                </div>
                <div class="card-body">
                    @if($banks->count() > 0)
                    <div class="alert alert-info py-2 mb-3">
                        <small><i class="bi bi-info-circle me-1"></i> Select bank accounts to assign to this company</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Bank Accounts</label>
                        @foreach($banks as $bank)
                        <div class="form-check mb-2 bank-option" data-country="{{ $bank->country }}">
                            <input class="form-check-input bank-checkbox" type="checkbox" name="bank_ids[]" value="{{ $bank->id }}" id="bank{{ $bank->id }}" {{ in_array($bank->id, old('bank_ids', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="bank{{ $bank->id }}">
                                <strong>{{ $bank->bank_name }}</strong>
                                <span class="badge bg-light text-dark ms-1">{{ $bank->country_label }}</span>
                                <br><small class="text-muted">A/C: {{ $bank->account_number }} | {{ ucfirst($bank->account_type) }}</small>
                            </label>
                        </div>
                        @endforeach
                        <div class="alert alert-warning py-2 mb-0" id="bank_region_empty" style="display: none;">
                            <small><i class="bi bi-exclamation-triangle me-1"></i> No unassigned bank accounts for the selected region.</small>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-warning py-2">
                        <i class="bi bi-exclamation-triangle me-1"></i> No unassigned bank accounts available.
                        <a href="{{ route('admin.banks.index') }}" target="_blank">Add new bank accounts</a> first.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    
    <div class="row">
        <div class="col-12">
            <div class="main-card card">
                <div class="card-body d-flex justify-content-between">
                    <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Create Company
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection
