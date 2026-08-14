@extends('layouts.app')
@section('title', 'Income')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-graph-up-arrow icon-gradient bg-success"></i>
            </div>
            <div>
                Income Management
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.income.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="btn btn-success" title="Export to Excel">
                <i class="bi bi-file-earmark-excel me-1"></i> Export
            </a>
            @if(auth()->user()->hasPermission('income', 'create'))
            <a href="{{ route('admin.income.create') }}" class="btn btn-success" title="Add Income">
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
@php $totalDateFiltered = request('total_from') || request('total_to'); @endphp
<div class="card summary-bar mb-3">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="summary-icon summary-icon--success"><i class="bi bi-cash-stack"></i></span>
            <div>
                <div class="summary-label">
                    Total Income
                    @if($totalDateFiltered)<span class="badge bg-success-subtle text-success ms-1">Date Filtered</span>
                    @elseif(request()->hasAny(['search', 'income_type', 'payment_mode_id']))<span class="badge bg-secondary-subtle text-secondary ms-1">Filtered</span>@endif
                </div>
                <div class="summary-value text-success">{{ formatMoney($totalAmount) }}</div>
                <div class="summary-sub">{{ $totalCount }} record(s)</div>
            </div>
        </div>
        <form method="GET" action="{{ route('admin.income.index') }}" class="summary-filter d-flex align-items-end gap-2 flex-wrap">
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
            <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-funnel me-1"></i>Apply</button>
            @if($totalDateFiltered)
                <a href="{{ route('admin.income.index', request()->except(['total_from', 'total_to', 'page'])) }}" class="btn btn-sm btn-outline-secondary" title="Clear date filter"><i class="bi bi-x-lg"></i></a>
            @endif
        </form>
    </div>
</div>
<div class="main-card mb-3 card">
    <div class="card-header has-tabs d-flex justify-content-between align-items-center">
        <ul class="nav lead-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.income.index') }}">
                    <i class="bi bi-graph-up-arrow me-1"></i> Active ({{ $incomes->total() }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.income.trashed') }}">
                    <i class="bi bi-archive me-1"></i> Deleted ({{ $trashedCount }})
                </a>
            </li>
        </ul>
        <form method="GET" action="{{ route('admin.income.index') }}" class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm" style="width: 130px;" name="income_type" onchange="this.form.submit()">
                <option value="">All Types</option>
                <option value="trip" {{ request('income_type') == 'trip' ? 'selected' : '' }}>Trip</option>
                <option value="advance" {{ request('income_type') == 'advance' ? 'selected' : '' }}>Advance</option>
                <option value="other" {{ request('income_type') == 'other' ? 'selected' : '' }}>Other</option>
            </select>
            <select class="form-select form-select-sm" style="width: 130px;" name="payment_mode_id" onchange="this.form.submit()">
                <option value="">All Modes</option>
                @foreach($paymentModes as $mode)
                <option value="{{ $mode->id }}" {{ request('payment_mode_id') == $mode->id ? 'selected' : '' }}>{{ $mode->name }}</option>
                @endforeach
            </select>
            <div class="input-group input-group-sm" style="width: 320px;">
                <input type="text" name="search" class="form-control" placeholder="Search income" value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            @if(request()->hasAny(['search', 'income_type', 'payment_mode_id']))
                <a href="{{ route('admin.income.index') }}" class="btn btn-sm btn-danger" title="Clear Filters">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </form>
    </div>
    <div class="card-body">
        <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th width="50" class="text-center">SR</th>
                <th>Receipt No.</th>
                <th>Date</th>
                <th>Type</th>
                <th>Trip/Customer</th>
                <th>Payment</th>
                <th>Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incomes as $income)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td><a href="{{ route('admin.income.show', $income) }}"><strong>{{ $income->receipt_number }}</strong></a></td>
                <td>{{ formatDate($income->income_date) }}</td>
                <td>
                    @php
                        $typeColors = [
                            'trip' => 'bg-primary',
                            'advance' => 'bg-info',
                            'other' => 'bg-secondary',
                        ];
                    @endphp
                    <span class="badge {{ $typeColors[$income->income_type] ?? 'bg-secondary' }}">{{ ucfirst($income->income_type) }}</span>
                </td>
                <td>
                    @if($income->trip)
                        <a href="{{ route('admin.trips.show', $income->trip) }}" title="{{ $income->trip->name }}">{{ $income->trip->name }}</a>
                        @if($income->customer)
                            - <span class="name-truncate" title="{{ $income->customer->name }}">{{ $income->customer->name }}</span>
                        @endif
                    @elseif($income->customer)
                        <span class="name-truncate" title="{{ $income->customer->name }}">{{ $income->customer->name }}</span>
                    @else
                        -
                    @endif
                </td>
                <td>
                    @php
                        $modeColors = [
                            'cash' => 'bg-success',
                            'bank_transfer' => 'bg-primary',
                            'upi' => 'bg-success',
                            'cheque' => 'bg-warning',
                            'card' => 'bg-info',
                        ];
                        $slug = optional($income->paymentMode)->slug ?? '';
                    @endphp
                    <span class="badge {{ $modeColors[$slug] ?? 'bg-secondary' }}">{{ $income->paymentMode->name ?? '-' }}</span>
                </td>
                <td class="text-success"><strong>{{ formatMoney($income->amount) }}</strong></td>
                <td>
                    <div class="d-flex gap-1 justify-content-center">
                        <a href="{{ route('admin.income.edit', $income) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="{{ route('admin.income.show', $income) }}" class="btn btn-sm btn-outline-info" title="View">
                            <i class="bi bi-eye"></i>
                        </a>
                        <form action="{{ route('admin.income.destroy', $income) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this income?')">
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
                <td colspan="8" class="text-center py-4 text-muted">
                    <i class="bi bi-graph-up-arrow fs-1 d-block mb-2"></i>
                    No income records found.
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($incomes->count() > 0)
        <tfoot>
            <tr>
                <td colspan="6" class="text-end"><strong>Page Total</strong></td>
                <td><strong>{{ formatMoney($incomes->sum('amount')) }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>
    </div>
    <div class="card-footer">
        @include('partials.pagination', ['paginator' => $incomes])
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
