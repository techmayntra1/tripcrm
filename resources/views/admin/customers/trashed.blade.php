@extends('layouts.app')
@section('title', 'Deleted Customers')
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
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary" title="Add New Customer">
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
<div class="row">
    <div class="col-12">
        <div class="main-card mb-3 card">
            <div class="card-header has-tabs d-flex justify-content-between align-items-center">
                <ul class="nav lead-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.customers.index') }}">
                            <i class="bi bi-people-fill me-1"></i> Active ({{ $activeCount }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('admin.customers.trashed') }}">
                            <i class="bi bi-archive me-1"></i> Deleted ({{ $customers->total() }})
                        </a>
                    </li>
                </ul>
                <form method="GET" action="{{ route('admin.customers.trashed') }}" class="d-flex align-items-center gap-2">
                    <select name="payment_type" class="form-select form-select-sm" style="width: 140px;" onchange="this.form.submit()">
                        <option value="">All Payment</option>
                        @foreach(['Cash', 'Cheque', 'Bank Transfer', 'UPI', 'Credit'] as $type)
                            <option value="{{ $type }}" {{ request('payment_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                    <div class="input-group input-group-sm" style="width: 350px;">
                        <input type="text" name="search" class="form-control" placeholder="Search name, mobile, email, city..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    @if(request('search') || request('payment_type'))
                    <a href="{{ route('admin.customers.trashed') }}" class="btn btn-sm btn-danger" title="Clear Filters">
                        <i class="bi bi-x-lg"></i>
                    </a>
                    @endif
                </form>
            </div>
            <div class="card-body">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Work Type</th>
                                <th>Projects</th>
                                <th>Payment Type</th>
                                <th>City</th>
                                <th>Deleted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.customers.trashed.show', $customer->id) }}"><strong class="text-muted">{{ $customer->name }}</strong></a>
                                    <br><small class="text-muted">{{ $customer->created_at->format('d-m-Y') }}</small>
                                </td>
                                <td>
                                    @if($customer->work_type && count($customer->work_type))
                                        @foreach(array_slice($customer->work_type, 0, 2) as $type)
                                            <span class="badge bg-secondary">{{ $type }}</span>
                                        @endforeach
                                        @if(count($customer->work_type) > 2)
                                            <span class="badge bg-secondary" style="cursor: pointer;" title="{{ implode(', ', array_slice($customer->work_type, 2)) }}">+{{ count($customer->work_type) - 2 }}</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($customer->projects_count > 0)
                                        <span class="badge bg-secondary">
                                            {{ $customer->projects_count }} {{ Str::plural('Project', $customer->projects_count) }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($customer->payment_type)
                                        <span class="badge bg-secondary">{{ $customer->payment_type }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $customer->city_name }}</td>
                                <td>
                                    <small class="text-muted">{{ $customer->deleted_at->format('d-m-Y') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.customers.trashed.show', $customer->id) }}" class="btn btn-sm btn-outline-info" title="Details">
                                            <i class="bi bi-info-circle"></i>
                                        </a>
                                        <form action="{{ route('admin.customers.restore', $customer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Restore this customer?')">
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
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-archive fs-3 d-block mb-2"></i>
                                    No deleted customers.
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
</style>
@endpush
