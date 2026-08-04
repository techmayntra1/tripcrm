@extends('layouts.app')
@section('title', 'Deleted Vendors')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-shop icon-gradient bg-arielle-smile"></i>
            </div>
            <div>
                Vendors
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.vendors.create') }}" class="btn btn-primary" title="Add New Vendor">
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
                        <a class="nav-link" href="{{ route('admin.vendors.index') }}">
                            <i class="bi bi-shop me-1"></i> Active ({{ $activeCount }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('admin.vendors.trashed') }}">
                            <i class="bi bi-archive me-1"></i> Deleted ({{ $vendors->total() }})
                        </a>
                    </li>
                </ul>
                <form method="GET" action="{{ route('admin.vendors.trashed') }}" class="d-flex align-items-center gap-2">
                    <select name="category" class="form-select form-select-sm" style="width: 160px;" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <div class="input-group input-group-sm" style="width: 320px;">
                        <input type="text" name="search" class="form-control" placeholder="Search name, mobile, GST..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    @if(request('search') || request('category'))
                    <a href="{{ route('admin.vendors.trashed') }}" class="btn btn-sm btn-danger" title="Clear Filters">
                        <i class="bi bi-x-lg"></i>
                    </a>
                    @endif
                </form>
            </div>
            <div class="card-body">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Vendor Name</th>
                                <th>Categories</th>
                                <th>Contact</th>
                                <th>Total Paid</th>
                                <th>Outstanding</th>
                                <th>Deleted</th>
                                <th width="100">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vendors as $vendor)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.vendors.trashed.show', $vendor->id) }}"><strong class="text-muted">{{ $vendor->name }}</strong></a>
                                    @if($vendor->gst_number)
                                    <br><small class="text-muted">GST: {{ $vendor->gst_number }}</small>
                                    @endif
                                </td>
                                <td>
                                    @php $categoryNames = $vendor->category_names; @endphp
                                    @if(count($categoryNames))
                                        @foreach(array_slice($categoryNames, 0, 2) as $catName)
                                            <span class="badge bg-secondary">{{ $catName }}</span>
                                        @endforeach
                                        @if(count($categoryNames) > 2)
                                            <span class="badge bg-secondary" style="cursor: pointer;" title="{{ implode(', ', array_slice($categoryNames, 2)) }}">+{{ count($categoryNames) - 2 }}</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    {{ $vendor->mobile }}
                                    @if($vendor->email)
                                    <br><small class="text-muted">{{ $vendor->email }}</small>
                                    @endif
                                </td>
                                <td class="text-success">{{ formatMoney($vendor->total_paid) }}</td>
                                <td class="{{ $vendor->outstanding > 0 ? 'text-danger' : '' }}">{{ formatMoney($vendor->outstanding) }}</td>
                                <td>
                                    <small class="text-muted">{{ $vendor->deleted_at->format('d-m-Y') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.vendors.trashed.show', $vendor->id) }}" class="btn btn-sm btn-outline-info" title="Details">
                                            <i class="bi bi-info-circle"></i>
                                        </a>
                                        <form action="{{ route('admin.vendors.restore', $vendor->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Restore this vendor?')">
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
                                    No deleted vendors.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
            </div>
            <div class="card-footer">
                @include('partials.pagination', ['paginator' => $vendors])
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
