@extends('layouts.app')
@section('title', 'Deleted Expenses')
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
            <a href="{{ route('admin.expenses.create') }}" class="btn btn-danger" title="Add Expense">
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
<div class="main-card mb-3 card">
    <div class="card-header has-tabs d-flex justify-content-between align-items-center">
        <ul class="nav lead-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.expenses.index') }}">
                    <i class="bi bi-cash-coin me-1"></i> Active ({{ $activeCount }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.expenses.trashed') }}">
                    <i class="bi bi-archive me-1"></i> Deleted ({{ $expenses->total() }})
                </a>
            </li>
        </ul>
        <form method="GET" action="{{ route('admin.expenses.trashed') }}" class="d-flex align-items-center gap-2">
            <div class="input-group input-group-sm" style="width: 320px;">
                <input type="text" name="search" class="form-control" placeholder="Search expenses" value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            @if(request('search'))
                <a href="{{ route('admin.expenses.trashed') }}" class="btn btn-sm btn-danger" title="Clear Filters">
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
                    <th width="90">Date</th>
                    <th width="120">Expense #</th>
                    <th width="100" class="text-center">Type</th>
                    <th>Expense type</th>
                    <th width="100" class="text-end">Amount</th>
                    <th width="130">Deleted At</th>
                    <th width="80" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ formatDate($expense->expense_date) }}</td>
                    <td><strong>{{ $expense->expense_number }}</strong></td>
                    <td class="text-center">
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
                            <span class="small text-muted d-block name-truncate" title="{{ $expense->vendor->name }}">{{ $expense->vendor->name }}</span>
                        @elseif($expense->expense_type === 'trip' && $expense->trip)
                            <span class="small text-muted d-block name-truncate" title="{{ $expense->trip->name }}">{{ $expense->trip->name }}</span>
                        @elseif($expense->expense_type === 'salary' && $expense->staff)
                            <span class="small text-muted d-block name-truncate" title="{{ $expense->staff->name }}">{{ $expense->staff->name }}</span>
                        @elseif($expense->vendor)
                            <span class="small text-muted d-block name-truncate" title="{{ $expense->vendor->name }}">{{ $expense->vendor->name }}</span>
                        @elseif($expense->trip)
                            <span class="small text-muted d-block name-truncate" title="{{ $expense->trip->name }}">{{ $expense->trip->name }}</span>
                        @elseif($expense->staff)
                            <span class="small text-muted d-block name-truncate" title="{{ $expense->staff->name }}">{{ $expense->staff->name }}</span>
                        @endif
                    </td>
                    <td class="text-end text-danger"><strong>{{ formatMoney($expense->grand_total, 0, $expense) }}</strong></td>
                    <td>{{ $expense->deleted_at->format('d-m-Y H:i') }}</td>
                    <td class="text-center">
                        <form action="{{ route('admin.expenses.restore', $expense->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Restore this expense?')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="bi bi-archive fs-1 d-block mb-2"></i>
                        No deleted expenses found.
                    </td>
                </tr>
                @endforelse
            </tbody>
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
</style>
@endpush
