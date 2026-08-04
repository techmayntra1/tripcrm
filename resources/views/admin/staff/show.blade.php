@extends('layouts.app')
@section('title', 'Staff Details - ' . $staff->name)
@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-person-badge-fill icon-gradient bg-deep-blue"></i>
            </div>
            <div title="{{ $staff->name }} - {{ $staff->position_name }}">
                {{ Str::limit($staff->name, 30) }} - {{ $staff->position_name }}

            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            @can('staff.edit')
            <a href="{{ route('admin.staff.edit', $staff) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            <a href="{{ route('admin.staff.salary-payments.index', $staff) }}" class="btn btn-success">
                <i class="bi bi-cash me-1"></i> Salary Payments
            </a>
            @endcan
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="main-card mb-3 card">
            <div class="card-body text-center">
                <div class="avatar-icon-wrapper mb-3" style="width: 100px; height: 100px; margin: 0 auto;">
                    <div class="avatar-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; font-size: 2.5rem;">
                        {{ strtoupper(substr($staff->name, 0, 1)) }}
                    </div>
                </div>
                <h4 class="mb-1">{{ $staff->name }}</h4>
                <span class="badge bg-primary mb-2">{{ $staff->position_name }}</span>
                <p class="text-muted mb-0"><i class="bi bi-telephone me-1"></i> {{ $staff->mobile }}</p>
                @if($staff->email)
                <p class="text-muted mb-0"><i class="bi bi-envelope me-1"></i> {{ $staff->email }}</p>
                @endif
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                @if($staff->trashed())
                    <span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i> Inactive</span>
                @else
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Active</span>
                @endif
                <h4 class="text-success mb-0">{{ formatMoney($staff->salary_amount) }}<small class="text-muted">/month</small></h4>
            </div>
        </div>
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-person-vcard me-2"></i> Personal Details
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Joining Date</small>
                    <strong>{{ $staff->joining_date ? $staff->joining_date->format('d-m-Y') : '-' }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Address</small>
                    <strong>{{ $staff->address ?: '-' }}</strong>
                </div>
            </div>
        </div>
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-bank me-2"></i> Bank Details
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Bank Name</small>
                    <strong>{{ $staff->bank_name ?: '-' }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Account Number</small>
                    <strong>{{ $staff->account_number ?: '-' }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">IFSC Code</small>
                    <strong>{{ $staff->ifsc_code ?: '-' }}</strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-clock-history me-2"></i> Recent Salary Records
            </div>
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                    <tbody>
                        @forelse($salaryRecords as $record)
                        @php
                            $isAdvance = str_contains($record->description, 'Advance');
                        @endphp
                        <tr>
                            <td>{{ formatDate($record->expense_date) }}</td>
                            <td>
                                @if($isAdvance)
                                    <span class="badge bg-warning">Advance</span>
                                @else
                                    <span class="badge bg-success">Salary</span>
                                @endif
                            </td>
                            <td>{{ $record->description }}</td>
                            <td class="text-end"><strong>{{ formatMoney($record->grand_total) }}</strong></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                                No salary records found
                            </td>
                        </tr>
                        @endforelse
                </tbody>
            </table>
        </div>
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-kanban me-2"></i> Assigned Projects
            </div>
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Project</th>
                        <th>Customer</th>
                        <th>Status</th>
                    </tr>
                </thead>
                    <tbody>
                        @forelse($projects as $project)
                        <tr>
                            <td>
                                <a href="{{ route('admin.projects.show', $project) }}">
                                    <strong>{{ $project->project_code }}</strong> - {{ $project->name }}
                                </a>
                            </td>
                            <td>{{ $project->customer?->name ?? '-' }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'planning' => 'secondary',
                                        'in_progress' => 'warning',
                                        'on_hold' => 'info',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$project->status] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">
                                <i class="bi bi-folder fs-1 d-block mb-2"></i>
                                No projects assigned
                            </td>
                        </tr>
                        @endforelse
                </tbody>
            </table>
        </div>
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-list-task me-2"></i> Assigned Tasks
                <span class="badge bg-warning text-dark ms-2">{{ $tasks->count() }}</span>
            </div>
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Project</th>
                        <th>Start Date</th>
                        <th>Due Date</th>
                        <th>Location</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                    <tr class="{{ $task->is_overdue ? 'table-danger' : '' }}">
                        <td><strong>{{ $task->title }}</strong></td>
                        <td>
                            @if($task->project)
                                <a href="{{ route('admin.projects.show', $task->project) }}">
                                    {{ $task->project->project_code }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $task->start_at ? $task->start_at->format('d-m-Y') : '-' }}</td>
                        <td>
                            {{ $task->due_at ? $task->due_at->format('d-m-Y') : '-' }}
                            @if($task->is_overdue)
                                <span class="badge bg-danger ms-1">Overdue</span>
                            @endif
                        </td>
                        <td>{{ $task->location ? Str::limit($task->location, 30) : '-' }}</td>
                        <td><span class="badge bg-{{ $task->status_color }}">{{ $task->status_name }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-list-task fs-1 d-block mb-2"></i>
                            No tasks assigned
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($staff->pan_card || $staff->aadhar_front || $staff->aadhar_back)
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-file-earmark-text me-2"></i> Documents
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-4">
                    @if($staff->pan_card)
                    <div class="text-center">
                        <small class="text-muted d-block mb-1">PAN Card</small>
                        <a href="{{ Storage::url($staff->pan_card) }}" target="_blank">
                            <img src="{{ Storage::url($staff->pan_card) }}" alt="PAN Card" class="img-thumbnail" style="max-height: 120px;">
                        </a>
                    </div>
                    @endif
                    @if($staff->aadhar_front)
                    <div class="text-center">
                        <small class="text-muted d-block mb-1">Aadhar Card (Front)</small>
                        <a href="{{ Storage::url($staff->aadhar_front) }}" target="_blank">
                            <img src="{{ Storage::url($staff->aadhar_front) }}" alt="Aadhar Front" class="img-thumbnail" style="max-height: 120px;">
                        </a>
                    </div>
                    @endif
                    @if($staff->aadhar_back)
                    <div class="text-center">
                        <small class="text-muted d-block mb-1">Aadhar Card (Back)</small>
                        <a href="{{ Storage::url($staff->aadhar_back) }}" target="_blank">
                            <img src="{{ Storage::url($staff->aadhar_back) }}" alt="Aadhar Back" class="img-thumbnail" style="max-height: 120px;">
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
