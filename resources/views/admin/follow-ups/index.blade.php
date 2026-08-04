@extends('layouts.app')
@section('title', 'Follow-ups')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-clock-history icon-gradient bg-sunny-morning"></i>
            </div>
            <div>
                Follow-ups
            </div>
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
    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div class="row">
    <div class="col-12">
        <div class="main-card mb-3 card">
            <div class="card-header has-tabs d-flex justify-content-between align-items-center">
                <ul class="nav lead-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ !request('tab') ? 'active' : '' }}" href="{{ route('admin.follow-ups.index') }}">
                            <i class="bi bi-list-ul me-1"></i> All ({{ $allCount }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') == 'today' ? 'active' : '' }}" href="{{ route('admin.follow-ups.index', ['tab' => 'today']) }}">
                            <i class="bi bi-calendar-check me-1"></i> Today ({{ $todayCount }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') == 'upcoming' ? 'active' : '' }}" href="{{ route('admin.follow-ups.index', ['tab' => 'upcoming']) }}">
                            <i class="bi bi-calendar-plus me-1"></i> Upcoming ({{ $upcomingCount }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') == 'past' ? 'active' : '' }}" href="{{ route('admin.follow-ups.index', ['tab' => 'past']) }}">
                            <i class="bi bi-calendar-x me-1"></i> Past ({{ $pastCount }})
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <form method="GET" action="{{ route('admin.follow-ups.index') }}" id="sourceFilterForm">
                        @if(request('tab'))
                        <input type="hidden" name="tab" value="{{ request('tab') }}">
                        @endif
                        @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        <select name="source" class="form-select form-select-sm" style="width: auto;" onchange="document.getElementById('sourceFilterForm').submit()">
                            <option value="" {{ !request('source') ? 'selected' : '' }}>All Sources</option>
                            <option value="leads" {{ request('source') == 'leads' ? 'selected' : '' }}>Leads Only</option>
                            <option value="customers" {{ request('source') == 'customers' ? 'selected' : '' }}>Customers Only</option>
                        </select>
                    </form>
                    <form method="GET" action="{{ route('admin.follow-ups.index') }}" class="d-flex align-items-center gap-2">
                        @if(request('tab'))
                        <input type="hidden" name="tab" value="{{ request('tab') }}">
                        @endif
                        @if(request('source'))
                        <input type="hidden" name="source" value="{{ request('source') }}">
                        @endif
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" name="search" class="form-control" placeholder="Search follow-ups" value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                        @if(request('search'))
                        <a href="{{ route('admin.follow-ups.index', array_filter(['tab' => request('tab'), 'source' => request('source')])) }}" class="btn btn-sm btn-danger" title="Clear">
                            <i class="bi bi-x-lg"></i>
                        </a>
                        @endif
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="white-space: nowrap">SR</th>
                            <th>Source</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Notes</th>
                            <th style="white-space: nowrap">Follow-up Date</th>
                            <th>Created By</th>
                            <th style="white-space: nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($followUps as $followUp)
                        @php
                            $isPast = $followUp['follow_up_date']->isPast();
                            $isToday = $followUp['follow_up_date']->isToday();
                        @endphp
                        <tr class="{{ $isPast && !$isToday ? 'table-warning' : '' }}">
                            <td>{{ $loop->iteration + (($followUps->currentPage() - 1) * $followUps->perPage()) }}</td>
                            <td>
                                @if($followUp['type'] === 'lead')
                                    <span class="badge bg-info">Lead</span>
                                @else
                                    <span class="badge bg-success">Customer</span>
                                @endif
                            </td>
                            <td>
                                @if($followUp['type'] === 'lead')
                                    <a href="{{ route('admin.leads.show', $followUp['entity_id']) }}" class="name-truncate" title="{{ $followUp['entity_name'] }}">
                                        <strong>{{ $followUp['entity_name'] }}</strong>
                                    </a>
                                @else
                                    <a href="{{ route('admin.customers.show', $followUp['entity_id']) }}" class="name-truncate" title="{{ $followUp['entity_name'] }}">
                                        <strong>{{ $followUp['entity_name'] }}</strong>
                                    </a>
                                @endif
                                @if($followUp['entity_phone'])
                                <br><small class="text-muted">{{ $followUp['entity_phone'] }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $followUp['update_type_color'] }}">{{ $followUp['update_type'] }}</span>
                            </td>
                            <td>
                                <span class="name-truncate" title="{{ $followUp['notes'] }}">{{ Str::limit($followUp['notes'], 50) }}</span>
                            </td>
                            <td>
                                @if($isToday)
                                    <strong class="text-primary">{{ $followUp['follow_up_date']->format('d-m-Y') }}</strong>
                                    <br><small class="text-primary fw-bold">{{ $followUp['follow_up_date']->format('h:i A') }} (Today)</small>
                                @elseif($isPast)
                                    <strong class="text-danger">{{ $followUp['follow_up_date']->format('d-m-Y') }}</strong>
                                    <br><small class="text-danger">{{ $followUp['follow_up_date']->format('h:i A') }} (Overdue)</small>
                                @else
                                    <strong>{{ $followUp['follow_up_date']->format('d-m-Y') }}</strong>
                                    <br><small class="text-muted">{{ $followUp['follow_up_date']->format('h:i A') }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="name-truncate">{{ $followUp['created_by'] }}</span>
                            </td>
                            <td>
                                <div class="gap-1">
                                    @if($followUp['type'] === 'lead')
                                        <a href="{{ route('admin.leads.show', $followUp['entity_id']) }}" class="btn btn-sm btn-outline-info" title="View Lead">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('admin.customers.show', $followUp['entity_id']) }}" class="btn btn-sm btn-outline-info" title="View Customer">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-clock-history fs-3 d-block mb-2"></i>
                                No follow-ups found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
            <div class="card-footer">
                @include('partials.pagination', ['paginator' => $followUps])
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card-header .form-control {
        background-color: #fff !important;
        color: #333 !important;
    }
</style>
@endpush
