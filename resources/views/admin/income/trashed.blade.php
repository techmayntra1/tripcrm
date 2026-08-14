@extends('layouts.app')
@section('title', 'Deleted Income')
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
            <a href="{{ route('admin.income.create') }}" class="btn btn-success" title="Add Income">
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
                <a class="nav-link" href="{{ route('admin.income.index') }}">
                    <i class="bi bi-graph-up-arrow me-1"></i> Active ({{ $activeCount }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.income.trashed') }}">
                    <i class="bi bi-archive me-1"></i> Deleted ({{ $incomes->total() }})
                </a>
            </li>
        </ul>
        <form method="GET" action="{{ route('admin.income.trashed') }}" class="d-flex align-items-center gap-2">
            <div class="input-group input-group-sm" style="width: 320px;">
                <input type="text" name="search" class="form-control" placeholder="Search income" value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            @if(request('search'))
                <a href="{{ route('admin.income.trashed') }}" class="btn btn-sm btn-danger" title="Clear Filters">
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
                <th width="120">Receipt No.</th>
                <th width="90" class="text-center">Type</th>
                <th>Trip/Customer</th>
                <th width="100" class="text-end">Amount</th>
                <th width="130">Deleted At</th>
                <th width="80" class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incomes as $income)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ formatDate($income->income_date) }}</td>
                <td><strong>{{ $income->receipt_number }}</strong></td>
                <td class="text-center">
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
                        {{ $income->trip->name }}
                        @if($income->customer)
                            - <span class="name-truncate" title="{{ $income->customer->name }}">{{ $income->customer->name }}</span>
                        @endif
                    @elseif($income->customer)
                        <span class="name-truncate" title="{{ $income->customer->name }}">{{ $income->customer->name }}</span>
                    @else
                        -
                    @endif
                </td>
                <td class="text-end text-success"><strong>{{ formatMoney($income->amount) }}</strong></td>
                <td>{{ $income->deleted_at->format('d-m-Y H:i') }}</td>
                <td class="text-center">
                    <form action="{{ route('admin.income.restore', $income->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Restore this income?')">
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
                    No deleted income records found.
                </td>
            </tr>
            @endforelse
            </tbody>
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
</style>
@endpush
