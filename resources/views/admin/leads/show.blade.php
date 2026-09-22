@extends('layouts.app')
@section('title', 'Lead Details')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-person-lines-fill icon-gradient bg-mean-fruit"></i>
            </div>
            <div title="{{ $lead->name }}">
                {{ Str::limit($lead->name, 30) }}

            </div>
        </div>
        <div class="page-title-actions">
            @if(!isset($isTrashed) || !$isTrashed)
            @can('leads.edit')
            <a href="{{ route('admin.leads.edit', $lead ?? 1) }}" class="btn btn-primary me-2">
                <i class="bi bi-pencil me-1"></i> Edit Lead
            </a>
            <a href="{{ route('admin.leads.convert', $lead ?? 1) }}" class="btn btn-success me-2">
                <i class="bi bi-person-check me-1"></i> Convert to Customer
            </a>
            @endcan
            @else
            <span class="badge bg-danger me-2 py-2 px-3">
                <i class="bi bi-exclamation-triangle me-1"></i> Deleted Lead (Read Only)
            </span>
            @can('leads.delete')
            <form action="{{ route('admin.leads.restore', $lead->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success me-2">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Restore Lead
                </button>
            </form>
            @endcan
            @endif
            <a href="{{ route('admin.leads.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
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
<div class="row mb-3">
    <div class="col-md-12">
        <div class="main-card card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge bg-{{ $lead->status_color }}">{{ ucfirst($lead->status) }}</span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Final Budget</small>
                        <strong class="text-success">{{ $lead->final_budget ? formatMoney($lead->final_budget) : '-' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Upcoming Meetings</small>
                        <strong>{{ $upcomingMeetings->count() }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">{{ $lead->converted_at ? 'Converted On' : 'Lead Since' }}</small>
                        <strong>{{ $lead->converted_at ? $lead->converted_at->format('d-m-Y') : $lead->created_at->format('d-m-Y') }}</strong>
                    </div>
                </div>
                <hr class="my-2">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Mobile</small>
                        <strong>{{ $lead->mobile ? $lead->full_mobile : '-' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Email</small>
                        <strong>{{ $lead->email ?? '-' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">City</small>
                        <strong>{{ $lead->city ?? '-' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Work Lead</small>
                        <strong>{{ $lead->work_lead ?? '-' }}</strong>
                    </div>
                </div>
                @if($lead->address)
                <hr class="my-2">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Address</small>
                        <strong>{{ $lead->address }}</strong>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="main-card mb-3 card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-event me-2"></i> Upcoming Meetings</span>
                @if(!isset($isTrashed) || !$isTrashed)
                @can('meetings.create')
                <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#scheduleMeetingModal" style="color: #ffffff !important">
                    <i class="bi bi-plus-lg me-1"></i> Schedule Meeting
                </button>
                @endcan
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="white-space: nowrap">Date</th>
                            <th>Title</th>
                            <th style="white-space: nowrap">Time</th>
                            <th>Location</th>
                            <th>Purpose</th>
                            @if(!isset($isTrashed) || !$isTrashed)
                            <th width="150" style="white-space: nowrap">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($upcomingMeetings as $meeting)
                        <tr>
                            <td style="white-space: nowrap">{{ $meeting->meeting_at->isToday() ? 'Today' : $meeting->meeting_at->format('d-m-Y') }}</td>
                            <td><strong class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $meeting->title }}">{{ $meeting->title }}</strong></td>
                            <td style="white-space: nowrap">{{ $meeting->meeting_at->format('h:i A') }}</td>
                            <td><span class="text-truncate d-inline-block" style="max-width: 120px;" title="{{ $meeting->location }}">{{ $meeting->location ?? '-' }}</span></td>
                            <td style="white-space: nowrap">
                                @if($meeting->purpose)
                                <span class="badge bg-info">{{ $meeting->purpose->name }}</span>
                                @else
                                -
                                @endif
                            </td>
                            @if(!isset($isTrashed) || !$isTrashed)
                            <td style="white-space: nowrap">
                                <a href="{{ route('admin.meetings.show', $meeting) }}" class="btn btn-sm btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('meetings.edit')
                                <button class="btn btn-sm btn-outline-primary" title="Edit" data-bs-toggle="modal" data-bs-target="#editMeetingModal{{ $meeting->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success" title="Mark Complete" data-bs-toggle="modal" data-bs-target="#completeMeetingModal{{ $meeting->id }}">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-info" title="Reschedule" data-bs-toggle="modal" data-bs-target="#rescheduleMeetingModal{{ $meeting->id }}">
                                    <i class="bi bi-calendar-plus"></i>
                                </button>
                                @endcan
                                @can('meetings.delete')
                                <form action="{{ route('admin.meetings.deactivate', $meeting) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this meeting?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-archive"></i>
                                    </button>
                                </form>
                                @endcan
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ (!isset($isTrashed) || !$isTrashed) ? 6 : 5 }}" class="text-center py-4 text-muted">No upcoming meetings scheduled.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="main-card mb-3 card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2"></i> Activity Timeline</span>
                @if(!isset($isTrashed) || !$isTrashed)
                @can('leads.edit')
                <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addUpdateModal" style="color: #fff !important;">
                    <i class="bi bi-plus-lg me-1"></i> Add Update
                </button>
                @endcan
                @endif
            </div>
                <table class="table table-hover mb-0" style="table-layout: fixed; width: 100%;">
                    <thead class="table-light">
                        <tr>
                            <th width="130" style="white-space: nowrap">Follow-up</th>
                            <th width="150" style="white-space: nowrap">Type</th>
                            <th width="250">Details</th>
                            <th width="100" style="white-space: nowrap">Added By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                            @if($activity['type'] === 'update')
                                @php $update = $activity['data']; @endphp
                                <tr>
                                    <td style="white-space: nowrap">{{ $update->follow_up_date ? formatDate($update->follow_up_date, true) : '-' }}</td>
                                    <td><span class="badge bg-{{ $update->updateType->color ?? 'primary' }}"><i class="bi {{ $update->updateType->icon ?? 'bi-sticky' }} me-1"></i>{{ $update->updateType->name ?? 'Update' }}</span></td>
                                    <td style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $update->notes }}">{{ $update->notes }}</td>
                                    <td style="white-space: nowrap">{{ $update->user->name ?? 'System' }}</td>
                                </tr>
                            @endif
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No activity recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
        </div>
    </div>
</div>
@endsection
@push('modals')
@if(!isset($isTrashed) || !$isTrashed)
<div class="modal fade" id="scheduleMeetingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>Schedule Meeting</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.leads.meetings.store', $lead) }}" method="POST">
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
                        <label class="form-label">Lead</label>
                        <div class="form-control bg-light">{{ $lead->name }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label">Meeting Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" id="leadMeetingTitle" value="{{ old('title') }}" placeholder="e.g., Site Visit - Measurement" maxlength="40">
                        <div class="invalid-feedback">Please enter meeting title</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="meeting_at" class="form-label">Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="meeting_at" id="leadMeetingDatetime" value="{{ old('meeting_at', date('Y-m-d') . 'T10:00') }}">
                            <div class="invalid-feedback">Please select date and time</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="purpose_id" class="form-label">Purpose</label>
                            <select class="form-select" name="purpose_id" id="purpose_id">
                                <option value="">Select Purpose</option>
                                @foreach($meetingPurposes as $purpose)
                                    <option value="{{ $purpose->id }}" {{ old('purpose_id') == $purpose->id ? 'selected' : '' }}>{{ $purpose->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" id="location" value="{{ old('location') }}" placeholder="e.g., Client's Home, Office" maxlength="20">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Notes</label>
                        <textarea class="form-control" name="description" id="description" rows="2" placeholder="Any additional notes..." maxlength="150">{{ old('description') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-calendar-check me-1"></i> Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($upcomingMeetings as $meeting)
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
                            <strong>{{ $lead->name }}</strong> (Lead)<br>
                            <small class="text-muted">{{ $meeting->meeting_at->format('d-m-Y') }}, {{ $meeting->meeting_at->format('h:i A') }}</small><br>
                            <small>{{ $meeting->title }}</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="outcome{{ $meeting->id }}" class="form-label">Meeting Outcome <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="outcome" id="outcome{{ $meeting->id }}" rows="3" placeholder="Describe the outcome of this meeting..." required></textarea>
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
                <input type="hidden" name="meeting_with" value="lead">
                <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Lead</label>
                        <div class="form-control bg-light">{{ $lead->name }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_title{{ $meeting->id }}" class="form-label">Meeting Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control lead-edit-title" name="title" id="edit_title{{ $meeting->id }}" value="{{ $meeting->title }}" maxlength="40">
                        <div class="invalid-feedback">Please enter meeting title</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_meeting_at{{ $meeting->id }}" class="form-label">Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control lead-edit-datetime" name="meeting_at" id="edit_meeting_at{{ $meeting->id }}" value="{{ $meeting->meeting_at->format('Y-m-d\TH:i') }}">
                            <div class="invalid-feedback">Please select date and time</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_purpose_id{{ $meeting->id }}" class="form-label">Purpose</label>
                            <select class="form-select" name="purpose_id" id="edit_purpose_id{{ $meeting->id }}">
                                <option value="">Select Purpose</option>
                                @foreach($meetingPurposes as $purpose)
                                    <option value="{{ $purpose->id }}" {{ $meeting->purpose_id == $purpose->id ? 'selected' : '' }}>{{ $purpose->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_location{{ $meeting->id }}" class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" id="edit_location{{ $meeting->id }}" value="{{ $meeting->location }}" maxlength="20">
                    </div>
                    <div class="mb-3">
                        <label for="edit_description{{ $meeting->id }}" class="form-label">Notes</label>
                        <textarea class="form-control" name="description" id="edit_description{{ $meeting->id }}" rows="2" maxlength="150">{{ $meeting->description }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="rescheduleMeetingModal{{ $meeting->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>Reschedule Meeting</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.meetings.reschedule', $meeting) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Schedule</label>
                        <div class="p-3 bg-light rounded">
                            <strong>{{ $lead->name }}</strong> (Lead)<br>
                            <small class="text-muted">{{ $meeting->meeting_at->format('d-m-Y') }}, {{ $meeting->meeting_at->format('h:i A') }}</small><br>
                            <small>{{ $meeting->title }}</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="new_meeting_at{{ $meeting->id }}" class="form-label">New Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" name="meeting_at" id="new_meeting_at{{ $meeting->id }}" value="{{ date('Y-m-d') }}T10:00">
                        <div class="invalid-feedback">Meeting date cannot be in the past</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info"><i class="bi bi-calendar-check me-1"></i> Reschedule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<div class="modal fade" id="addUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.leads.updates.store', $lead) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="update_type_id" class="form-label">Update Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="update_type_id" id="update_type_id" required>
                                <option value="">Select Type</option>
                                @foreach($updateTypes as $type)
                                    <option value="{{ $type->id }}" data-icon="{{ $type->icon }}" data-color="{{ $type->color }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Lead Status</label>
                            @php
                                $currentStatusOrder = $leadStatuses->firstWhere(fn($s) => strtolower($s->name) === $lead->status)?->sort_order ?? 0;
                                $isAdmin = auth()->user()->isAdmin();
                            @endphp
                            <select class="form-select" name="status" id="status">
                                <option value="">Keep Current</option>
                                @foreach($leadStatuses as $status)
                                    @if($isAdmin || $status->sort_order >= $currentStatusOrder)
                                        <option value="{{ strtolower($status->name) }}">{{ $status->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="follow_up_date" class="form-label">Next Follow-up Date</label>
                        <input type="datetime-local" class="form-control" name="follow_up_date" id="follow_up_date" placeholder="Select date and time">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes / Update Details <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="notes" id="notes" rows="3" placeholder="Enter update details..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
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

    var scheduleMeetingForm = document.querySelector('#scheduleMeetingModal form');
    if (scheduleMeetingForm) {
        scheduleMeetingForm.addEventListener('submit', function(e) {
            var isValid = true;
            var title = document.getElementById('leadMeetingTitle');
            var datetime = document.getElementById('leadMeetingDatetime');

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

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    document.querySelectorAll('[id^="editMeetingModal"] form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            var isValid = true;
            var title = form.querySelector('.lead-edit-title');
            var datetime = form.querySelector('.lead-edit-datetime');

            if (title) title.classList.remove('is-invalid');
            if (datetime) datetime.classList.remove('is-invalid');

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
        var meetingModal = document.getElementById('scheduleMeetingModal');
        if (meetingModal) {
            var modal = new bootstrap.Modal(meetingModal);
            modal.show();
        }
    @endif
});
</script>
@endpush
