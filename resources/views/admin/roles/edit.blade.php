@extends('layouts.app')
@section('title', 'Edit Role')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-shield-fill icon-gradient bg-happy-itmeo"></i>
            </div>
            <div>
                Edit Role: {{ $role->name }}
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>
@if($role->slug === 'admin')
<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    Admin role has all permissions by default and cannot be modified.
</div>
@endif
<form action="{{ route('admin.roles.update', $role) }}" method="POST" class="needs-validation" novalidate>
    @csrf
    @method('PUT')
    <div class="main-card mb-3 card">
        <div class="card-header">
            <i class="bi bi-shield-fill me-2"></i> Role Information
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $role->name) }}" placeholder="Enter role name" required {{ $role->slug === 'admin' ? 'readonly' : '' }}>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" value="{{ old('description', $role->description) }}" placeholder="Enter role description">
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($role->slug !== 'admin')
    <div class="main-card mb-3 card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-key me-2"></i> Permissions</span>
            <div>
                <button type="button" class="btn btn-sm btn-outline-success" id="selectAll">
                    <i class="bi bi-check-all me-1"></i> Select All
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">
                    <i class="bi bi-x-lg me-1"></i> Deselect All
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($modules as $moduleKey => $module)
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card permission-card">
                        <div class="card-header py-2 d-flex align-items-center">
                            <div class="permission-module-icon text-white rounded me-2">
                                <i class="bi {{ $module['icon'] }}"></i>
                            </div>
                            <strong>{{ $module['name'] }}</strong>
                            <div class="form-check form-switch ms-auto">
                                <input class="form-check-input module-toggle" type="checkbox" data-module="{{ $moduleKey }}" id="toggle_{{ $moduleKey }}">
                            </div>
                        </div>
                        <div class="card-body p-4 ps-3">
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($actions as $actionKey => $action)
                                    @php
                                        $permission = isset($permissions[$moduleKey]) ? $permissions[$moduleKey]->firstWhere('action', $actionKey) : null;
                                    @endphp
                                    @if($permission)
                                    <label class="permission-btn {{ $action['color'] }}" title="{{ $action['name'] }}">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="permission-checkbox" data-module="{{ $moduleKey }}" {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                        <span class="permission-icon">
                                            <i class="bi {{ $action['icon'] }}"></i>
                                        </span>
                                        <span class="permission-label">{{ $action['name'] }}</span>
                                    </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> Update Role
        </button>
    </div>
</form>
@endsection
@push('styles')
<style>
.permission-module-icon {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}
.permission-btn {
    display: inline-flex;
    align-items: center;
    padding: 0.3rem 0.5rem;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s;
    background: transparent !important;
    border: 1.5px solid #212529;
    color: #212529;
}
.permission-btn:hover {
    background: #f8f9fa !important;
}
.permission-btn input[type="checkbox"] {
    display: none;
}
.permission-btn:has(input:checked) {
    border-style: solid;
    color: #fff !important;
}
.permission-btn:has(input:checked).bg-info { background: #0dcaf0 !important; border-color: #0dcaf0; }
.permission-btn:has(input:checked).bg-success { background: #198754 !important; border-color: #198754; }
.permission-btn:has(input:checked).bg-warning { background: #ffc107 !important; border-color: #ffc107; color: #000 !important; }
.permission-btn:has(input:checked).bg-danger { background: #dc3545 !important; border-color: #dc3545; }
.permission-icon {
    font-size: 0.9rem;
}
.permission-label {
    font-size: 0.75rem;
    margin-left: 0.3rem;
    font-weight: 500;
}
.permission-card {
    border: 1px solid #e9ecef;
}
.permission-card .card-header {
    background: #f8f9fa;
}
</style>
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.module-toggle').forEach(function(toggle) {
        updateModuleToggle(toggle.dataset.module);
    });

    document.querySelectorAll('.module-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const module = this.dataset.module;
            const checkboxes = document.querySelectorAll('.permission-checkbox[data-module="' + module + '"]');
            checkboxes.forEach(function(cb) {
                cb.checked = toggle.checked;
            });
        });
    });

    document.querySelectorAll('.permission-checkbox').forEach(function(cb) {
        cb.addEventListener('change', function() {
            updateModuleToggle(this.dataset.module);
        });
    });

    function updateModuleToggle(module) {
        const checkboxes = document.querySelectorAll('.permission-checkbox[data-module="' + module + '"]');
        const toggle = document.querySelector('.module-toggle[data-module="' + module + '"]');
        if (!toggle) return;
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        const someChecked = Array.from(checkboxes).some(cb => cb.checked);
        toggle.checked = allChecked;
        toggle.indeterminate = someChecked && !allChecked;
    }

    var selectAllBtn = document.getElementById('selectAll');
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            document.querySelectorAll('.permission-checkbox').forEach(function(cb) {
                cb.checked = true;
            });
            document.querySelectorAll('.module-toggle').forEach(function(toggle) {
                toggle.checked = true;
                toggle.indeterminate = false;
            });
        });
    }

    var deselectAllBtn = document.getElementById('deselectAll');
    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function() {
            document.querySelectorAll('.permission-checkbox').forEach(function(cb) {
                cb.checked = false;
            });
            document.querySelectorAll('.module-toggle').forEach(function(toggle) {
                toggle.checked = false;
                toggle.indeterminate = false;
            });
        });
    }

    (function () {
        'use strict';
        const forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
});
</script>
@endpush
