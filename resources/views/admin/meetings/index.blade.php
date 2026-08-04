@extends('layouts.app')
@section('title', 'Meetings')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-calendar-event icon-gradient bg-warning"></i>
            </div>
            <div>
                Meetings
            </div>
        </div>
        <div class="page-title-actions">
            @if(auth()->user()->hasPermission('meetings', 'create'))
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addMeetingModal" title="Add Meeting">
                <i class="bi bi-plus-lg"></i>
            </button>
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
    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>
    @foreach($errors->all() as $error)
        {{ $error }}<br>
    @endforeach
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div class="row">
    <div class="col-12">
        <div class="main-card mb-3 card">
            <div class="card-header has-tabs d-flex justify-content-between align-items-center">
                <ul class="nav lead-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') != 'deleted' ? 'active' : '' }}" href="{{ route('admin.meetings.index') }}">
                            <i class="bi bi-calendar-event me-1"></i> Active ({{ $allCount }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') == 'deleted' ? 'active' : '' }}" href="{{ route('admin.meetings.index', ['tab' => 'deleted']) }}">
                            <i class="bi bi-archive me-1"></i> Deleted ({{ $deletedCount }})
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    @if(request('tab') != 'deleted')
                    <form method="GET" action="{{ route('admin.meetings.index') }}" id="filterForm">
                        @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        <select name="tab" class="form-select form-select-sm" style="width: auto;" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ !request('tab') ? 'selected' : '' }}>All</option>
                            <option value="today" {{ request('tab') == 'today' ? 'selected' : '' }}>Today ({{ $todayCount ?? 0 }})</option>
                            <option value="upcoming" {{ request('tab') == 'upcoming' ? 'selected' : '' }}>Upcoming ({{ $upcomingCount ?? 0 }})</option>
                            <option value="past" {{ request('tab') == 'past' ? 'selected' : '' }}>Past ({{ $pastCount ?? 0 }})</option>
                            <option value="completed" {{ request('tab') == 'completed' ? 'selected' : '' }}>Completed ({{ $completedCount }})</option>
                            <option value="cancelled" {{ request('tab') == 'cancelled' ? 'selected' : '' }}>Cancelled ({{ $cancelledCount ?? 0 }})</option>
                        </select>
                    </form>
                    @endif
                    <form method="GET" action="{{ route('admin.meetings.index') }}" class="d-flex align-items-center gap-2">
                        @if(request('tab'))
                        <input type="hidden" name="tab" value="{{ request('tab') }}">
                        @endif
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" name="search" class="form-control" placeholder="Search meetings" value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                        @if(request('search'))
                        <a href="{{ route('admin.meetings.index', request('tab') ? ['tab' => request('tab')] : []) }}" class="btn btn-sm btn-danger" title="Clear">
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
                            <th>Title</th>
                            <th>Lead/Customer</th>
                            <th style="white-space: nowrap">Date & Time</th>
                            <th style="white-space: nowrap">Purpose</th>
                            <th>Location</th>
                            <th style="white-space: nowrap">Status</th>
                            <th style="white-space: nowrap">Actions</th>
                        </tr>
                    </thead>
                        <tbody>
                            @forelse($meetings as $meeting)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if($meeting->trashed())
                                    <a href="{{ route('admin.meetings.trashed.show', $meeting->id) }}" class="name-truncate" title="{{ $meeting->title }}"><strong>{{ $meeting->title }}</strong></a>
                                    @else
                                    <a href="{{ route('admin.meetings.show', $meeting) }}" class="name-truncate" title="{{ $meeting->title }}"><strong>{{ $meeting->title }}</strong></a>
                                    @endif
                                </td>
                                <td>
                                    @if($meeting->lead)
                                        <a href="{{ route('admin.leads.show', $meeting->lead) }}" class="name-truncate">{{ $meeting->lead->name }}</a>
                                        <br><small class="text-muted">Lead</small>
                                    @elseif($meeting->customer)
                                        <a href="{{ route('admin.customers.show', $meeting->customer) }}" class="name-truncate">{{ $meeting->customer->name }}</a>
                                        <br><small class="text-muted">Customer</small>
                                    @elseif($meeting->vendor)
                                        <a href="{{ route('admin.vendors.edit', $meeting->vendor) }}" class="name-truncate">{{ $meeting->vendor->name }}</a>
                                        <br><small class="text-muted">Vendor</small>
                                    @elseif($meeting->other_attendee)
                                        <span class="name-truncate">{{ $meeting->other_attendee }}</span>
                                        <br><small class="text-muted">Other</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $meeting->meeting_at->isToday() ? 'Today' : $meeting->meeting_at->format('d-m-Y') }}</strong>
                                    <br><small class="text-muted">{{ $meeting->meeting_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    @if($meeting->purpose)
                                        <span class="badge bg-info">{{ $meeting->purpose->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><span class="text-truncate d-inline-block" style="max-width: 120px;" title="{{ $meeting->location }}">{{ $meeting->location ?? '-' }}</span></td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'scheduled' => 'bg-primary',
                                            'completed' => 'bg-success',
                                            'cancelled' => 'bg-danger',
                                            'rescheduled' => 'bg-warning',
                                        ];
                                    @endphp
                                    <span class="badge {{ $statusColors[$meeting->status] ?? 'bg-secondary' }}">
                                        {{ ucfirst($meeting->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="gap-1">
                                        @if($meeting->trashed())
                                            @if(auth()->user()->hasPermission('meetings', 'edit'))
                                            <form action="{{ route('admin.meetings.reactivate', $meeting->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            </form>
                                            @endif
                                        @elseif($meeting->status === 'completed')
                                            @if(auth()->user()->hasPermission('meetings', 'edit'))
                                            <form action="{{ route('admin.meetings.deactivate', $meeting) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this meeting?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="bi bi-archive"></i>
                                                </button>
                                            </form>
                                            @endif
                                        @else
                                            <a href="{{ route('admin.meetings.show', $meeting) }}" class="btn btn-sm btn-outline-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if(auth()->user()->hasPermission('meetings', 'edit'))
                                            <button type="button" class="btn btn-sm btn-outline-primary" title="Edit" data-bs-toggle="modal" data-bs-target="#editMeetingModal{{ $meeting->id }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" title="Complete" data-bs-toggle="modal" data-bs-target="#completeMeetingModal{{ $meeting->id }}">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning" title="Reschedule" data-bs-toggle="modal" data-bs-target="#rescheduleMeetingModal{{ $meeting->id }}">
                                                <i class="bi bi-calendar-plus"></i>
                                            </button>
                                            <form action="{{ route('admin.meetings.deactivate', $meeting) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this meeting?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="bi bi-archive"></i>
                                                </button>
                                            </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>
                                    No meetings found.
                                </td>
                            </tr>
                            @endforelse
                    </tbody>
                </table>
                </div>
            </div>
            <div class="card-footer">
                @include('partials.pagination', ['paginator' => $meetings])
            </div>
        </div>
    </div>
</div>
@endsection

@push('modals')
@if(auth()->user()->hasPermission('meetings', 'create'))

<div class="modal fade" id="addMeetingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>Add Meeting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.meetings.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="bi bi-exclamation-triangle me-2"></i>Error:</strong>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Meeting Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" id="addMeetingTitle" value="{{ old('title') }}" maxlength="40">
                        <div class="invalid-feedback">Please enter meeting title</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="meeting_at" id="addMeetingDatetime" value="{{ old('meeting_at', date('Y-m-d') . 'T10:00') }}">
                            <div class="invalid-feedback">Please select date and time</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Purpose</label>
                            <select class="form-select" name="purpose_id">
                                <option value="">Select Purpose</option>
                                @foreach($meetingPurposes as $purpose)
                                    <option value="{{ $purpose->id }}" {{ old('purpose_id') == $purpose->id ? 'selected' : '' }}>{{ $purpose->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Meeting With <span class="text-danger">*</span></label>
                            <select class="form-select" name="meeting_with" id="addMeetingWith">
                                <option value="">Select</option>
                                <option value="lead" {{ old('meeting_with') == 'lead' ? 'selected' : '' }}>Lead</option>
                                <option value="customer" {{ old('meeting_with') == 'customer' ? 'selected' : '' }}>Customer</option>
                                <option value="vendor" {{ old('meeting_with') == 'vendor' ? 'selected' : '' }}>Vendor</option>
                                <option value="other" {{ old('meeting_with') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            <div class="invalid-feedback">Please select meeting with</div>
                        </div>
                        <div class="col-md-8 mb-3" id="addLeadSection" style="{{ old('meeting_with') == 'lead' ? '' : 'display: none;' }}">
                            <label class="form-label">Select Lead <span class="text-danger">*</span></label>
                            <select class="form-select" name="lead_id" id="addLeadId">
                                <option value="">Select Lead</option>
                                @foreach($leads as $lead)
                                    <option value="{{ $lead->id }}" {{ old('lead_id') == $lead->id ? 'selected' : '' }}>{{ $lead->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a lead</div>
                        </div>
                        <div class="col-md-8 mb-3" id="addCustomerSection" style="{{ old('meeting_with') == 'customer' ? '' : 'display: none;' }}">
                            <label class="form-label">Select Customer <span class="text-danger">*</span></label>
                            <select class="form-select" name="customer_id" id="addCustomerId">
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a customer</div>
                        </div>
                        <div class="col-md-8 mb-3" id="addVendorSection" style="{{ old('meeting_with') == 'vendor' ? '' : 'display: none;' }}">
                            <label class="form-label">Select Vendor <span class="text-danger">*</span></label>
                            <select class="form-select" name="vendor_id" id="addVendorId">
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a vendor</div>
                        </div>
                        <div class="col-md-8 mb-3" id="addOtherSection" style="{{ old('meeting_with') == 'other' ? '' : 'display: none;' }}">
                            <label class="form-label">Attendee Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="other_attendee" id="addOtherAttendee" value="{{ old('other_attendee') }}" maxlength="150" placeholder="Enter name">
                            <div class="invalid-feedback">Please enter attendee name</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" value="{{ old('location') }}" maxlength="20">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="description" maxlength="150">{{ old('description') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-lg me-1"></i> Save Meeting
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if(auth()->user()->hasPermission('meetings', 'edit'))
@foreach($meetings as $meeting)
@if(!$meeting->trashed() && $meeting->status !== 'completed')

<div class="modal fade" id="editMeetingModal{{ $meeting->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Meeting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.meetings.update', $meeting) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Meeting Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control edit-meeting-title" name="title" value="{{ $meeting->title }}" maxlength="40" data-meeting-id="{{ $meeting->id }}">
                        <div class="invalid-feedback">Please enter meeting title</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control edit-meeting-datetime" name="meeting_at" value="{{ $meeting->meeting_at->format('Y-m-d\TH:i') }}" data-meeting-id="{{ $meeting->id }}">
                            <div class="invalid-feedback">Please select date and time</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Purpose</label>
                            <select class="form-select" name="purpose_id">
                                <option value="">Select Purpose</option>
                                @foreach($meetingPurposes as $purpose)
                                    <option value="{{ $purpose->id }}" {{ $meeting->purpose_id == $purpose->id ? 'selected' : '' }}>{{ $purpose->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Meeting With <span class="text-danger">*</span></label>
                            <select class="form-select edit-meeting-with" data-meeting-id="{{ $meeting->id }}" name="meeting_with">
                                <option value="">Select</option>
                                <option value="lead" {{ $meeting->lead_id ? 'selected' : '' }}>Lead</option>
                                <option value="customer" {{ $meeting->customer_id ? 'selected' : '' }}>Customer</option>
                                <option value="vendor" {{ $meeting->vendor_id ? 'selected' : '' }}>Vendor</option>
                                <option value="other" {{ $meeting->other_attendee ? 'selected' : '' }}>Other</option>
                            </select>
                            <div class="invalid-feedback">Please select meeting with</div>
                        </div>
                        <div class="col-md-8 mb-3 edit-lead-section-{{ $meeting->id }}" style="{{ $meeting->lead_id ? '' : 'display: none;' }}">
                            <label class="form-label">Select Lead <span class="text-danger">*</span></label>
                            <select class="form-select edit-lead-select" name="lead_id" data-meeting-id="{{ $meeting->id }}">
                                <option value="">Select Lead</option>
                                @foreach($leads as $lead)
                                    <option value="{{ $lead->id }}" {{ $meeting->lead_id == $lead->id ? 'selected' : '' }}>{{ $lead->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a lead</div>
                        </div>
                        <div class="col-md-8 mb-3 edit-customer-section-{{ $meeting->id }}" style="{{ $meeting->customer_id ? '' : 'display: none;' }}">
                            <label class="form-label">Select Customer <span class="text-danger">*</span></label>
                            <select class="form-select edit-customer-select" name="customer_id" data-meeting-id="{{ $meeting->id }}">
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ $meeting->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a customer</div>
                        </div>
                        <div class="col-md-8 mb-3 edit-vendor-section-{{ $meeting->id }}" style="{{ $meeting->vendor_id ? '' : 'display: none;' }}">
                            <label class="form-label">Select Vendor <span class="text-danger">*</span></label>
                            <select class="form-select edit-vendor-select" name="vendor_id" data-meeting-id="{{ $meeting->id }}">
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ $meeting->vendor_id == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a vendor</div>
                        </div>
                        <div class="col-md-8 mb-3 edit-other-section-{{ $meeting->id }}" style="{{ $meeting->other_attendee ? '' : 'display: none;' }}">
                            <label class="form-label">Attendee Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control edit-other-input" name="other_attendee" value="{{ $meeting->other_attendee }}" maxlength="150" placeholder="Enter name" data-meeting-id="{{ $meeting->id }}">
                            <div class="invalid-feedback">Please enter attendee name</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" value="{{ $meeting->location }}" maxlength="20">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="description" maxlength="150">{{ $meeting->description }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Update Meeting
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="completeMeetingModal{{ $meeting->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Complete Meeting</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.meetings.complete', $meeting) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Meeting Details</label>
                        <div class="p-3 bg-light rounded">
                            <strong>{{ $meeting->title }}</strong><br>
                            <small class="text-muted">{{ $meeting->meeting_at->format('d-m-Y') }}, {{ $meeting->meeting_at->format('h:i A') }}</small><br>
                            @if($meeting->lead)
                                <small>With: {{ $meeting->lead->name }} (Lead)</small>
                            @elseif($meeting->customer)
                                <small>With: {{ $meeting->customer->name }} (Customer)</small>
                            @elseif($meeting->other_attendee)
                                <small>With: {{ $meeting->other_attendee }}</small>
                            @endif
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meeting Outcome</label>
                        <textarea class="form-control" name="outcome" rows="3" placeholder="Describe the outcome of this meeting..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> Mark Complete</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="rescheduleMeetingModal{{ $meeting->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>Reschedule Meeting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.meetings.reschedule', $meeting) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Schedule</label>
                        <div class="p-3 bg-light rounded">
                            <strong>{{ $meeting->title }}</strong><br>
                            <small class="text-muted">{{ $meeting->meeting_at->format('d-m-Y') }}, {{ $meeting->meeting_at->format('h:i A') }}</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" name="meeting_at" value="{{ date('Y-m-d') }}T10:00">
                        <div class="invalid-feedback">Meeting date cannot be in the past</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-calendar-check me-1"></i> Reschedule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach
@endif
@endpush

@push('styles')
<style>
    .meeting-filter-select {
        background-color: var(--primary-color) !important;
        color: #fff !important;
        border: none !important;
        font-weight: 600;
        padding: 8px 35px 8px 15px;
        border-radius: 6px;
        cursor: pointer;
        min-width: 160px;
    }
    .meeting-filter-select:focus {
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.4) !important;
    }
    .meeting-filter-select option {
        background-color: #fff;
        color: #333;
        padding: 10px;
    }
    .card-header .form-control {
        background-color: #fff !important;
        color: #333 !important;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function isPastDateTime(datetimeValue) {
        if (!datetimeValue) return false;
        var selectedDate = new Date(datetimeValue);
        var now = new Date();
        return selectedDate < now;
    }

    function getMinDateTime() {
        var now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        return now.toISOString().slice(0, 16);
    }

    var minDateTime = getMinDateTime();
    document.querySelectorAll('input[type="datetime-local"]').forEach(function(input) {
        input.setAttribute('min', minDateTime);
    });

    var addMeetingWith = document.getElementById('addMeetingWith');
    if (addMeetingWith) {
        addMeetingWith.addEventListener('change', function() {
            document.getElementById('addLeadSection').style.display = 'none';
            document.getElementById('addCustomerSection').style.display = 'none';
            document.getElementById('addVendorSection').style.display = 'none';
            document.getElementById('addOtherSection').style.display = 'none';
            this.classList.remove('is-invalid');

            if (this.value === 'lead') {
                document.getElementById('addLeadSection').style.display = 'block';
            } else if (this.value === 'customer') {
                document.getElementById('addCustomerSection').style.display = 'block';
            } else if (this.value === 'vendor') {
                document.getElementById('addVendorSection').style.display = 'block';
            } else if (this.value === 'other') {
                document.getElementById('addOtherSection').style.display = 'block';
            }
        });
    }

    document.querySelectorAll('.edit-meeting-with').forEach(function(select) {
        select.addEventListener('change', function() {
            var meetingId = this.getAttribute('data-meeting-id');
            document.querySelector('.edit-lead-section-' + meetingId).style.display = 'none';
            document.querySelector('.edit-customer-section-' + meetingId).style.display = 'none';
            document.querySelector('.edit-vendor-section-' + meetingId).style.display = 'none';
            document.querySelector('.edit-other-section-' + meetingId).style.display = 'none';
            this.classList.remove('is-invalid');

            if (this.value === 'lead') {
                document.querySelector('.edit-lead-section-' + meetingId).style.display = 'block';
            } else if (this.value === 'customer') {
                document.querySelector('.edit-customer-section-' + meetingId).style.display = 'block';
            } else if (this.value === 'vendor') {
                document.querySelector('.edit-vendor-section-' + meetingId).style.display = 'block';
            } else if (this.value === 'other') {
                document.querySelector('.edit-other-section-' + meetingId).style.display = 'block';
            }
        });
    });

    var addMeetingForm = document.querySelector('#addMeetingModal form');
    if (addMeetingForm) {
        addMeetingForm.addEventListener('submit', function(e) {
            var isValid = true;
            var meetingWith = document.getElementById('addMeetingWith');
            var datetime = document.getElementById('addMeetingDatetime');
            var title = document.getElementById('addMeetingTitle');

            meetingWith.classList.remove('is-invalid');
            document.getElementById('addLeadId').classList.remove('is-invalid');
            document.getElementById('addCustomerId').classList.remove('is-invalid');
            document.getElementById('addVendorId').classList.remove('is-invalid');
            document.getElementById('addOtherAttendee').classList.remove('is-invalid');
            if (title) title.classList.remove('is-invalid');
            if (datetime) datetime.classList.remove('is-invalid');

            if (!title.value.trim()) {
                title.classList.add('is-invalid');
                isValid = false;
            }

            if (!datetime.value) {
                datetime.classList.add('is-invalid');
                datetime.nextElementSibling.textContent = 'Please select date and time';
                isValid = false;
            } else if (isPastDateTime(datetime.value)) {
                datetime.classList.add('is-invalid');
                datetime.nextElementSibling.textContent = 'Meeting date cannot be in the past';
                isValid = false;
            }

            if (!meetingWith.value) {
                meetingWith.classList.add('is-invalid');
                isValid = false;
            } else if (meetingWith.value === 'lead') {
                var leadSelect = document.getElementById('addLeadId');
                if (!leadSelect.value) {
                    leadSelect.classList.add('is-invalid');
                    isValid = false;
                }
            } else if (meetingWith.value === 'customer') {
                var customerSelect = document.getElementById('addCustomerId');
                if (!customerSelect.value) {
                    customerSelect.classList.add('is-invalid');
                    isValid = false;
                }
            } else if (meetingWith.value === 'vendor') {
                var vendorSelect = document.getElementById('addVendorId');
                if (!vendorSelect.value) {
                    vendorSelect.classList.add('is-invalid');
                    isValid = false;
                }
            } else if (meetingWith.value === 'other') {
                var otherInput = document.getElementById('addOtherAttendee');
                if (!otherInput.value.trim()) {
                    otherInput.classList.add('is-invalid');
                    isValid = false;
                }
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    document.querySelectorAll('[id^="editMeetingModal"] form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            var isValid = true;
            var title = form.querySelector('.edit-meeting-title');
            var datetime = form.querySelector('.edit-meeting-datetime');
            var meetingWith = form.querySelector('.edit-meeting-with');

            if (title) title.classList.remove('is-invalid');
            if (datetime) datetime.classList.remove('is-invalid');
            if (meetingWith) meetingWith.classList.remove('is-invalid');
            var leadSelect = form.querySelector('.edit-lead-select');
            var customerSelect = form.querySelector('.edit-customer-select');
            var otherInput = form.querySelector('.edit-other-input');
            if (leadSelect) leadSelect.classList.remove('is-invalid');
            if (customerSelect) customerSelect.classList.remove('is-invalid');
            if (otherInput) otherInput.classList.remove('is-invalid');

            if (title && !title.value.trim()) {
                title.classList.add('is-invalid');
                isValid = false;
            }

            if (datetime && !datetime.value) {
                datetime.classList.add('is-invalid');
                datetime.nextElementSibling.textContent = 'Please select date and time';
                isValid = false;
            } else if (datetime && isPastDateTime(datetime.value)) {
                datetime.classList.add('is-invalid');
                datetime.nextElementSibling.textContent = 'Meeting date cannot be in the past';
                isValid = false;
            }

            if (meetingWith) {
                if (!meetingWith.value) {
                    meetingWith.classList.add('is-invalid');
                    isValid = false;
                } else if (meetingWith.value === 'lead') {
                    if (leadSelect && !leadSelect.value) {
                        leadSelect.classList.add('is-invalid');
                        isValid = false;
                    }
                } else if (meetingWith.value === 'customer') {
                    if (customerSelect && !customerSelect.value) {
                        customerSelect.classList.add('is-invalid');
                        isValid = false;
                    }
                } else if (meetingWith.value === 'other') {
                    if (otherInput && !otherInput.value.trim()) {
                        otherInput.classList.add('is-invalid');
                        isValid = false;
                    }
                }
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    });

    document.querySelectorAll('[id^="rescheduleMeetingModal"] form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            var datetime = form.querySelector('input[name="meeting_at"]');
            if (datetime) {
                datetime.classList.remove('is-invalid');
                if (!datetime.value) {
                    datetime.classList.add('is-invalid');
                    e.preventDefault();
                } else if (isPastDateTime(datetime.value)) {
                    datetime.classList.add('is-invalid');
                    if (!datetime.nextElementSibling || !datetime.nextElementSibling.classList.contains('invalid-feedback')) {
                        var feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        feedback.textContent = 'Meeting date cannot be in the past';
                        datetime.parentNode.appendChild(feedback);
                    } else {
                        datetime.nextElementSibling.textContent = 'Meeting date cannot be in the past';
                    }
                    e.preventDefault();
                }
            }
        });
    });

    @if(($errors->any() || session('error')) && old('_token'))
        var addModalEl = document.getElementById('addMeetingModal');
        if (addModalEl) {
            var addModal = new bootstrap.Modal(addModalEl);
            addModal.show();
        }
    @endif
});
</script>
@endpush
