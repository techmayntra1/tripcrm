@extends('layouts.app')
@section('title', 'Deleted Users')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-people-fill icon-gradient bg-happy-itmeo"></i>
            </div>
            <div>
                Users
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary" title="Add New User">
                <i class="bi bi-plus-lg"></i>
            </a>
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
                <a class="nav-link" href="{{ route('admin.users.index') }}">
                    <i class="bi bi-people-fill me-1"></i> Active ({{ $activeCount }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.users.trashed') }}">
                    <i class="bi bi-archive me-1"></i> Deleted ({{ $users->count() }})
                </a>
            </li>
        </ul>
        <div class="input-group input-group-sm" style="width: 250px;">
            <input type="text" class="form-control" placeholder="Search users..." id="searchInput">
            <button class="btn btn-outline-secondary" type="button">
                <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Deleted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="usersTable">
                @forelse($users as $user)
                <tr>
                    <td><strong class="text-muted">{{ $user->name }}</strong></td>
                    <td><span class="text-muted">{{ $user->email }}</span></td>
                    <td>
                        @if($user->role)
                            <span class="badge bg-secondary">{{ $user->role->name }}</span>
                        @else
                            <span class="badge bg-light text-dark">No Role</span>
                        @endif
                    </td>
                    <td>
                        <small class="text-muted">{{ $user->deleted_at->format('d-m-Y') }}</small>
                    </td>
                    <td>
                        <div class="btn-group-actions">
                            <form action="{{ route('admin.users.restore', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Restore this user?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="empty-state">
                        <i class="bi bi-archive"></i>
                        No deleted users.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer text-muted">
        Showing {{ $users->count() }} deleted {{ Str::plural('user', $users->count()) }}
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('#usersTable tr');
    searchInput.addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        tableRows.forEach(function(row) {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
});
</script>
@endpush
