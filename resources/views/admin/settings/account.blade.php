@extends('layouts.app')
@section('title', 'Account Settings')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-person-gear icon-gradient bg-premium-dark"></i>
            </div>
            <div>
                Account Settings
                
            </div>
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
    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div class="row">
    <div class="col-md-4">
        <div class="main-card mb-3 card">
            <div class="card-body text-center">
                <div class="mb-3 position-relative d-inline-block">
                    @if(auth()->user()->profile_photo_url)
                        <img src="{{ auth()->user()->profile_photo_url }}" class="rounded-circle" width="120" height="120" alt="Profile" id="profilePhotoPreview" style="object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 120px; height: 120px; font-size: 3rem;">
                            <i class="bi bi-person-fill"></i>
                        </div>
                    @endif
                </div>
                <h5 class="mb-1">{{ auth()->user()->name }}</h5>
                <p class="text-muted mb-3">{{ auth()->user()->role?->name ?? 'User' }}</p>
                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#uploadPhotoModal">
                        <i class="bi bi-camera me-1"></i> Change Photo
                    </button>
                    @if(auth()->user()->profile_photo)
                    <form action="{{ route('admin.settings.photo.remove') }}" method="POST" class="d-inline" onsubmit="return confirm('Remove profile photo?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-shield-check me-2"></i> Security
            </div>
            <div class="list-group list-group-flush">
                @if(auth()->user()->isAdmin())
                <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    <i class="bi bi-key me-2"></i> Change Password
                </a>
                @else
                <div class="list-group-item text-muted">
                    <i class="bi bi-info-circle me-2"></i> To change your password, please contact an administrator.
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-person me-2"></i> Profile Information
            </div>
            <div class="card-body">
                <form action="{{ route('admin.settings.profile.update') }}" method="POST" class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', auth()->user()->name) }}" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', auth()->user()->email) }}" required>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', auth()->user()->phone) }}">
                                @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->role?->name ?? 'User' }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('modals')
<div class="modal fade" id="uploadPhotoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.settings.photo.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-camera me-2"></i>Upload Profile Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        @if(auth()->user()->profile_photo_url)
                            <img src="{{ auth()->user()->profile_photo_url }}" class="rounded-circle mb-3" width="150" height="150" alt="Preview" id="uploadPreview" style="object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-light text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px; font-size: 4rem;" id="uploadPreviewPlaceholder">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <img src="" class="rounded-circle mb-3 d-none" width="150" height="150" alt="Preview" id="uploadPreview" style="object-fit: cover;">
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Choose Photo <span class="text-danger">*</span></label>
                        <input type="file" name="profile_photo" class="form-control" accept="image/jpeg,image/png,image/jpg,image/gif" required id="photoInput">
                        <div class="form-text">Max file size: 2MB. Allowed formats: JPEG, PNG, JPG, GIF</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i> Upload Photo</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.settings.password.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-key me-2"></i>Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="Enter current password" required>
                            <span class="input-group-text toggle-password" style="cursor: pointer;" data-target="#current_password">
                                <i class="bi bi-eye text-muted"></i>
                            </span>
                            @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" id="new_password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter new password" required>
                            <span class="input-group-text toggle-password" style="cursor: pointer;" data-target="#new_password">
                                <i class="bi bi-eye text-muted"></i>
                            </span>
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="password_confirmation_modal" class="form-control" placeholder="Confirm new password" required>
                            <span class="input-group-text toggle-password" style="cursor: pointer;" data-target="#password_confirmation_modal">
                                <i class="bi bi-eye text-muted"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
@push('scripts')
<script>
document.getElementById('photoInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('uploadPreview');
            const placeholder = document.getElementById('uploadPreviewPlaceholder');
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            if (placeholder) {
                placeholder.classList.add('d-none');
            }
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
@endsection
