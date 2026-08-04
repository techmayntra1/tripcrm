@extends('layouts.app')
@section('title', 'Task Details')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-list-task icon-gradient bg-mean-fruit"></i>
            </div>
            <div title="{{ $task->title }}">
                {{ Str::limit($task->title, 30) }}
            </div>
        </div>
        <div class="page-title-actions">
            @if(!isset($isTrashed) || !$isTrashed)
            @can('tasks.edit')
            <button class="btn btn-primary me-2" type="button" data-bs-toggle="modal" data-bs-target="#editTaskModal">
                <i class="bi bi-pencil me-1"></i> Edit Task
            </button>
            <button class="btn btn-success me-2" type="button" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                <i class="bi bi-arrow-repeat me-1"></i> Update Status
            </button>
            @endcan
            @else
            <span class="badge bg-danger me-2 py-2 px-3">
                <i class="bi bi-exclamation-triangle me-1"></i> Deleted Task (Read Only)
            </span>
            @can('tasks.delete')
            <form action="{{ route('admin.tasks.reactivate', $task->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success me-2">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Restore Task
                </button>
            </form>
            @endcan
            @endif
            <a href="{{ $backUrl ?? route('admin.tasks.index') }}" class="btn btn-outline-secondary">
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
                        <span class="badge bg-{{ $task->status_color }}">{{ Str::limit($task->status_name, 15) }}</span>
                        @if($task->is_overdue)
                        <span class="badge bg-danger ms-1">Overdue</span>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Assignee</small>
                        <strong>{{ $task->assignee_name }}</strong>
                        <small class="text-muted">({{ ucfirst($task->assignee_type) }})</small>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Start Date</small>
                        <strong>{{ $task->start_at->format('d-m-Y h:i A') }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Due Date</small>
                        <strong>{{ $task->due_at ? $task->due_at->format('d-m-Y') : '-' }}</strong>
                    </div>
                </div>
                <hr class="my-2">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Project</small>
                        @if($task->project)
                        <a href="{{ route('admin.projects.show', $task->project) }}">{{ $task->project->name }}</a>
                        @else
                        <strong>-</strong>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Location</small>
                        <strong>{{ $task->location ?? '-' }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Description</small>
                        <strong>{{ $task->description ?? '-' }}</strong>
                    </div>
                </div>
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
                    @forelse($task->updates as $update)
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
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-arrow-repeat me-2"></i>Update Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.tasks.status', $task) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Status</label>
                        <div class="form-control bg-light">
                            <span class="badge bg-{{ $task->status_color }}">{{ $task->status_name }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="new_status_id" class="form-label">New Status <span class="text-danger">*</span></label>
                        <select class="form-select" name="status_id" id="new_status_id" required>
                            <option value="">Select Status</option>
                            @foreach($taskStatuses as $status)
                            <option value="{{ $status->id }}" {{ $task->status_id == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="due_at" class="form-label">Update Due Date</label>
                        <input type="date" class="form-control" name="due_at" id="due_at" value="{{ $task->due_at ? $task->due_at->format('Y-m-d') : '' }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info"><i class="bi bi-check-lg me-1"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editTaskModal" tabindex="-1">
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
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" id="title" value="{{ $task->title }}" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="description" rows="2" maxlength="200">{{ $task->description }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="project_id" class="form-label">Project</label>
                            <select class="form-select" name="project_id" id="project_id">
                                <option value="">Select Project</option>
                                @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ $task->project_id == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" id="location" value="{{ $task->location }}" maxlength="255">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="assignee_type" class="form-label">Assignee Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="assignee_type" id="assignee_type" required>
                                <option value="staff" {{ $task->assignee_type == 'staff' ? 'selected' : '' }}>Staff</option>
                                <option value="vendor" {{ $task->assignee_type == 'vendor' ? 'selected' : '' }}>Vendor</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3" id="staffSelectContainer" style="{{ $task->assignee_type == 'vendor' ? 'display:none' : '' }}">
                            <label for="staff_id" class="form-label">Staff Member <span class="text-danger">*</span></label>
                            <select class="form-select" name="staff_id" id="staff_id">
                                <option value="">Select Staff</option>
                                @foreach($staffMembers as $staff)
                                <option value="{{ $staff->id }}" {{ $task->assignee_type == 'staff' && $task->assignee_id == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3" id="vendorSelectContainer" style="{{ $task->assignee_type == 'staff' ? 'display:none' : '' }}">
                            <label for="vendor_id" class="form-label">Vendor <span class="text-danger">*</span></label>
                            <select class="form-select" name="vendor_id" id="vendor_id">
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ $task->assignee_type == 'vendor' && $task->assignee_id == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="start_at" class="form-label">Start Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="start_at" id="start_at" value="{{ $task->start_at->format('Y-m-d\TH:i') }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_due_at" class="form-label">Due Date & Time</label>
                            <input type="datetime-local" class="form-control" name="due_at" id="edit_due_at" value="{{ $task->due_at ? $task->due_at->format('Y-m-d\TH:i') : '' }}" min="{{ $task->start_at->format('Y-m-d\TH:i') }}">
                        </div>
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

@foreach($task->updates as $update)
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
    var assigneeType = document.getElementById('assignee_type');
    var staffContainer = document.getElementById('staffSelectContainer');
    var vendorContainer = document.getElementById('vendorSelectContainer');

    if (assigneeType) {
        assigneeType.addEventListener('change', function() {
            if (this.value === 'staff') {
                staffContainer.style.display = '';
                vendorContainer.style.display = 'none';
            } else {
                staffContainer.style.display = 'none';
                vendorContainer.style.display = '';
            }
        });
    }
    
    var startAt = document.getElementById('start_at');
    var dueAt = document.getElementById('edit_due_at');
    if (startAt && dueAt) {
        startAt.addEventListener('change', function() {
            dueAt.min = this.value;
            if (dueAt.value && dueAt.value < this.value) {
                dueAt.value = this.value;
            }
        });
    }
});
</script>
@endpush
