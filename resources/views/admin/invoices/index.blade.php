@extends('layouts.app')
@section('title', 'Invoices')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-receipt-cutoff icon-gradient bg-grow-early"></i>
            </div>
            <div>
                Invoices
            </div>
        </div>
        <div class="page-title-actions">
            @if(auth()->user()->hasPermission('invoices', 'create'))
            <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary" title="Create Invoice">
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
                        <a class="nav-link active" href="{{ route('admin.invoices.index') }}">
                            <i class="bi bi-receipt me-1"></i> Active ({{ $activeCount ?? $invoices->total() }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.invoices.trashed') }}">
                            <i class="bi bi-archive me-1"></i> Deleted ({{ $trashedCount ?? 0 }})
                        </a>
                    </li>
                </ul>
                <form action="{{ route('admin.invoices.index') }}" method="GET" class="d-flex align-items-center gap-2">
                    <select class="form-select form-select-sm" name="status" onchange="this.form.submit()" style="width: 130px;">
                        <option value="">All Status</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                    <div class="input-group input-group-sm" style="width: 320px;">
                        <input type="text" class="form-control" name="search" placeholder="Search invoices..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    @if(request('search') || request('status'))
                    <a href="{{ route('admin.invoices.index') }}" class="btn btn-sm btn-danger" title="Clear Filters">
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
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Trip</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                        <tr>
                            <td>{{ $loop->iteration + ($invoices->currentPage() - 1) * $invoices->perPage() }}</td>
                            <td><a href="{{ route('admin.invoices.show', $invoice) }}"><strong>{{ $invoice->invoice_number }}</strong></a></td>
                            <td>{{ formatDate($invoice->date) }}</td>
                            <td><span class="name-truncate" title="{{ $invoice->customer->name ?? '-' }}">{{ $invoice->customer->name ?? '-' }}</span></td>
                            <td>
                                @if($invoice->trip)
                                    <a href="{{ route('admin.trips.show', $invoice->trip) }}">{{ $invoice->trip->trip_number }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ formatMoney($invoice->grand_total, 0, $invoice) }}</td>
                            <td class="text-success">{{ formatMoney($invoice->amount_paid, 0, $invoice) }}</td>
                            <td class="{{ $invoice->balance_due > 0 ? 'text-danger' : '' }}">{{ formatMoney($invoice->balance_due, 0, $invoice) }}</td>
                            <td><span class="badge bg-{{ $invoice->status_color }}">{{ ucfirst($invoice->status) }}</span></td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    @if($invoice->status !== 'paid')
                                    <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endif
                                    <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.invoices.destroy', $invoice) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this invoice?')">
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
                            <td colspan="10" class="empty-state">
                                <i class="bi bi-receipt"></i>
                                No invoices found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                @include('partials.pagination', ['paginator' => $invoices])
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
