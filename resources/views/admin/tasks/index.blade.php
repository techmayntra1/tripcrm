@extends('layouts.app')
@section('title', 'Tasks')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-list-task icon-gradient bg-sunny-morning"></i>
            </div>
            <div>
                Tasks
            </div>
        </div>
        <div class="page-title-actions">
            @if(auth()->user()->hasPermission('tasks', 'create'))
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addTaskModal" title="Add Task">
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
                        <a class="nav-link {{ request('tab') != 'deleted' ? 'active' : '' }}" href="{{ route('admin.tasks.index') }}">
                            <i class="bi bi-list-task me-1"></i> Active ({{ $allCount }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') == 'deleted' ? 'active' : '' }}" href="{{ route('admin.tasks.index', ['tab' => 'deleted']) }}">
                            <i class="bi bi-archive me-1"></i> Deleted ({{ $deletedCount }})
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    @if(request('tab') != 'deleted')
                    <form method="GET" action="{{ route('admin.tasks.index') }}" id="filterForm">
                        @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        <select name="tab" class="form-select form-select-sm" style="width: auto;" onchange="document.getElementById('filterForm').submit()">
                            <option value="" {{ !request('tab') ? 'selected' : '' }}>All</option>
                            <option value="today" {{ request('tab') == 'today' ? 'selected' : '' }}>Today ({{ $todayCount ?? 0 }})</option>
                            <option value="upcoming" {{ request('tab') == 'upcoming' ? 'selected' : '' }}>Upcoming ({{ $upcomingCount ?? 0 }})</option>
                            <option value="past" {{ request('tab') == 'past' ? 'selected' : '' }}>Overdue ({{ $pastCount ?? 0 }})</option>
                            <option value="in_progress" {{ request('tab') == 'in_progress' ? 'selected' : '' }}>In Progress ({{ $inProgressCount ?? 0 }})</option>
                            <option value="completed" {{ request('tab') == 'completed' ? 'selected' : '' }}>Completed ({{ $completedCount }})</option>
                        </select>
                    </form>
                    @endif
                    <form method="GET" action="{{ route('admin.tasks.index') }}" class="d-flex align-items-center gap-2">
                        @if(request('tab'))
                        <input type="hidden" name="tab" value="{{ request('tab') }}">
                        @endif
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" name="search" class="form-control" placeholder="Search tasks" value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                        @if(request('search'))
                        <a href="{{ route('admin.tasks.index', request('tab') ? ['tab' => request('tab')] : []) }}" class="btn btn-sm btn-danger" title="Clear">
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
                            <th>Project</th>
                            <th>Assigned To</th>
                            <th style="white-space: nowrap">Start Date</th>
                            <th style="white-space: nowrap">Due Date</th>
                            <th>Location</th>
                            <th style="white-space: nowrap">Status</th>
                            <th style="white-space: nowrap">Actions</th>
                        </tr>
                    </thead>
                        <tbody>
                            @forelse($tasks as $task)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if($task->trashed())
                                    <a href="{{ route('admin.tasks.trashed.show', $task->id) }}" class="name-truncate" title="{{ $task->title }}"><strong>{{ $task->title }}</strong></a>
                                    @else
                                    <a href="{{ route('admin.tasks.show', $task) }}" class="name-truncate" title="{{ $task->title }}"><strong>{{ $task->title }}</strong></a>
                                    @endif
                                    @if($task->description)
                                    <br><small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($task->project)
                                        <a href="{{ route('admin.projects.show', $task->project) }}" class="name-truncate">{{ $task->project->name }}</a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="name-truncate">{{ $task->assignee_name }}</span>
                                    <br><small class="text-muted">{{ ucfirst($task->assignee_type) }}</small>
                                </td>
                                <td>
                                    <strong>{{ $task->start_at->format('d-m-Y') }}</strong>
                                    <br><small class="text-muted">{{ $task->start_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    @if($task->due_at)
                                        <strong>{{ $task->due_at->format('d-m-Y') }}</strong>
                                        <br><small class="text-muted">{{ $task->due_at->format('h:i A') }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($task->location)
                                        <span class="name-truncate" title="{{ $task->location }}">{{ Str::limit($task->location, 30) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $task->status_color }}">
                                        {{ $task->status_name }}
                                    </span>
                                </td>
                                <td>
                                    <div class="gap-1">
                                        @if($task->trashed())
                                            @if(auth()->user()->hasPermission('tasks', 'edit'))
                                            <form action="{{ route('admin.tasks.reactivate', $task->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            </form>
                                            @endif
                                        @elseif($task->status_name === 'Completed')
                                            @if(auth()->user()->hasPermission('tasks', 'edit'))
                                            <form action="{{ route('admin.tasks.deactivate', $task) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this task?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="bi bi-archive"></i>
                                                </button>
                                            </form>
                                            @endif
                                        @else
                                            <a href="{{ route('admin.tasks.show', $task) }}" class="btn btn-sm btn-outline-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if(auth()->user()->hasPermission('tasks', 'edit'))
                                            <button type="button" class="btn btn-sm btn-outline-primary" title="Edit" data-bs-toggle="modal" data-bs-target="#editTaskModal{{ $task->id }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning" title="Update Status" data-bs-toggle="modal" data-bs-target="#statusTaskModal{{ $task->id }}">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                            <form action="{{ route('admin.tasks.deactivate', $task) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this task?')">
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
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="bi bi-list-task fs-3 d-block mb-2"></i>
                                    No tasks found.
                                </td>
                            </tr>
                            @endforelse
                    </tbody>
                </table>
                </div>
            </div>
            <div class="card-footer">
                @include('partials.pagination', ['paginator' => $tasks])
            </div>
        </div>
    </div>
</div>
@endsection

@push('modals')
@if(auth()->user()->hasPermission('tasks', 'create'))

<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.tasks.store') }}" method="POST" id="addTaskForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Task Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" id="addTaskTitle" value="{{ old('title') }}" maxlength="100">
                        <div class="invalid-feedback">Please enter task title</div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Project</label>
                            <select class="form-select" name="project_id" id="addProjectId">
                                <option value="">Select Project</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" data-address="{{ $project->site_address }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>{{ Str::limit($project->name, 40) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="start_at" id="addStartAt" value="{{ old('start_at', date('Y-m-d') . 'T10:00') }}">
                            <div class="invalid-feedback">Please select start date and time</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Due Date & Time</label>
                            <input type="datetime-local" class="form-control" name="due_at" id="addDueAt" value="{{ old('due_at') }}" min="{{ old('start_at', date('Y-m-d') . 'T10:00') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Assign To <span class="text-danger">*</span></label>
                            <select class="form-select" name="assignee_type" id="addAssigneeType">
                                <option value="">Select Type</option>
                                <option value="staff" {{ old('assignee_type') == 'staff' ? 'selected' : '' }}>Staff</option>
                                <option value="vendor" {{ old('assignee_type') == 'vendor' ? 'selected' : '' }}>Vendor</option>
                            </select>
                            <div class="invalid-feedback">Please select assignee type</div>
                        </div>
                        <div class="col-md-8 mb-3" id="addStaffSection" style="{{ old('assignee_type') == 'staff' ? '' : 'display: none;' }}">
                            <label class="form-label">Select Staff <span class="text-danger">*</span></label>
                            <select class="form-select" name="staff_id" id="addStaffId">
                                <option value="">Select Staff</option>
                                @foreach($staffMembers as $staff)
                                    <option value="{{ $staff->id }}" {{ old('staff_id') == $staff->id ? 'selected' : '' }}>{{ $staff->name }} ({{ $staff->role_display }})</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a staff member</div>
                        </div>
                        <div class="col-md-8 mb-3" id="addVendorSection" style="{{ old('assignee_type') == 'vendor' ? '' : 'display: none;' }}">
                            <label class="form-label">Select Vendor <span class="text-danger">*</span></label>
                            <select class="form-select" name="vendor_id" id="addVendorId">
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a vendor</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" id="addLocation" value="{{ old('location') }}" maxlength="255" placeholder="Type to search project addresses" list="projectAddressList">
                        <datalist id="projectAddressList">
                            @foreach($projects as $project)
                                @if($project->site_address)
                                <option value="{{ $project->site_address }}">{{ $project->name }}</option>
                                @endif
                            @endforeach
                        </datalist>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2" maxlength="200">{{ old('description') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-lg me-1"></i> Save Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if(auth()->user()->hasPermission('tasks', 'edit'))
@foreach($tasks as $task)
@if(!$task->trashed() && $task->status_name !== 'Completed')

<div class="modal fade" id="editTaskModal{{ $task->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.tasks.update', $task) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Task Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" value="{{ $task->title }}" maxlength="100">
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Project</label>
                            <select class="form-select edit-project-select" name="project_id" data-task-id="{{ $task->id }}">
                                <option value="">No Project</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" data-address="{{ $project->site_address }}" {{ $task->project_id == $project->id ? 'selected' : '' }}>{{ Str::limit($project->name, 40) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control task-start-at" name="start_at" value="{{ $task->start_at->format('Y-m-d\TH:i') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Due Date & Time</label>
                            <input type="datetime-local" class="form-control task-due-at" name="due_at" value="{{ $task->due_at ? $task->due_at->format('Y-m-d\TH:i') : '' }}" min="{{ $task->start_at->format('Y-m-d\TH:i') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Assign To <span class="text-danger">*</span></label>
                            <select class="form-select edit-assignee-type" data-task-id="{{ $task->id }}" name="assignee_type">
                                <option value="">Select Type</option>
                                <option value="staff" {{ $task->assignee_type == 'staff' ? 'selected' : '' }}>Staff</option>
                                <option value="vendor" {{ $task->assignee_type == 'vendor' ? 'selected' : '' }}>Vendor</option>
                            </select>
                        </div>
                        <div class="col-md-8 mb-3 edit-staff-section-{{ $task->id }}" style="{{ $task->assignee_type == 'staff' ? '' : 'display: none;' }}">
                            <label class="form-label">Select Staff <span class="text-danger">*</span></label>
                            <select class="form-select" name="staff_id">
                                <option value="">Select Staff</option>
                                @foreach($staffMembers as $staff)
                                    <option value="{{ $staff->id }}" {{ ($task->assignee_type == 'staff' && $task->assignee_id == $staff->id) ? 'selected' : '' }}>{{ $staff->name }} ({{ $staff->role_display }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8 mb-3 edit-vendor-section-{{ $task->id }}" style="{{ $task->assignee_type == 'vendor' ? '' : 'display: none;' }}">
                            <label class="form-label">Select Vendor <span class="text-danger">*</span></label>
                            <select class="form-select" name="vendor_id">
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ ($task->assignee_type == 'vendor' && $task->assignee_id == $vendor->id) ? 'selected' : '' }}>{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control edit-location-{{ $task->id }}" name="location" value="{{ $task->location }}" maxlength="255" placeholder="Type to search project addresses" list="projectAddressList">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2" maxlength="200">{{ $task->description }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Update Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="statusTaskModal{{ $task->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-arrow-repeat me-2"></i>Update Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.tasks.status', $task) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Task Details</label>
                        <div class="p-3 bg-light rounded">
                            <strong>{{ $task->title }}</strong><br>
                            <small class="text-muted">Start: {{ $task->start_at->format('d-m-Y h:i A') }}</small>
                            @if($task->due_at)
                            <br><small class="text-muted">Due: {{ $task->due_at->format('d-m-Y') }}</small>
                            @endif
                            <br><small>Assigned to: {{ $task->assignee_name }} ({{ ucfirst($task->assignee_type) }})</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status_id">
                                @foreach($taskStatuses as $status)
                                <option value="{{ $status->id }}" {{ $task->status_id == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Due Date & Time</label>
                            <input type="datetime-local" class="form-control" name="due_at" value="{{ $task->due_at ? $task->due_at->format('Y-m-d\TH:i') : '' }}" min="{{ $task->start_at->format('Y-m-d\TH:i') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-check-lg me-1"></i> Update Status</button>
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
    .task-filter-select {
        background-color: #ffc107 !important;
        color: #000 !important;
        border: none !important;
        font-weight: 600;
        padding: 8px 35px 8px 15px;
        border-radius: 6px;
        cursor: pointer;
        min-width: 160px;
    }
    .task-filter-select:focus {
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.4) !important;
    }
    .task-filter-select option {
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
    var addStartAt = document.getElementById('addStartAt');
    var addDueAt = document.getElementById('addDueAt');
    if (addStartAt && addDueAt) {
        addStartAt.addEventListener('change', function() {
            addDueAt.min = this.value;
            if (addDueAt.value && addDueAt.value < this.value) {
                addDueAt.value = this.value;
            }
        });
    }

    document.querySelectorAll('.task-start-at').forEach(function(startInput) {
        var form = startInput.closest('form');
        var dueInput = form.querySelector('.task-due-at');
        if (dueInput) {
            startInput.addEventListener('change', function() {
                dueInput.min = this.value;
                if (dueInput.value && dueInput.value < this.value) {
                    dueInput.value = this.value;
                }
            });
        }
    });

    var addAssigneeType = document.getElementById('addAssigneeType');
    if (addAssigneeType) {
        addAssigneeType.addEventListener('change', function() {
            document.getElementById('addStaffSection').style.display = 'none';
            document.getElementById('addVendorSection').style.display = 'none';
            this.classList.remove('is-invalid');

            if (this.value === 'staff') {
                document.getElementById('addStaffSection').style.display = 'block';
            } else if (this.value === 'vendor') {
                document.getElementById('addVendorSection').style.display = 'block';
            }
        });
    }

    var addProjectId = document.getElementById('addProjectId');
    if (addProjectId) {
        addProjectId.addEventListener('change', function() {
            var selected = this.options[this.selectedIndex];
            var locationField = document.getElementById('addLocation');
            if (this.value && selected.dataset.address) {
                locationField.value = selected.dataset.address;
            }
        });
    }

    document.querySelectorAll('.edit-assignee-type').forEach(function(select) {
        select.addEventListener('change', function() {
            var taskId = this.getAttribute('data-task-id');
            document.querySelector('.edit-staff-section-' + taskId).style.display = 'none';
            document.querySelector('.edit-vendor-section-' + taskId).style.display = 'none';
            this.classList.remove('is-invalid');

            if (this.value === 'staff') {
                document.querySelector('.edit-staff-section-' + taskId).style.display = 'block';
            } else if (this.value === 'vendor') {
                document.querySelector('.edit-vendor-section-' + taskId).style.display = 'block';
            }
        });
    });

    document.querySelectorAll('.edit-project-select').forEach(function(select) {
        select.addEventListener('change', function() {
            var taskId = this.getAttribute('data-task-id');
            var selected = this.options[this.selectedIndex];
            var locationField = document.querySelector('.edit-location-' + taskId);

            if (this.value && selected.dataset.address) {
                locationField.value = selected.dataset.address;
            }
        });
    });
    
    var addTaskForm = document.getElementById('addTaskForm');
    if (addTaskForm) {
        addTaskForm.addEventListener('submit', function(e) {
            var isValid = true;
            var assigneeType = document.getElementById('addAssigneeType');
            var startAt = document.getElementById('addStartAt');
            var title = document.getElementById('addTaskTitle');

            assigneeType.classList.remove('is-invalid');
            document.getElementById('addStaffId').classList.remove('is-invalid');
            document.getElementById('addVendorId').classList.remove('is-invalid');
            if (title) title.classList.remove('is-invalid');
            if (startAt) startAt.classList.remove('is-invalid');

            if (!title.value.trim()) {
                title.classList.add('is-invalid');
                isValid = false;
            }

            if (!startAt.value) {
                startAt.classList.add('is-invalid');
                isValid = false;
            }

            if (!assigneeType.value) {
                assigneeType.classList.add('is-invalid');
                isValid = false;
            } else if (assigneeType.value === 'staff') {
                var staffSelect = document.getElementById('addStaffId');
                if (!staffSelect.value) {
                    staffSelect.classList.add('is-invalid');
                    isValid = false;
                }
            } else if (assigneeType.value === 'vendor') {
                var vendorSelect = document.getElementById('addVendorId');
                if (!vendorSelect.value) {
                    vendorSelect.classList.add('is-invalid');
                    isValid = false;
                }
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    @if(($errors->any() || session('error')) && old('_token'))
        var addModalEl = document.getElementById('addTaskModal');
        if (addModalEl) {
            var addModal = new bootstrap.Modal(addModalEl);
            addModal.show();
        }
    @endif
});
</script>
@endpush
