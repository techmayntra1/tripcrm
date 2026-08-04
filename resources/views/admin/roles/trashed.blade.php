@extends('layouts.app')
@section('title', 'Deleted Roles')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-shield-lock-fill icon-gradient bg-happy-itmeo"></i>
            </div>
            <div>
                Roles
            </div>
        </div>
        <div class="page-title-actions">
            @if(auth()->user()->hasPermission('roles', 'create'))
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary" title="Add New Role">
                <i class="bi bi-plus-lg"></i>
            </a>
            @endif
        </div>
    </div>
</div>
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div class="main-card mb-3 card">
    <div class="card-header has-tabs d-flex justify-content-between align-items-center">
        <ul class="nav lead-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.roles.index') }}">
                    <i class="bi bi-shield-lock me-1"></i> Active ({{ $activeCount }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.roles.trashed') }}">
                    <i class="bi bi-archive me-1"></i> Deleted ({{ $roles->count() }})
                </a>
            </li>
        </ul>
    </div>
    <table class="table table-hover table-striped mb-0">
        <thead>
            <tr>
                <th>SR</th>
                <th>Name</th>
                <th>Description</th>
                <th>Permissions</th>
                <th>Deleted</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($roles as $role)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    <strong class="name-truncate text-muted" title="{{ $role->name }}">{{ $role->name }}</strong>
                </td>
                <td><span class="desc-truncate text-muted">{{ $role->description ?? '-' }}</span></td>
                <td>
                    @if($role->slug === 'admin')
                        <span class="badge bg-success"><i class="bi bi-check-all me-1"></i>Full Access</span>
                    @else
                        @php
                            $roleModules = $role->permissions->pluck('module')->unique();
                            $moduleCount = $roleModules->count();
                        @endphp
                        @if($moduleCount > 0)
                            <a href="#" class="badge bg-primary text-decoration-none" data-bs-toggle="modal" data-bs-target="#permissionsModal{{ $role->id }}">
                                <i class="bi bi-key me-1"></i>{{ $moduleCount }}/{{ $totalModules }} modules
                            </a>
                        @else
                            <span class="badge bg-light text-dark">No Access</span>
                        @endif
                    @endif
                </td>
                <td>
                    <small class="text-muted">{{ $role->deleted_at->format('d-m-Y') }}</small>
                </td>
                <td class="col-actions">
                    <div class="btn-group-actions">
                        @if(auth()->user()->hasPermission('roles', 'delete'))
                        <form action="{{ route('admin.roles.restore', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Restore this role?')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="empty-state">
                    <i class="bi bi-archive"></i>
                    No deleted roles.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('modals')
@foreach($roles as $role)
@if($role->slug !== 'admin')
@php
    $rolePermissions = $role->permissions->groupBy('module');
@endphp
<div class="modal fade" id="permissionsModal{{ $role->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-shield-lock me-2"></i>Permissions: {{ $role->name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    @foreach($modules as $moduleKey => $moduleInfo)
                    @php
                        $modulePerms = $rolePermissions->get($moduleKey, collect());
                        $hasAccess = $modulePerms->isNotEmpty();
                    @endphp
                    <div class="col-md-4 col-sm-6">
                        <div class="card {{ $hasAccess ? 'border-success' : 'border-light bg-light' }}">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi {{ $moduleInfo['icon'] }} me-2 {{ $hasAccess ? 'text-success' : 'text-muted' }}"></i>
                                    <strong class="{{ $hasAccess ? '' : 'text-muted' }}">{{ $moduleInfo['name'] }}</strong>
                                    @if($hasAccess)
                                        <i class="bi bi-check-circle-fill text-success ms-auto"></i>
                                    @endif
                                </div>
                                @if($hasAccess)
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($modulePerms as $perm)
                                        <span class="badge bg-{{ $perm->action === 'view' ? 'info' : ($perm->action === 'create' ? 'success' : ($perm->action === 'edit' ? 'warning' : 'danger')) }}" style="font-size: 0.7rem;">
                                            {{ ucfirst($perm->action) }}
                                        </span>
                                    @endforeach
                                </div>
                                @else
                                <small class="text-muted">No access</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif
@endforeach
@endpush
