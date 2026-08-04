@extends('layouts.app')
@section('title', 'Edit Staff')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-pencil-square icon-gradient bg-deep-blue"></i>
            </div>
            <div>Edit Staff - {{ $staff->name }}</div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.staff.show', $staff) }}" class="btn btn-outline-info me-2">
                <i class="bi bi-eye me-1"></i> View
            </a>
            <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
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

<form action="{{ route('admin.staff.update', $staff) }}" method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-lg-8">
            <div class="main-card mb-3 card">
                <div class="card-header">
                    <i class="bi bi-person me-2"></i> Personal Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $staff->name) }}" required minlength="2" maxlength="30">
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
                                <input type="tel" class="form-control @error('mobile') is-invalid @enderror" id="mobile" name="mobile" value="{{ old('mobile', $staff->mobile) }}" required minlength="10" maxlength="10" inputmode="numeric">
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
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $staff->email) }}" maxlength="100">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="position_id" class="form-label">Position <span class="text-danger">*</span></label>
                                <select class="form-select @error('position_id') is-invalid @enderror" id="position_id" name="position_id" required>
                                    <option value="">Select Position</option>
                                    @foreach($positions as $position)
                                        <option value="{{ $position->id }}" {{ old('position_id', $staff->position_id) == $position->id ? 'selected' : '' }}>{{ $position->name }}</option>
                                    @endforeach
                                </select>
                                @error('position_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Please select a position</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="joining_date" class="form-label">Joining Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('joining_date') is-invalid @enderror" id="joining_date" name="joining_date" value="{{ old('joining_date', $staff->joining_date?->format('Y-m-d')) }}" required>
                                @error('joining_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Please select joining date</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2" maxlength="150">{{ old('address', $staff->address) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="main-card mb-3 card">
                <div class="card-header">
                    <i class="bi bi-cash-stack me-2"></i> Salary Details
                </div>
                <div class="card-body">
                    <input type="hidden" name="salary_type" value="monthly">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="salary_amount" class="form-label">Monthly Salary <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control @error('salary_amount') is-invalid @enderror" id="salary_amount" name="salary_amount" value="{{ old('salary_amount', intval($staff->salary_amount)) }}" min="1" max="999999999" step="1" required inputmode="numeric">
                                    <span class="input-group-text">/month</span>
                                    @error('salary_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="invalid-feedback">Please enter salary amount</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="main-card mb-3 card">
                <div class="card-header">
                    <i class="bi bi-bank me-2"></i> Bank Details
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="bank_name" class="form-label">Bank Name</label>
                                <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{ old('bank_name', $staff->bank_name) }}" maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="account_number" class="form-label">Account Number</label>
                                <input type="text" class="form-control" id="account_number" name="account_number" value="{{ old('account_number', $staff->account_number) }}" maxlength="20">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-0">
                                <label for="ifsc_code" class="form-label">IFSC Code</label>
                                <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" value="{{ old('ifsc_code', $staff->ifsc_code) }}" maxlength="11" style="text-transform: uppercase;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="main-card mb-3 card">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-person-badge me-2"></i> Staff Summary
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-icon-wrapper" style="width: 80px; height: 80px; margin: 0 auto;">
                            <div class="avatar-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                                {{ strtoupper(substr($staff->name, 0, 1)) }}
                            </div>
                        </div>
                        <h5 class="mt-2 mb-0">{{ $staff->name }}</h5>
                        <span class="badge bg-primary">{{ $staff->position_name }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Joined:</span>
                        <strong>{{ $staff->joining_date ? $staff->joining_date->format('d-m-Y') : '-' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Monthly Salary:</span>
                        <strong class="text-success">{{ formatMoney($staff->salary_amount) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Status:</span>
                        @if($staff->trashed())
                            <span class="badge bg-secondary">Inactive</span>
                        @else
                            <span class="badge bg-success">Active</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="main-card mb-3 card">
                <div class="card-header">
                    <i class="bi bi-file-earmark-text me-2"></i> Documents
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="pan_card" class="form-label">PAN Card</label>
                        <input type="file" class="form-control @error('pan_card') is-invalid @enderror" id="pan_card" name="pan_card" accept="image/jpeg,image/png,image/jpg">
                        <small class="text-muted">JPG, JPEG, PNG (Max 2MB)</small>
                        @error('pan_card')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="pan_card_preview" class="mt-2" @if(!$staff->pan_card) style="display: none;" @endif>
                            @if($staff->pan_card)
                                <img src="{{ Storage::url($staff->pan_card) }}" alt="PAN Card" class="img-thumbnail" style="max-height: 120px;">
                                <div class="mt-1"><small class="text-success">Current PAN Card</small></div>
                            @else
                                <img src="" alt="PAN Card Preview" class="img-thumbnail" style="max-height: 120px;">
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label">Aadhar Card @if(!$staff->aadhar_front || !$staff->aadhar_back)<span class="text-danger">*</span>@endif</label>
                        <select class="form-select" id="aadhar_side_select">
                            <option value="">Select Side to Upload/Update</option>
                            <option value="front">Front Side</option>
                            <option value="back">Back Side</option>
                        </select>
                    </div>

                    <div id="aadhar_front_section" style="display: none;">
                        <div class="mb-3">
                            <label for="aadhar_front" class="form-label">Aadhar Front @if(!$staff->aadhar_front)<span class="text-danger">*</span>@endif</label>
                            <input type="file" class="form-control @error('aadhar_front') is-invalid @enderror" id="aadhar_front" name="aadhar_front" accept="image/jpeg,image/png,image/jpg" @if(!$staff->aadhar_front) required @endif>
                            <small class="text-muted">JPG, JPEG, PNG (Max 2MB)</small>
                            @error('aadhar_front')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="aadhar_front_preview" class="mt-2" @if(!$staff->aadhar_front) style="display: none;" @endif>
                                @if($staff->aadhar_front)
                                    <img src="{{ Storage::url($staff->aadhar_front) }}" alt="Aadhar Front" class="img-thumbnail" style="max-height: 120px;">
                                    <div class="mt-1"><small class="text-success">Current Aadhar Front</small></div>
                                @else
                                    <img src="" alt="Aadhar Front Preview" class="img-thumbnail" style="max-height: 120px;">
                                @endif
                            </div>
                        </div>
                    </div>

                    <div id="aadhar_back_section" style="display: none;">
                        <div class="mb-3">
                            <label for="aadhar_back" class="form-label">Aadhar Back @if(!$staff->aadhar_back)<span class="text-danger">*</span>@endif</label>
                            <input type="file" class="form-control @error('aadhar_back') is-invalid @enderror" id="aadhar_back" name="aadhar_back" accept="image/jpeg,image/png,image/jpg" @if(!$staff->aadhar_back) required @endif>
                            <small class="text-muted">JPG, JPEG, PNG (Max 2MB)</small>
                            @error('aadhar_back')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="aadhar_back_preview" class="mt-2" @if(!$staff->aadhar_back) style="display: none;" @endif>
                                @if($staff->aadhar_back)
                                    <img src="{{ Storage::url($staff->aadhar_back) }}" alt="Aadhar Back" class="img-thumbnail" style="max-height: 120px;">
                                    <div class="mt-1"><small class="text-success">Current Aadhar Back</small></div>
                                @else
                                    <img src="" alt="Aadhar Back Preview" class="img-thumbnail" style="max-height: 120px;">
                                @endif
                            </div>
                        </div>
                    </div>

                    <div id="aadhar_status" class="mt-3">
                        <div class="d-flex gap-2">
                            <span id="front_status" class="badge {{ $staff->aadhar_front ? 'bg-success' : 'bg-secondary' }}">
                                Front: {{ $staff->aadhar_front ? 'Uploaded' : 'Not uploaded' }}
                            </span>
                            <span id="back_status" class="badge {{ $staff->aadhar_back ? 'bg-success' : 'bg-secondary' }}">
                                Back: {{ $staff->aadhar_back ? 'Uploaded' : 'Not uploaded' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-lg me-1"></i> Update Staff
                </button>
                <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var hasExistingFront = {{ $staff->aadhar_front ? 'true' : 'false' }};
    var hasExistingBack = {{ $staff->aadhar_back ? 'true' : 'false' }};
    var aadharFrontUploaded = hasExistingFront;
    var aadharBackUploaded = hasExistingBack;

    $('#mobile').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
    });

    $('#ifsc_code').on('input', function() {
        this.value = this.value.toUpperCase().slice(0, 11);
    });

    $('#salary_amount').on('input', function() {
        var val = this.value.replace(/[^0-9]/g, '');
        if (val.length > 7) {
            val = val.slice(0, 7);
        }
        this.value = val;
    });

    $('#salary_amount').on('blur', function() {
        var val = parseInt(this.value) || 0;
        if (val < 1) {
            this.classList.add('is-invalid');
            showSalaryError('Salary must be at least 1.');
        } else if (val > 1000000) {
            this.classList.add('is-invalid');
            showSalaryError('Salary must not exceed 10,00,000.');
        } else {
            this.classList.remove('is-invalid');
            hideSalaryError();
        }
    });

    function showSalaryError(msg) {
        var container = $('#salary_amount').closest('.mb-0');
        if (!container.find('.salary-error').length) {
            container.append('<div class="text-danger small mt-1 salary-error">' + msg + '</div>');
        } else {
            container.find('.salary-error').text(msg);
        }
    }

    function hideSalaryError() {
        $('.salary-error').remove();
    }

    $('#aadhar_side_select').on('change', function() {
        var selected = $(this).val();
        $('#aadhar_front_section').hide();
        $('#aadhar_back_section').hide();

        if (selected === 'front') {
            $('#aadhar_front_section').slideDown();
        } else if (selected === 'back') {
            $('#aadhar_back_section').slideDown();
        }
    });

    function previewImage(input, previewId) {
        const preview = $(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.find('img').attr('src', e.target.result);
                preview.find('small').text('New file selected');
                preview.show();
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function updateAadharStatus() {
        if (aadharFrontUploaded) {
            $('#front_status').removeClass('bg-secondary').addClass('bg-success').text('Front: Uploaded');
        } else {
            $('#front_status').removeClass('bg-success').addClass('bg-secondary').text('Front: Not uploaded');
        }

        if (aadharBackUploaded) {
            $('#back_status').removeClass('bg-secondary').addClass('bg-success').text('Back: Uploaded');
        } else {
            $('#back_status').removeClass('bg-success').addClass('bg-secondary').text('Back: Not uploaded');
        }
    }

    $('#pan_card').on('change', function() {
        previewImage(this, '#pan_card_preview');
    });

    $('#aadhar_front').on('change', function() {
        previewImage(this, '#aadhar_front_preview');
        if (this.files && this.files[0]) {
            aadharFrontUploaded = true;
            updateAadharStatus();
        }
    });

    $('#aadhar_back').on('change', function() {
        previewImage(this, '#aadhar_back_preview');
        if (this.files && this.files[0]) {
            aadharBackUploaded = true;
            updateAadharStatus();
        }
    });

    $('input, select, textarea').on('input change', function() {
        var val = $(this).val();
        var hasValue = Array.isArray(val) ? val.length > 0 : (val && val.trim());
        if (hasValue) {
            $(this).removeClass('is-invalid');
        }
    });
    
    $('.needs-validation').on('submit', function(e) {
        var salaryVal = parseInt($('#salary_amount').val()) || 0;

        if (!aadharFrontUploaded || !aadharBackUploaded) {
            e.preventDefault();
            window.koAlert.warning('Please upload both Aadhar front and back images.');
            return false;
        }

        if (salaryVal < 1 || salaryVal > 1000000) {
            e.preventDefault();
            $('#salary_amount').addClass('is-invalid').focus();
            window.koAlert.warning('Salary must be between 1 and 10,00,000.');
            return false;
        }

        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });
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
</style>
@endpush
