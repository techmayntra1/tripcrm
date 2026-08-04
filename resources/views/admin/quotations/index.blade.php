@extends('layouts.app')
@section('title', 'Quotations')
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
            @if(auth()->user()->hasPermission('quotations', 'create'))
            <a href="{{ route('admin.quotations.create') }}" class="btn btn-primary" title="Create Quotation">
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
                        <a class="nav-link active" href="{{ route('admin.quotations.index') }}">
                            <i class="bi bi-file-earmark-text me-1"></i> Active ({{ $quotations->total() }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.quotations.trashed') }}">
                            <i class="bi bi-archive me-1"></i> Deleted ({{ $trashedCount ?? 0 }})
                        </a>
                    </li>
                </ul>
                <form action="{{ route('admin.quotations.index') }}" method="GET" class="d-flex align-items-center gap-2">
                    <select class="form-select form-select-sm" name="company" onchange="this.form.submit()" style="width: 150px;">
                        <option value="">All Companies</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ request('company') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                        @endforeach
                    </select>
                    <select class="form-select form-select-sm" name="status" onchange="this.form.submit()" style="width: 120px;">
                        <option value="">All Status</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                    <div class="input-group input-group-sm" style="width: 320px;">
                        <input type="text" class="form-control" name="search" placeholder="Search quotations..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    @if(request('search') || request('status') || request('company'))
                    <a href="{{ route('admin.quotations.index') }}" class="btn btn-sm btn-danger" title="Clear Filters">
                        <i class="bi bi-x-lg"></i>
                    </a>
                    @endif
                </form>
            </div>
            <div class="card-body">
                <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th >SR</th>
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
                    <td><a href="{{ route('admin.quotations.show', $quotation) }}"><strong>{{ $quotation->quotation_number }}</strong></a></td>
                    <td>{{ formatDate($quotation->date) }}</td>
                    <td><span class="name-truncate" title="{{ $quotation->customer->name ?? '-' }}">{{ $quotation->customer->name ?? '-' }}</span></td>
                    <td><span class="name-truncate" title="{{ $quotation->company->name ?? '-' }}">{{ $quotation->company->name ?? '-' }}</span></td>
                    <td>{{ formatMoney($quotation->grand_total) }}</td>
                    <td>
                        <span class="badge bg-{{ $quotation->status_color }}">{{ ucfirst($quotation->status) }}</span>
                        @if($quotation->hasInvoice())
                        <a href="{{ route('admin.invoices.show', $quotation->invoices->first()) }}" class="badge bg-success text-decoration-none" title="View Invoice">
                            <i class="bi bi-receipt-cutoff me-1"></i>Invoiced
                        </a>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group-actions">
                            @if($quotation->status !== 'accepted')
                            <a href="{{ route('admin.quotations.edit', $quotation) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                            <a href="{{ route('admin.quotations.show', $quotation) }}" class="btn btn-sm btn-outline-info" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($quotation->status === 'accepted' && !$quotation->hasInvoice())
                            <a href="{{ route('admin.invoices.create') }}?quotation_id={{ $quotation->id }}" class="btn btn-sm btn-outline-success" title="Convert to Invoice">
                                <i class="bi bi-receipt"></i>
                            </a>
                            @endif
                            <form action="{{ route('admin.quotations.destroy', $quotation) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this quotation?')">
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
                    <td colspan="8" class="empty-state">
                        <i class="bi bi-file-earmark-text"></i>
                        No quotations found.
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
@push('styles')
<style>
    .card-header .form-select,
    .card-header .form-control {
        background-color: #fff !important;
        color: #333 !important;
    }
</style>
@endpush
