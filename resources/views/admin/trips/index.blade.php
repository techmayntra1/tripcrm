@extends('layouts.app')
@section('title', 'Trips')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-kanban icon-gradient bg-strong-bliss"></i>
            </div>
            <div>
                Trips
            </div>
        </div>
        <div class="page-title-actions">
            @if(request('customer_id'))
                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Customers
                </a>
            @endif
            @if(auth()->user()->hasPermission('trips', 'create'))
            <a href="{{ route('admin.trips.create') }}" class="btn btn-primary" title="Create Trip">
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
                <a class="nav-link {{ request('tab') != 'deleted' ? 'active' : '' }}" href="{{ route('admin.trips.index') }}">
                    <i class="bi bi-kanban me-1"></i> Active ({{ $allCount }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('tab') == 'deleted' ? 'active' : '' }}" href="{{ route('admin.trips.index', ['tab' => 'deleted']) }}">
                    <i class="bi bi-archive me-1"></i> Deleted ({{ $deletedCount }})
                </a>
            </li>
        </ul>
        <div class="d-flex align-items-center gap-2">
            @if(request('tab') != 'deleted')
            <form method="GET" action="{{ route('admin.trips.index') }}" id="filterForm">
                @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                <select name="tab" class="form-select form-select-sm" style="width: auto;" onchange="document.getElementById('filterForm').submit()">
                    <option value="" {{ !request('tab') ? 'selected' : '' }}>All</option>
                    <option value="planning" {{ request('tab') == 'planning' ? 'selected' : '' }}>Planning ({{ $planningCount }})</option>
                    <option value="in_progress" {{ request('tab') == 'in_progress' ? 'selected' : '' }}>In Progress ({{ $inProgressCount }})</option>
                    <option value="on_hold" {{ request('tab') == 'on_hold' ? 'selected' : '' }}>On Hold ({{ $onHoldCount }})</option>
                    <option value="completed" {{ request('tab') == 'completed' ? 'selected' : '' }}>Completed ({{ $completedCount }})</option>
                </select>
            </form>
            @endif
            <form method="GET" action="{{ route('admin.trips.index') }}" class="d-flex align-items-center gap-2">
                @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
                @endif
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" name="search" class="form-control" placeholder="Search trips" value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                @if(request('search'))
                <a href="{{ route('admin.trips.index', request('tab') ? ['tab' => request('tab')] : []) }}" class="btn btn-sm btn-danger" title="Clear Filters">
                    <i class="bi bi-x-lg"></i>
                </a>
                @endif
            </form>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th >SR</th>
                    <th>Trip</th>
                    <th>Client</th>
                    <th>Budget</th>
                    <th>Add On</th>
                    <th>Spent</th>
                    <th>Income</th>
                    <th>Profit</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
                <tbody>
                    @forelse($trips as $trip)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <a href="{{ route('admin.trips.show', $trip) }}"><strong class="name-truncate" title="{{ $trip->name }}">{{ $trip->name }}</strong></a>
                            <br><small class="text-muted">{{ $trip->start_date ? $trip->start_date->format('d-m-Y') : '-' }}</small>
                        </td>
                        <td>
                            @if($trip->customer)
                            <div>
                                <a href="{{ route('admin.customers.show', $trip->customer) }}" class="name-truncate" title="{{ $trip->customer->name }}"><strong>{{ $trip->customer->name }}</strong></a>
                                <br><small class="text-muted">{{ $trip->customer->mobile }}</small>
                                <br><small class="text-muted">Company: {{ $trip->company->name ?? '-' }}</small>
                            </div>
                            @else
                                <small class="text-muted">Company: {{ $trip->company->name ?? '-' }}</small>
                            @endif
                        </td>
                        <td>{{ formatMoney($trip->budget) }}</td>
                        <td class="{{ $trip->add_on_total > 0 ? 'text-info' : 'text-muted' }}">{{ formatMoney($trip->add_on_total) }}</td>
                        <td class="text-danger">{{ formatMoney($trip->total_spent) }}</td>
                        <td class="text-success">{{ formatMoney($trip->total_income) }}</td>
                        <td class="{{ $trip->profit >= 0 ? 'text-success' : 'text-danger' }}"><strong>{{ formatMoney($trip->profit) }}</strong></td>
                        <td>
                            @php
                                $statusColors = [
                                    'planning' => 'bg-secondary',
                                    'in_progress' => 'bg-warning',
                                    'on_hold' => 'bg-info',
                                    'completed' => 'bg-success',
                                    'cancelled' => 'bg-danger',
                                ];
                                $statusLabels = [
                                    'planning' => 'Planning',
                                    'in_progress' => 'In Progress',
                                    'on_hold' => 'On Hold',
                                    'completed' => 'Completed',
                                    'cancelled' => 'Cancelled',
                                ];
                            @endphp
                            <span class="badge {{ $statusColors[$trip->status] ?? 'bg-secondary' }}">
                                {{ $statusLabels[$trip->status] ?? ucfirst($trip->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group-actions">
                                @if($trip->trashed())
                                    @if(auth()->user()->hasPermission('trips', 'delete'))
                                    <form action="{{ route('admin.trips.restore', $trip->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>
                                    @endif
                                @else
                                    @if(auth()->user()->hasPermission('trips', 'edit'))
                                    <a href="{{ route('admin.trips.edit', $trip) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endif
                                    <a href="{{ route('admin.trips.show', $trip) }}" class="btn btn-sm btn-outline-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(auth()->user()->hasPermission('trips', 'delete'))
                                    <form action="{{ route('admin.trips.destroy', $trip) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this trip?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="empty-state">
                            <i class="bi bi-kanban"></i>
                            No trips found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
    </div>
    <div class="card-footer">
        @include('partials.pagination', ['paginator' => $trips])
    </div>
</div>
@endsection
@push('styles')
<style>
    .trip-filter-select {
        background-color: #6f42c1 !important;
        color: #fff !important;
        border: none !important;
        font-weight: 600;
        padding: 8px 35px 8px 15px;
        border-radius: 6px;
        cursor: pointer;
        min-width: 160px;
    }
    .trip-filter-select:focus {
        box-shadow: 0 0 0 3px rgba(111, 66, 193, 0.4) !important;
    }
    .trip-filter-select option {
        background-color: #fff;
        color: #333;
        padding: 10px;
    }
    .card-header .form-control {
        background-color: #fff !important;
        color: #333 !important;
    }
</style>
@endpush
