@extends('layouts.app')
@section('title', 'Customers')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-people-fill icon-gradient bg-happy-itmeo"></i>
            </div>
            <div>
                Customers
            </div>
        </div>
        <div class="page-title-actions">
            @if(auth()->user()->hasPermission('customers', 'create'))
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary" title="Add New Customer">
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
<div class="row">
    <div class="col-12">
        <div class="main-card mb-3 card">
            <div class="card-header has-tabs d-flex justify-content-between align-items-center">
                <ul class="nav lead-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('admin.customers.index') }}">
                            <i class="bi bi-people-fill me-1"></i> Active ({{ $customers->total() }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.customers.trashed') }}">
                            <i class="bi bi-archive me-1"></i> Deleted ({{ $trashedCount }})
                        </a>
                    </li>
                </ul>
                <form method="GET" action="{{ route('admin.customers.index') }}" class="d-flex align-items-center gap-2">
                    <select name="payment_type" class="form-select form-select-sm" style="width: 140px;" onchange="this.form.submit()">
                        <option value="">All Payment</option>
                        @foreach(['Cash', 'Cheque', 'Bank Transfer', 'UPI', 'Credit'] as $type)
                            <option value="{{ $type }}" {{ request('payment_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                    <div class="input-group input-group-sm" style="width: 350px;">
                        <input type="text" name="search" class="form-control" placeholder="Search customers" value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    @if(request('search') || request('payment_type'))
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-danger" title="Clear Filters">
                        <i class="bi bi-x-lg"></i>
                    </a>
                    @endif
                </form>
            </div>
            <div class="card-body">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>SR</th>
                                <th>Name</th>
                                <th>Work Type</th>
                                <th>Trips</th>
                                <th>Payment</th>
                                <th>GST Number</th>
                                <th>City</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{ route('admin.customers.show', $customer) }}"><strong class="name-truncate" title="{{ $customer->name }}">{{ $customer->name }}</strong></a>
                                    <br><small class="text-muted">{{ $customer->created_at->format('d-m-Y') }}</small>
                                </td>
                                <td>
                                    @if($customer->work_type && count($customer->work_type))
                                        @foreach(array_slice($customer->work_type, 0, 2) as $type)
                                            <span class="badge bg-primary">{{ $type }}</span>
                                        @endforeach
                                        @if(count($customer->work_type) > 2)
                                            <span class="badge bg-secondary" style="cursor: pointer;" title="{{ implode(', ', array_slice($customer->work_type, 2)) }}">+{{ count($customer->work_type) - 2 }}</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($customer->trips_count > 0)
                                        <a href="{{ route('admin.trips.index', ['customer_id' => $customer->id]) }}" class="trip-count-badge" title="{{ $customer->trips->take(3)->pluck('name')->implode(', ') }}{{ $customer->trips_count > 3 ? '...' : '' }}">
                                            <i class="bi bi-kanban"></i>
                                            <span>{{ $customer->trips_count }}</span>
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($customer->payment_type)
                                        @php
                                            $paymentColors = [
                                                'Cash' => 'bg-success',
                                                'Cheque' => 'bg-warning',
                                                'Bank Transfer' => 'bg-info',
                                                'UPI' => 'bg-primary',
                                                'Credit' => 'bg-danger',
                                            ];
                                        @endphp
                                        <span class="badge {{ $paymentColors[$customer->payment_type] ?? 'bg-secondary' }}">{{ $customer->payment_type }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $customer->gst_number ?? '-' }}</td>
                                <td>{{ $customer->city_name }}</td>
                                <td>
                                    <div class="btn-group-actions">
                                        @if(auth()->user()->hasPermission('customers', 'edit'))
                                        <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @endif
                                        <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-outline-info" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if(auth()->user()->hasPermission('customers', 'delete'))
                                        <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this customer?')">
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
                                <td colspan="8" class="empty-state">
                                    <i class="bi bi-people"></i>
                                    No active customers found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
            </div>
            <div class="card-footer">
                @include('partials.pagination', ['paginator' => $customers])
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
    .trip-count-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.6rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border-radius: 0.5rem;
        font-size: 0.8rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s;
    }
    .trip-count-badge:hover {
        transform: scale(1.05);
        box-shadow: 0 3px 8px rgba(102, 126, 234, 0.4);
        color: #fff;
    }
    .trip-count-badge i {
        font-size: 0.9rem;
    }
</style>
@endpush
@push('scripts')
<script>
$(document).ready(function() {
    $('[title]').tooltip();
});
</script>
@endpush
