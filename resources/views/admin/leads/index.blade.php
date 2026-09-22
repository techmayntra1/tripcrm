@extends('layouts.app')
@section('title', 'Leads')
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
            @if(auth()->user()->hasPermission('leads', 'create'))
            <a href="{{ route('admin.leads.create') }}" class="btn btn-primary" title="Add New Lead">
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
                        <a class="nav-link active" href="{{ route('admin.leads.index') }}">
                            <i class="bi bi-person-lines-fill me-1"></i> Active ({{ $leads->total() }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.leads.trashed') }}">
                            <i class="bi bi-archive me-1"></i> Deleted ({{ $trashedCount }})
                        </a>
                    </li>
                </ul>
                <form method="GET" action="{{ route('admin.leads.index') }}" class="d-flex align-items-center gap-2">
                    <select name="status" class="form-select form-select-sm" style="width: 130px;" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        @foreach($leadStatuses as $status)
                            <option value="{{ strtolower($status->name) }}" {{ request('status') == strtolower($status->name) ? 'selected' : '' }}>{{ $status->name }}</option>
                        @endforeach
                    </select>
                    <div class="input-group input-group-sm" style="width: 320px;">
                        <input type="text" name="search" class="form-control" placeholder="Search leads" value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    @if(request('search') || request('status'))
                    <a href="{{ route('admin.leads.index') }}" class="btn btn-sm btn-danger" title="Clear Filters">
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
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Lead Source</th>
                                <th>Final Budget</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leads as $index => $lead)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{ route('admin.leads.show', $lead) }}"><strong class="name-truncate" title="{{ $lead->name }}">{{ $lead->name }}</strong></a>
                                    <br><small class="text-muted">{{ $lead->created_at->format('d-m-Y') }}</small>
                                </td>
                                <td>{{ $lead->mobile ? $lead->full_mobile : '-' }}</td>
                                <td>
                                    @if($lead->work_lead)
                                        <span class="badge bg-info">{{ $lead->work_lead }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($lead->final_budget)
                                        <strong class="text-success">{{ formatMoney($lead->final_budget) }}</strong>
                                    @else
                                        -
                                    @endif
                                    @if($lead->lost_to)
                                        <br><small class="text-danger lost-to-text" title="{{ $lead->lost_to }}">Lost to: {{ Str::limit($lead->lost_to, 15) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if(in_array($lead->status, ['won', 'lost']))
                                        <span class="badge bg-{{ $lead->status == 'won' ? 'success' : 'danger' }} px-3 py-2">
                                            <i class="bi bi-{{ $lead->status == 'won' ? 'trophy' : 'x-circle' }} me-1"></i>
                                            {{ ucfirst($lead->status) }}
                                        </span>
                                    @else
                                        <div class="d-flex align-items-center gap-1">
                                            @if(auth()->user()->hasPermission('leads', 'edit'))
                                            <form action="{{ route('admin.leads.update', $lead) }}" method="POST" class="status-form" id="statusForm{{ $lead->id }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="name" value="{{ $lead->name }}">
                                                <input type="hidden" name="mobile" value="{{ $lead->mobile }}">
                                                @php
                                                    $currentStatusOrder = $leadStatuses->firstWhere(fn($s) => strtolower($s->name) === $lead->status)?->sort_order ?? 0;
                                                    $isAdmin = auth()->user()->isAdmin();
                                                @endphp
                                                <select name="status" class="status-select status-{{ $lead->status }}" onchange="this.form.submit()">
                                                    @foreach($leadStatuses as $status)
                                                        @if(!in_array(strtolower($status->name), ['won', 'lost']))
                                                            @if($isAdmin || $status->sort_order >= $currentStatusOrder)
                                                                <option value="{{ strtolower($status->name) }}" {{ $lead->status == strtolower($status->name) ? 'selected' : '' }}>{{ $status->name }}</option>
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#wonModal{{ $lead->id }}" title="Mark Won">
                                                <i class="bi bi-trophy"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#lostModal{{ $lead->id }}" title="Mark Lost">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                            @else
                                            <span class="badge bg-secondary status-{{ $lead->status }}">{{ ucfirst($lead->status) }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if(in_array($lead->status, ['won', 'lost']))
                                        <div class="btn-group-actions">
                                            <a href="{{ route('admin.leads.show', $lead) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.leads.follow-ups', $lead) }}" class="btn btn-sm btn-outline-warning" title="Follow Ups">
                                                <i class="bi bi-clock-history"></i>
                                            </a>
                                            @if(auth()->user()->hasPermission('leads', 'delete'))
                                            <form action="{{ route('admin.leads.destroy', $lead) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this lead?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="bi bi-archive"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    @else
                                        <div class="btn-group-actions">
                                            @if(auth()->user()->hasPermission('leads', 'edit'))
                                            <a href="{{ route('admin.leads.edit', $lead) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @endif
                                            <a href="{{ route('admin.leads.show', $lead) }}" class="btn btn-sm btn-outline-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.leads.follow-ups', $lead) }}" class="btn btn-sm btn-outline-warning" title="Follow Ups">
                                                <i class="bi bi-clock-history"></i>
                                            </a>
                                            @if(auth()->user()->hasPermission('leads', 'create'))
                                            <a href="{{ route('admin.leads.convert', $lead) }}" class="btn btn-sm btn-outline-success" title="Convert">
                                                <i class="bi bi-person-check"></i>
                                            </a>
                                            @endif
                                            @if(auth()->user()->hasPermission('leads', 'delete'))
                                            <form action="{{ route('admin.leads.destroy', $lead) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this lead?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="bi bi-archive"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <i class="bi bi-person-x"></i>
                                    No active leads found.
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
    .status-select {
        font-size: 0.8rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        padding: 0.35rem 2rem 0.35rem 0.75rem;
        border-radius: 4px;
        width: 110px;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.5rem center;
        background-size: 12px;
    }
    .status-select.status-new {
        background-color: #6c757d !important;
        color: #fff !important;
    }
    .status-select.status-contacted {
        background-color: #17a2b8 !important;
        color: #fff !important;
    }
    .status-select.status-qualified {
        background-color: #ffc107 !important;
        color: #000 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23000000' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    }
    .status-select.status-lost {
        background-color: #dc3545 !important;
        color: #fff !important;
    }
    .status-select.status-negotiation {
        background-color: #0d6efd !important;
        color: #fff !important;
    }
    .status-select.status-won {
        background-color: #198754 !important;
        color: #fff !important;
    }
    .status-select option {
        background-color: #fff;
        color: #333;
    }
</style>
@endpush

@push('modals')
@foreach($leads as $lead)

<div class="modal fade" id="wonModal{{ $lead->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-trophy me-2"></i>Mark as Won</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.leads.mark-won', $lead) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Lead</label>
                        <div class="p-2 bg-light rounded">
                            <strong>{{ $lead->name }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Final Budget <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="final_budget" value="" step="1" min="0" required>
                        <small class="text-muted">Enter the final agreed budget amount</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> Mark as Won</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="lostModal{{ $lead->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Mark as Lost</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.leads.mark-lost', $lead) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Lead</label>
                        <div class="p-2 bg-light rounded">
                            <strong>{{ $lead->name }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lost To <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="lost_to" placeholder="Lost to" maxlength="30" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Additional details..." maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-x-lg me-1"></i> Mark as Lost</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endpush
