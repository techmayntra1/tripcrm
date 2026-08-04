@extends('layouts.app')
@section('title', 'Edit Vendor')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-pencil-square icon-gradient bg-arielle-smile"></i>
            </div>
            <div>
                Edit Vendor
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>
<div class="main-card mb-3 card">
    <div class="card-header">
        <i class="bi bi-shop me-2"></i> Vendor Information
    </div>
    <div class="card-body">
        <form action="{{ route('admin.vendors.update', $vendor) }}" method="POST" class="needs-validation" novalidate>
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Vendor Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $vendor->name) }}" required minlength="2" maxlength="30">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Please enter a valid vendor name</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="categories" class="form-label">Categories <span class="text-danger">*</span></label>
                        <select class="form-select select2-multiple @error('categories') is-invalid @enderror" id="categories" name="categories[]" multiple required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ in_array($category->id, old('categories', $vendor->categories ?? [])) ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('categories')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Please select at least one category</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="contact_person" class="form-label">Contact Person</label>
                        <input type="text" class="form-control" id="contact_person" name="contact_person" value="{{ old('contact_person', $vendor->contact_person) }}" maxlength="30">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="mobile" class="form-label">Mobile <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control @error('mobile') is-invalid @enderror" id="mobile" name="mobile" value="{{ old('mobile', $vendor->mobile) }}" placeholder="Enter 10-digit mobile number" required minlength="10" maxlength="10" inputmode="numeric">
                        @error('mobile')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Please enter a 10-digit mobile number</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $vendor->email) }}" maxlength="100">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Please enter a valid email address</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="gst_number" class="form-label">GST Number</label>
                        <input type="text" class="form-control @error('gst_number') is-invalid @enderror" id="gst_number" name="gst_number" value="{{ old('gst_number', $vendor->gst_number) }}" placeholder="e.g., 24AABCT1234D1ZH" maxlength="15" style="text-transform: uppercase;">
                        @error('gst_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">GST number must not exceed 15 characters</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="pan_number" class="form-label">PAN Number</label>
                        <input type="text" class="form-control @error('pan_number') is-invalid @enderror" id="pan_number" name="pan_number" value="{{ old('pan_number', $vendor->pan_number) }}" placeholder="e.g., ABCDE1234F" maxlength="10" style="text-transform: uppercase;">
                        @error('pan_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">PAN number must not exceed 10 characters</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="opening_balance" class="form-label">Opening Balance</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" id="opening_balance" name="opening_balance" value="{{ old('opening_balance', $vendor->opening_balance) }}" min="0" step="1" readonly>
                        </div>
                        <small class="text-muted">Opening balance cannot be changed after creation</small>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3" maxlength="150">{{ old('address', $vendor->address) }}</textarea>
            </div>
            <hr>
            <h6 class="mb-3"><i class="bi bi-bank me-1"></i> Bank Details</h6>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="bank_name" class="form-label">Bank Name</label>
                        <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{ old('bank_name', $vendor->bank_name) }}" maxlength="100">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="account_number" class="form-label">Account Number</label>
                        <input type="text" class="form-control" id="account_number" name="account_number" value="{{ old('account_number', $vendor->account_number) }}" maxlength="20">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="ifsc_code" class="form-label">IFSC Code</label>
                        <input type="text" class="form-control @error('ifsc_code') is-invalid @enderror" id="ifsc_code" name="ifsc_code" value="{{ old('ifsc_code', $vendor->ifsc_code) }}" placeholder="e.g., SBIN0001234" maxlength="11" style="text-transform: uppercase;">
                        @error('ifsc_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">IFSC code must not exceed 11 characters</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes" maxlength="150">{{ old('notes', $vendor->notes) }}</textarea>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Update Vendor
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
$(document).ready(function() {
    $('#categories').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select categories',
        allowClear: true
    });

    $('#mobile').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
    });

    $('#gst_number').on('input', function() {
        this.value = this.value.toUpperCase().slice(0, 15);
    });

    $('#pan_number').on('input', function() {
        this.value = this.value.toUpperCase().slice(0, 10);
    });

    $('#ifsc_code').on('input', function() {
        this.value = this.value.toUpperCase().slice(0, 11);
    });

    $('.needs-validation').on('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });
    
    $('input, select, textarea').on('input change', function() {
        var val = $(this).val();
        var hasValue = Array.isArray(val) ? val.length > 0 : (val && val.trim());
        if (hasValue) {
            $(this).removeClass('is-invalid');
        }
    });
});
</script>
@endpush
