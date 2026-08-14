@extends('layouts.app')
@section('title', 'Expenses')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-cash-coin icon-gradient bg-danger"></i>
            </div>
            <div>
                Expenses
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.expenses.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="btn btn-success" title="Export to Excel">
                <i class="bi bi-file-earmark-excel me-1"></i> Export
            </a>
            @if(auth()->user()->hasPermission('expenses', 'create'))
            <a href="{{ route('admin.expenses.create') }}" class="btn btn-danger" title="Add Expense">
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
@php
    $expenseFiltered = request()->hasAny(['search', 'expense_type', 'payment_status']);
    $totalDateFiltered = request('total_from') || request('total_to');
@endphp
<div class="card summary-bar mb-3">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center flex-wrap gap-4">
            <div class="d-flex align-items-center gap-3">
                <span class="summary-icon summary-icon--danger"><i class="bi bi-cash-coin"></i></span>
                <div>
                    <div class="summary-label">
                        Total Expense
                        @if($totalDateFiltered)<span class="badge bg-danger-subtle text-danger ms-1">Date Filtered</span>
                        @elseif($expenseFiltered)<span class="badge bg-secondary-subtle text-secondary ms-1">Filtered</span>@endif
                    </div>
                    <div class="summary-value text-danger">{{ formatMoney($totalGrandTotal) }}</div>
                    <div class="summary-sub">{{ $totalCount }} record(s)</div>
                </div>
            </div>
            <div class="summary-divider"></div>
            <div class="d-flex align-items-center gap-3">
                <span class="summary-icon summary-icon--success"><i class="bi bi-check2-circle"></i></span>
                <div>
                    <div class="summary-label">Total Paid</div>
                    <div class="summary-value text-success">{{ formatMoney($totalPaid) }}</div>
                    <div class="summary-sub">Balance: {{ formatMoney($totalGrandTotal - $totalPaid) }}</div>
                </div>
            </div>
        </div>
        <form method="GET" action="{{ route('admin.expenses.index') }}" class="summary-filter d-flex align-items-end gap-2 flex-wrap">
            @foreach(request()->except(['total_from', 'total_to', 'page']) as $key => $val)
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endforeach
            <div>
                <label class="summary-date-label">From</label>
                <input type="date" name="total_from" value="{{ request('total_from') }}" class="form-control form-control-sm">
            </div>
            <div>
                <label class="summary-date-label">To</label>
                <input type="date" name="total_to" value="{{ request('total_to') }}" class="form-control form-control-sm">
            </div>
            <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-funnel me-1"></i>Apply</button>
            @if($totalDateFiltered)
                <a href="{{ route('admin.expenses.index', request()->except(['total_from', 'total_to', 'page'])) }}" class="btn btn-sm btn-outline-secondary" title="Clear date filter"><i class="bi bi-x-lg"></i></a>
            @endif
        </form>
    </div>
</div>
<div class="main-card mb-3 card">
    <div class="card-header has-tabs d-flex justify-content-between align-items-center">
        <ul class="nav lead-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.expenses.index') }}">
                    <i class="bi bi-cash-coin me-1"></i> Active ({{ $expenses->total() }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.expenses.trashed') }}">
                    <i class="bi bi-archive me-1"></i> Deleted ({{ $trashedCount ?? 0 }})
                </a>
            </li>
        </ul>
        <form method="GET" action="{{ route('admin.expenses.index') }}" class="d-flex align-items-center gap-2">
            <select name="expense_type" class="form-select form-select-sm" style="width: 130px;" onchange="this.form.submit()">
                <option value="">All Types</option>
                <option value="trip" {{ request('expense_type') == 'trip' ? 'selected' : '' }}>Trip</option>
                <option value="vendor" {{ request('expense_type') == 'vendor' ? 'selected' : '' }}>Vendor</option>
                <option value="general" {{ request('expense_type') == 'general' ? 'selected' : '' }}>General</option>
                <option value="salary" {{ request('expense_type') == 'salary' ? 'selected' : '' }}>Salary</option>
            </select>
            <select name="payment_status" class="form-select form-select-sm" style="width: 130px;" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
            </select>
            <div class="input-group input-group-sm" style="width: 320px;">
                <input type="text" name="search" class="form-control" placeholder="Search expenses" value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            @if(request('search') || request('expense_type') || request('payment_status'))
            <a href="{{ route('admin.expenses.index') }}" class="btn btn-sm btn-danger" title="Clear Filters">
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
                <th>Expense #</th>
                <th>Date</th>
                <th>Type</th>
                <th>Expense type</th>
                <th>Amount</th>
                <th>Paid</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td><a href="{{ route('admin.expenses.show', $expense) }}"><strong>{{ $expense->expense_number }}</strong></a></td>
                <td>{{ formatDate($expense->expense_date) }}</td>
                <td>
                    @php
                        $typeColors = [
                            'trip' => 'bg-warning',
                            'vendor' => 'bg-info',
                            'general' => 'bg-secondary',
                            'salary' => 'bg-primary',
                        ];
                    @endphp
                    <span class="badge {{ $typeColors[$expense->expense_type] ?? 'bg-secondary' }}">
                        {{ ucfirst($expense->expense_type) }}
                    </span>
                </td>
                <td>
                    <div>{{ $expense->expense_type_display }}</div>
                    @if($expense->expense_type === 'vendor' && $expense->vendor)
                        <a href="{{ route('admin.vendors.show', $expense->vendor) }}" class="small text-muted d-block"><span class="name-truncate" title="{{ $expense->vendor->name }}">{{ $expense->vendor->name }}</span></a>
                    @elseif($expense->expense_type === 'trip' && $expense->trip)
                        <a href="{{ route('admin.trips.show', $expense->trip) }}" class="small text-muted d-block"><span class="name-truncate" title="{{ $expense->trip->name }}">{{ $expense->trip->name }}</span></a>
                    @elseif($expense->expense_type === 'salary' && $expense->staff)
                        <span class="small text-muted d-block name-truncate" title="{{ $expense->staff->name }}">{{ $expense->staff->name }}</span>
                    @elseif($expense->vendor)
                        <a href="{{ route('admin.vendors.show', $expense->vendor) }}" class="small text-muted d-block"><span class="name-truncate" title="{{ $expense->vendor->name }}">{{ $expense->vendor->name }}</span></a>
                    @elseif($expense->trip)
                        <a href="{{ route('admin.trips.show', $expense->trip) }}" class="small text-muted d-block"><span class="name-truncate" title="{{ $expense->trip->name }}">{{ $expense->trip->name }}</span></a>
                    @elseif($expense->staff)
                        <span class="small text-muted d-block name-truncate" title="{{ $expense->staff->name }}">{{ $expense->staff->name }}</span>
                    @endif
                </td>
                <td class="text-danger"><strong>{{ formatMoney($expense->grand_total) }}</strong></td>
                <td class="text-success">{{ formatMoney($expense->paid_amount) }}</td>
                <td>
                    @if($expense->payment_status == 'paid')
                        <span class="badge bg-success">Paid</span>
                    @elseif($expense->payment_status == 'partial')
                        <span class="badge bg-warning">Partial</span>
                    @else
                        <span class="badge bg-danger">Unpaid</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex gap-1 justify-content-center">
                        @if($expense->payment_status !== 'paid')
                        <a href="{{ route('admin.expenses.edit', $expense) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endif
                        <a href="{{ route('admin.expenses.show', $expense) }}" class="btn btn-sm btn-outline-info" title="View">
                            <i class="bi bi-eye"></i>
                        </a>
                        <form action="{{ route('admin.expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this expense?')">
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
                <td colspan="9" class="text-center py-4 text-muted">
                    <i class="bi bi-cash-coin fs-1 d-block mb-2"></i>
                    No expenses found.
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($expenses->count() > 0)
        <tfoot>
            <tr>
                <td colspan="5" class="text-end"><strong>Page Total</strong></td>
                <td><strong>{{ formatMoney($expenses->sum('grand_total')) }}</strong></td>
                <td><strong>{{ formatMoney($expenses->sum('paid_amount')) }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
        @endif
        </table>
    </div>
    <div class="card-footer">
        @include('partials.pagination', ['paginator' => $expenses])
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
    .summary-bar {
        border: none;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
    }
    .summary-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }
    .summary-icon--success { background: rgba(58, 196, 125, 0.12); color: #3ac47d; }
    .summary-icon--danger { background: rgba(217, 37, 80, 0.12); color: #d92550; }
    .summary-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        font-weight: 600;
    }
    .summary-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.15;
    }
    .summary-sub {
        font-size: 0.78rem;
        color: #adb5bd;
    }
    .summary-date-label {
        font-size: 0.7rem;
        color: #6c757d;
        margin-bottom: 2px;
        display: block;
    }
    .summary-divider {
        width: 1px;
        align-self: stretch;
        min-height: 46px;
        background: #e9ecef;
    }
    .summary-filter .form-control-sm { width: 150px; }
</style>
@endpush
