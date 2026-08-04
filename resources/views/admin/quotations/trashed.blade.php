@extends('layouts.app')
@section('title', 'Deleted Quotations')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-file-earmark-text-fill icon-gradient bg-premium-dark"></i>
            </div>
            <div>
                Quotations
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.quotations.create') }}" class="btn btn-primary" title="Create Quotation">
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
                        <a class="nav-link" href="{{ route('admin.quotations.index') }}">
                            <i class="bi bi-file-earmark-text me-1"></i> Active ({{ $activeCount ?? 0 }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('admin.quotations.trashed') }}">
                            <i class="bi bi-archive me-1"></i> Deleted ({{ $quotations->total() }})
                        </a>
                    </li>
                </ul>
                <form action="{{ route('admin.quotations.trashed') }}" method="GET" class="d-flex align-items-center gap-2">
                    <select class="form-select form-select-sm" name="company" onchange="this.form.submit()" style="width: 150px;">
                        <option value="">All Companies</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ request('company') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                        @endforeach
                    </select>
                    <div class="input-group input-group-sm" style="width: 320px;">
                        <input type="text" class="form-control" name="search" placeholder="Search quotations..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    @if(request('search') || request('company'))
                    <a href="{{ route('admin.quotations.trashed') }}" class="btn btn-sm btn-danger" title="Clear Filters">
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
                            <th>Quotation #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Company</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotations as $quotation)
                        <tr>
                            <td>{{ $loop->iteration + ($quotations->currentPage() - 1) * $quotations->perPage() }}</td>
                            <td><strong>{{ $quotation->quotation_number }}</strong></td>
                            <td>{{ formatDate($quotation->date) }}</td>
                            <td><span class="name-truncate" title="{{ $quotation->customer->name ?? '-' }}">{{ $quotation->customer->name ?? '-' }}</span></td>
                            <td><span class="name-truncate" title="{{ $quotation->company->name ?? '-' }}">{{ $quotation->company->name ?? '-' }}</span></td>
                            <td>{{ formatMoney($quotation->grand_total) }}</td>
                            <td><span class="badge bg-{{ $quotation->status_color }}">{{ ucfirst($quotation->status) }}</span></td>
                            <td>
                                <div class="btn-group-actions">
                                    <form action="{{ route('admin.quotations.restore', $quotation->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                <i class="bi bi-file-earmark-text"></i>
                                No deleted quotations found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                @include('partials.pagination', ['paginator' => $quotations])
            </div>
        </div>
    </div>
</div>
@endsection
