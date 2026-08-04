@extends('layouts.app')
@section('title', 'Staff & Salary')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-person-badge-fill icon-gradient bg-deep-blue"></i>
            </div>
            <div>
                Staff & Salary Management
            </div>
        </div>
        <div class="page-title-actions">
            @if(auth()->user()->hasPermission('staff', 'create'))
            <a href="{{ route('admin.staff.create') }}" class="btn btn-primary" title="Add Staff">
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
<div class="row">
    <div class="col-12">
        <div class="main-card mb-3 card">
            <div class="card-header has-tabs d-flex justify-content-between align-items-center">
                <ul class="nav lead-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('admin.staff.index') }}">
                            <i class="bi bi-person-badge me-1"></i> Active ({{ $activeCount }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.staff.trashed') }}">
                            <i class="bi bi-archive me-1"></i> Deleted ({{ $inactiveCount }})
                        </a>
                    </li>
                </ul>
                <form method="GET" action="{{ route('admin.staff.index') }}" class="d-flex align-items-center gap-2">
                    <select name="role" class="form-select form-select-sm" style="width: 140px;" onchange="this.form.submit()">
                        <option value="">All Positions</option>
                        <option value="supervisor" {{ request('role') == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                        <option value="site_manager" {{ request('role') == 'site_manager' ? 'selected' : '' }}>Site Manager</option>
                        <option value="designer" {{ request('role') == 'designer' ? 'selected' : '' }}>Designer</option>
                        <option value="carpenter" {{ request('role') == 'carpenter' ? 'selected' : '' }}>Carpenter</option>
                        <option value="electrician" {{ request('role') == 'electrician' ? 'selected' : '' }}>Electrician</option>
                        <option value="plumber" {{ request('role') == 'plumber' ? 'selected' : '' }}>Plumber</option>
                        <option value="painter" {{ request('role') == 'painter' ? 'selected' : '' }}>Painter</option>
                        <option value="helper" {{ request('role') == 'helper' ? 'selected' : '' }}>Helper</option>
                        <option value="driver" {{ request('role') == 'driver' ? 'selected' : '' }}>Driver</option>
                        <option value="office_staff" {{ request('role') == 'office_staff' ? 'selected' : '' }}>Office Staff</option>
                    </select>
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" class="form-control" name="search" placeholder="Search staff..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    @if(request('search') || request('role'))
                        <a href="{{ route('admin.staff.index') }}" class="btn btn-sm btn-danger" title="Clear Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </form>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th >SR</th>
                            <th>Staff Name</th>
                            <th>Position</th>
                            <th>Mobile</th>
                            <th>Salary</th>
                            <th>Joining</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staffMembers as $staff)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('admin.staff.show', $staff) }}"><strong class="name-truncate" title="{{ $staff->name }}">{{ $staff->name }}</strong></a>
                                @if($staff->email)
                                    <br><small class="text-muted email-truncate" title="{{ $staff->email }}">{{ $staff->email }}</small>
                                @endif
                            </td>
                            <td><span class="badge bg-primary">{{ $staff->position_name }}</span></td>
                            <td>{{ $staff->mobile }}</td>
                            <td>{{ formatMoney($staff->salary_amount) }}<small class="text-muted">/mo</small></td>
                            <td>{{ $staff->joining_date ? formatDate($staff->joining_date) : '-' }}</td>
                            <td>
                                <div class="btn-group-actions">
                                    <a href="{{ route('admin.staff.edit', $staff) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="{{ route('admin.staff.show', $staff) }}" class="btn btn-sm btn-outline-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.staff.salary-payments.index', $staff) }}" class="btn btn-sm btn-outline-success" title="Salary">
                                        <i class="bi bi-cash"></i>
                                    </a>
                                    <form action="{{ route('admin.staff.destroy', $staff) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this staff member?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                <i class="bi bi-people"></i>
                                No active staff members found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                @include('partials.pagination', ['paginator' => $staffMembers])
            </div>
        </div>
    </div>
</div>
@endsection
@push('styles')
<style>
    .card-header .form-select,
    .card-header .form-control {
        background-color: #fff !important;
        color: #333 !important;
    }
</style>
@endpush
