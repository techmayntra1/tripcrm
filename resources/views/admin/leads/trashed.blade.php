@extends('layouts.app')
@section('title', 'Deleted Leads')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-person-lines-fill icon-gradient bg-mean-fruit"></i>
            </div>
            <div>
                Leads
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.leads.create') }}" class="btn btn-primary" title="Add New Lead">
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
                        <a class="nav-link" href="{{ route('admin.leads.index') }}">
                            <i class="bi bi-person-lines-fill me-1"></i> Active ({{ $activeCount }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('admin.leads.trashed') }}">
                            <i class="bi bi-archive me-1"></i> Deleted ({{ $leads->total() }})
                        </a>
                    </li>
                </ul>
                <form method="GET" action="{{ route('admin.leads.trashed') }}" class="d-flex align-items-center gap-2">
                    <select name="status" class="form-select form-select-sm" style="width: 130px;" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        @foreach($leadStatuses as $status)
                            <option value="{{ strtolower($status->name) }}" {{ request('status') == strtolower($status->name) ? 'selected' : '' }}>{{ $status->name }}</option>
                        @endforeach
                    </select>
                    <div class="input-group input-group-sm" style="width: 320px;">
                        <input type="text" name="search" class="form-control" placeholder="Search name, mobile, email..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    @if(request('search') || request('status'))
                    <a href="{{ route('admin.leads.trashed') }}" class="btn btn-sm btn-danger" title="Clear Filters">
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
                                <th>Mobile</th>
                                <th>Lead Source</th>
                                <th>Final Budget</th>
                                <th>City</th>
                                <th width="100">Status</th>
                                <th>Deleted</th>
                                <th width="140">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leads as $index => $lead)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.leads.trashed.show', $lead->id) }}"><strong class="text-muted">{{ $lead->name }}</strong></a>
                                    <br><small class="text-muted">{{ $lead->created_at->format('d-m-Y') }}</small>
                                </td>
                                <td>{{ $lead->mobile ? $lead->full_mobile : '-' }}</td>
                                <td>
                                    @if($lead->work_lead)
                                        <span class="badge bg-secondary">{{ $lead->work_lead }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $lead->final_budget ? formatMoney($lead->final_budget) : '-' }}</td>
                                <td>{{ $lead->city ?? '-' }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'new' => 'bg-secondary',
                                            'contacted' => 'bg-info',
                                            'qualified' => 'bg-warning',
                                            'lost' => 'bg-danger',
                                        ];
                                    @endphp
                                    <span class="badge {{ $statusColors[$lead->status] ?? 'bg-secondary' }}">{{ ucfirst($lead->status) }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $lead->deleted_at->format('d-m-Y') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.leads.trashed.show', $lead->id) }}" class="btn btn-sm btn-outline-info" title="Details">
                                            <i class="bi bi-info-circle"></i>
                                        </a>
                                        <form action="{{ route('admin.leads.restore', $lead->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Restore this lead?')">
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
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi bi-archive fs-3 d-block mb-2"></i>
                                    No deleted leads.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
            </div>
            <div class="card-footer">
                @include('partials.pagination', ['paginator' => $leads])
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
