@extends('layouts.app')
@section('title', 'Deleted Invoices')
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
            <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary" title="Create Invoice">
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
                        <a class="nav-link" href="{{ route('admin.invoices.index') }}">
                            <i class="bi bi-receipt me-1"></i> Active ({{ $activeCount ?? 0 }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('admin.invoices.trashed') }}">
                            <i class="bi bi-archive me-1"></i> Deleted ({{ $invoices->total() }})
                        </a>
                    </li>
                </ul>
                <form action="{{ route('admin.invoices.trashed') }}" method="GET" class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm" style="width: 320px;">
                        <input type="text" class="form-control" name="search" placeholder="Search invoices..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    @if(request('search'))
                    <a href="{{ route('admin.invoices.trashed') }}" class="btn btn-sm btn-danger" title="Clear Filters">
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
                            <th>Project</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                        <tr>
                            <td>{{ $loop->iteration + ($invoices->currentPage() - 1) * $invoices->perPage() }}</td>
                            <td><strong>{{ $invoice->invoice_number }}</strong></td>
                            <td>{{ formatDate($invoice->date) }}</td>
                            <td><span class="name-truncate" title="{{ $invoice->customer->name ?? '-' }}">{{ $invoice->customer->name ?? '-' }}</span></td>
                            <td>
                                @if($invoice->project)
                                    {{ $invoice->project->project_number }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ formatMoney($invoice->grand_total) }}</td>
                            <td><span class="badge bg-{{ $invoice->status_color }}">{{ ucfirst($invoice->status) }}</span></td>
                            <td>
                                <div class="btn-group-actions">
                                    <form action="{{ route('admin.invoices.restore', $invoice->id) }}" method="POST" class="d-inline">
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
                                <i class="bi bi-receipt"></i>
                                No deleted invoices found.
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
