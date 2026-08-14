@extends('layouts.app')
@section('title', 'Meeting Details')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-calendar-event icon-gradient bg-mean-fruit"></i>
            </div>
            <div title="{{ $meeting->title }}">
                {{ Str::limit($meeting->title, 30) }}
            </div>
        </div>
        <div class="page-title-actions">
            @if(!isset($isTrashed) || !$isTrashed)
                @can('meetings.edit')
                @if($meeting->status === 'scheduled' || $meeting->status === 'rescheduled')
                <button class="btn btn-success me-2" type="button" data-bs-toggle="modal" data-bs-target="#completeMeetingModal">
                    <i class="bi bi-check-circle me-1"></i> Mark Complete
                </button>
                <button class="btn btn-info me-2" type="button" data-bs-toggle="modal" data-bs-target="#rescheduleMeetingModal">
                    <i class="bi bi-calendar-plus me-1"></i> Reschedule
                </button>
                @endif
                <button class="btn btn-primary me-2" type="button" data-bs-toggle="modal" data-bs-target="#editMeetingModal">
                    <i class="bi bi-pencil me-1"></i> Edit
                </button>
                @endcan
            @else
            <span class="badge bg-danger me-2 py-2 px-3">
                <i class="bi bi-exclamation-triangle me-1"></i> Deleted Meeting (Read Only)
            </span>
            @can('meetings.delete')
            <form action="{{ route('admin.meetings.reactivate', $meeting->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success me-2">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                </button>
            </form>
            @endcan
            @endif
            <a href="{{ $backUrl ?? route('admin.meetings.index') }}" class="btn btn-outline-secondary">
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
                <div class="row mb-3" style="overflow: hidden;">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge bg-{{ $meeting->status_color }}">{{ ucfirst($meeting->status) }}</span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Date & Time</small>
                        <strong>{{ $meeting->meeting_at ? ($meeting->meeting_at->isToday() ? 'Today' : $meeting->meeting_at->format('d-m-Y')) . ' ' . $meeting->meeting_at->format('h:i A') : '-' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Purpose</small>
                        @if($meeting->purpose)
                        <span class="badge bg-info">{{ Str::limit($meeting->purpose->name, 20) }}</span>
                        @else
                        <strong>-</strong>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Location</small>
                        <strong title="{{ $meeting->location ?? '-' }}">{{ Str::limit($meeting->location, 20) ?? '-' }}</strong>
                    </div>
                </div>
                <hr class="my-2">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Meeting With</small>
                        @if($meeting->lead)
                        <a href="{{ route('admin.leads.show', $meeting->lead) }}">{{ $meeting->lead->name }}</a>
                        <small class="text-muted">(Lead)</small>
                        @elseif($meeting->customer)
                        <a href="{{ route('admin.customers.show', $meeting->customer) }}">{{ $meeting->customer->name }}</a>
                        <small class="text-muted">(Customer)</small>
                        @elseif($meeting->vendor)
                        <a href="{{ route('admin.vendors.show', $meeting->vendor) }}">{{ $meeting->vendor->name }}</a>
                        <small class="text-muted">(Vendor)</small>
                        @elseif($meeting->other_attendee)
                        <strong>{{ $meeting->other_attendee }}</strong>
                        <small class="text-muted">(Other)</small>
                        @else
                        <strong>-</strong>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Trip</small>
                        @if($meeting->trip)
                        <a href="{{ route('admin.trips.show', $meeting->trip) }}">{{ $meeting->trip->name }}</a>
                        @else
                        <strong>-</strong>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Assigned To</small>
                        <strong>{{ $meeting->assignedUser->name ?? '-' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Created</small>
                        <strong>{{ $meeting->created_at->format('d-m-Y') }}</strong>
                    </div>
                </div>
                @if($meeting->description || $meeting->outcome)
                <hr class="my-2">
                <div class="row">
                    @if($meeting->description)
                    <div class="col-md-6">
                        <small class="text-muted d-block">Description</small>
                        <strong>{{ $meeting->description }}</strong>
                    </div>
                    @endif
                    @if($meeting->outcome)
                    <div class="col-md-6">
                        <small class="text-muted d-block">Outcome</small>
                        <strong>{{ $meeting->outcome }}</strong>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="main-card mb-3 card">
            <div class="card-header">
                <span><i class="bi bi-clock-history me-2"></i> Activity Timeline</span>
            </div>
            <table class="table table-hover mb-0" style="table-layout: fixed; width: 100%;">
                <thead class="table-light">
                    <tr>
                        <th style="white-space: nowrap">Date</th>
                        <th style="white-space: nowrap">Type</th>
                        <th>Details</th>
                        <th style="white-space: nowrap">Added By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($meeting->updates as $update)
                    <tr style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#activityDetailModal{{ $update->id }}">
                        <td style="white-space: nowrap">{{ $update->created_at->format('d-m-Y h:i A') }}</td>
                        <td>
                            <span class="badge bg-{{ $update->type_color }}">
                                <i class="bi {{ $update->type_icon }} me-1"></i>{{ $update->type_label }}
                            </span>
                        </td>
                        <td style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ Str::limit($update->notes, 50) }}
                        </td>
                        <td style="white-space: nowrap">{{ $update->user->name ?? 'System' }}</td>
                    </tr>
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
@if($meeting->status === 'scheduled' || $meeting->status === 'rescheduled')
<div class="modal fade" id="completeMeetingModal" tabindex="-1">
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
                            <small class="text-muted">{{ $meeting->meeting_at->format('d-m-Y h:i A') }}</small><br>
                            @if($meeting->lead)
                            <small>With: {{ $meeting->lead->name }} (Lead)</small>
                            @elseif($meeting->customer)
                            <small>With: {{ $meeting->customer->name }} (Customer)</small>
                            @elseif($meeting->vendor)
                            <small>With: {{ $meeting->vendor->name }} (Vendor)</small>
                            @endif
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="outcome" class="form-label">Meeting Outcome</label>
                        <textarea class="form-control" name="outcome" id="outcome" rows="3" placeholder="Describe the outcome of this meeting..."></textarea>
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

<div class="modal fade" id="rescheduleMeetingModal" tabindex="-1">
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
                            <strong>{{ $meeting->title }}</strong><br>
                            <small class="text-muted">{{ $meeting->meeting_at->format('d-m-Y h:i A') }}</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="meeting_at" class="form-label">New Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" name="meeting_at" id="reschedule_meeting_at" value="{{ date('Y-m-d') }}T10:00">
                        <div class="invalid-feedback">Please select date and time</div>
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
@endif

<div class="modal fade" id="editMeetingModal" tabindex="-1">
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
                        <label for="title" class="form-label">Meeting Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" id="edit_title" value="{{ $meeting->title }}" maxlength="40">
                        <div class="invalid-feedback">Please enter meeting title</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_meeting_at" class="form-label">Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="meeting_at" id="edit_meeting_at" value="{{ $meeting->meeting_at ? $meeting->meeting_at->format('Y-m-d\TH:i') : '' }}">
                            <div class="invalid-feedback">Please select date and time</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="purpose_id" class="form-label">Purpose</label>
                            <select class="form-select" name="purpose_id" id="purpose_id">
                                <option value="">Select Purpose</option>
                                @foreach($meetingPurposes as $purpose)
                                <option value="{{ $purpose->id }}" {{ $meeting->purpose_id == $purpose->id ? 'selected' : '' }}>{{ $purpose->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="meeting_with" class="form-label">Meeting With <span class="text-danger">*</span></label>
                            <select class="form-select" name="meeting_with" id="meeting_with">
                                <option value="lead" {{ $meeting->lead_id ? 'selected' : '' }}>Lead</option>
                                <option value="customer" {{ $meeting->customer_id ? 'selected' : '' }}>Customer</option>
                                <option value="vendor" {{ $meeting->vendor_id ? 'selected' : '' }}>Vendor</option>
                                <option value="other" {{ $meeting->other_attendee ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="leadSelectContainer" style="{{ !$meeting->lead_id ? 'display:none' : '' }}">
                            <label for="lead_id" class="form-label">Lead</label>
                            <select class="form-select" name="lead_id" id="lead_id">
                                <option value="">Select Lead</option>
                                @foreach($leads as $lead)
                                <option value="{{ $lead->id }}" {{ $meeting->lead_id == $lead->id ? 'selected' : '' }}>{{ $lead->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="customerSelectContainer" style="{{ !$meeting->customer_id ? 'display:none' : '' }}">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select class="form-select" name="customer_id" id="customer_id">
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ $meeting->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="vendorSelectContainer" style="{{ !$meeting->vendor_id ? 'display:none' : '' }}">
                            <label for="vendor_id" class="form-label">Vendor</label>
                            <select class="form-select" name="vendor_id" id="vendor_id">
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ $meeting->vendor_id == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="otherAttendeeContainer" style="{{ !$meeting->other_attendee ? 'display:none' : '' }}">
                            <label for="other_attendee" class="form-label">Other Attendee</label>
                            <input type="text" class="form-control" name="other_attendee" id="other_attendee" value="{{ $meeting->other_attendee }}" maxlength="20">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" id="location" value="{{ $meeting->location }}" maxlength="20">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="description" rows="2" maxlength="150">{{ $meeting->description }}</textarea>
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
@endif

@foreach($meeting->updates as $update)
<div class="modal fade" id="activityDetailModal{{ $update->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-{{ $update->type_color }} text-white">
                <h5 class="modal-title"><i class="bi {{ $update->type_icon }} me-2"></i>{{ $update->type_label }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Date</small>
                    <strong>{{ $update->created_at->format('d-m-Y h:i A') }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Details</small>
                    <p class="mb-0">{{ $update->notes }}</p>
                </div>
                @if($update->old_value && $update->new_value)
                <div class="mb-3">
                    <small class="text-muted d-block">Change</small>
                    <span class="text-danger">{{ $update->old_value }}</span>
                    <i class="bi bi-arrow-right mx-2"></i>
                    <span class="text-success">{{ $update->new_value }}</span>
                </div>
                @endif
                <div>
                    <small class="text-muted d-block">Added By</small>
                    <strong>{{ $update->user->name ?? 'System' }}</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach
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

    var meetingWith = document.getElementById('meeting_with');
    var leadContainer = document.getElementById('leadSelectContainer');
    var customerContainer = document.getElementById('customerSelectContainer');
    var vendorContainer = document.getElementById('vendorSelectContainer');
    var otherContainer = document.getElementById('otherAttendeeContainer');

    function toggleContainers() {
        leadContainer.style.display = 'none';
        customerContainer.style.display = 'none';
        vendorContainer.style.display = 'none';
        otherContainer.style.display = 'none';

        switch(meetingWith.value) {
            case 'lead':
                leadContainer.style.display = '';
                break;
            case 'customer':
                customerContainer.style.display = '';
                break;
            case 'vendor':
                vendorContainer.style.display = '';
                break;
            case 'other':
                otherContainer.style.display = '';
                break;
        }
    }

    if (meetingWith) {
        meetingWith.addEventListener('change', toggleContainers);
    }

    var editForm = document.querySelector('#editMeetingModal form');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            var isValid = true;
            var title = document.getElementById('edit_title');
            var datetime = document.getElementById('edit_meeting_at');

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
    }
    
    var rescheduleForm = document.querySelector('#rescheduleMeetingModal form');
    if (rescheduleForm) {
        rescheduleForm.addEventListener('submit', function(e) {
            var datetime = document.getElementById('reschedule_meeting_at');
            if (datetime) {
                datetime.classList.remove('is-invalid');
                if (!datetime.value) {
                    datetime.classList.add('is-invalid');
                    datetime.nextElementSibling.textContent = 'Please select date and time';
                    e.preventDefault();
                } else if (isPastDateTime(datetime.value)) {
                    datetime.classList.add('is-invalid');
                    datetime.nextElementSibling.textContent = 'Meeting date cannot be in the past';
                    e.preventDefault();
                }
            }
        });
    }
});
</script>
@endpush
