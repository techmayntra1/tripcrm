@extends('layouts.app')
@section('title', 'Users')
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
                <a class="nav-link active" href="{{ route('admin.users.index') }}">
                    <i class="bi bi-people-fill me-1"></i> Active ({{ $activeCount }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.users.trashed') }}">
                    <i class="bi bi-archive me-1"></i> Deleted ({{ $trashedCount }})
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
    <div class="card-body">
    <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th>SR</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="usersTable">
            @forelse($users as $user)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    <a href="{{ route('admin.users.edit', $user) }}" title="Edit">
                        <strong class="name-truncate" title="{{ $user->name }}">{{ $user->name }}</strong>
                    </a>
                    @if($user->id === auth()->id())
                        <span class="badge bg-info ms-1">You</span>
                    @endif
                </td>
                <td><span class="email-truncate" title="{{ $user->email }}">{{ $user->email }}</span></td>
                <td class="col-status">
                    @if($user->role)
                        <span class="badge {{ $user->role->slug === 'admin' ? 'bg-warning text-dark' : 'bg-secondary' }}">
                            {{ $user->role->name }}
                        </span>
                    @else
                        <span class="badge bg-light text-dark">No Role</span>
                    @endif
                </td>
                <td class="col-date">{{ formatDate($user->created_at) }}</td>
                <td class="col-actions">
                    <div class="btn-group-actions">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this user?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                <i class="bi bi-archive"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="empty-state">
                    <i class="bi bi-people"></i>
                    No active users found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="card-footer text-muted">
        Showing {{ $users->count() }} {{ Str::plural('user', $users->count()) }}
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
