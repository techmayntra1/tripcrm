@extends('layouts.app')
@section('title', 'Customer Details')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-people-fill icon-gradient bg-happy-itmeo"></i>
            </div>
            <div title="{{ $customer->name }}">
                {{ Str::limit($customer->name, 30) }}

            </div>
        </div>
        <div class="page-title-actions">
            @if(!isset($isTrashed) || !$isTrashed)
            @can('customers.edit')
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-primary me-2">
                <i class="bi bi-pencil me-1"></i> Edit Customer
            </a>
            @endcan
            @else
            <span class="badge bg-danger me-2 py-2 px-3">
                <i class="bi bi-exclamation-triangle me-1"></i> Deleted Customer (Read Only)
            </span>
            @can('customers.delete')
            <form action="{{ route('admin.customers.restore', $customer->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success me-2">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Restore Customer
                </button>
            </form>
            @endcan
            @endif
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
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


<div class="summary-row mb-3">
    <div class="summary-item bg-success text-white" data-bs-toggle="tooltip" title="{{ formatMoney($customer->project_value) }}">
        <span class="summary-label">Project Value</span>
        <span class="summary-value">{{ formatMoney($customer->project_value) }}</span>
    </div>
    <div class="summary-item bg-info text-white" data-bs-toggle="tooltip" title="{{ formatMoney($customer->total_income) }}">
        <span class="summary-label">Total Income</span>
        <span class="summary-value">{{ formatMoney($customer->total_income) }}</span>
    </div>
    @if($customer->income_receivable < 0)
    <div class="summary-item bg-success text-white" data-bs-toggle="tooltip" title="{{ formatMoney(abs($customer->income_receivable)) }}">
        <span class="summary-label">Advance / Overpaid</span>
        <span class="summary-value">{{ formatMoney(abs($customer->income_receivable)) }}</span>
    </div>
    @else
    <div class="summary-item {{ $customer->income_receivable > 0 ? 'bg-danger' : 'bg-secondary' }} text-white" data-bs-toggle="tooltip" title="{{ formatMoney($customer->income_receivable) }}">
        <span class="summary-label">Receivable</span>
        <span class="summary-value">{{ formatMoney($customer->income_receivable) }}</span>
    </div>
    @endif
    <div class="summary-item bg-midnight-bloom text-white" data-bs-toggle="tooltip" title="{{ $customer->projects->count() }}">
        <span class="summary-label">Projects</span>
        <span class="summary-value">{{ $customer->projects->count() }}</span>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="main-card card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Upcoming Meetings</small>
                        <strong>{{ $upcomingMeetings->count() }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Customer Since</small>
                        <strong>{{ $customer->created_at->format('d-m-Y') }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Invoices</small>
                        <strong>{{ $customer->invoices->count() }} ({{ $customer->invoices->where('status', '!=', 'paid')->where('status', '!=', 'cancelled')->count() }} pending)</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Mobile</small>
                        <strong>{{ $customer->mobile ?? '-' }}</strong>
                    </div>
                </div>
                <hr class="my-2">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Email</small>
                        <strong>{{ $customer->email ?? '-' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">City</small>
                        <strong>{{ $customer->city_name }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Payment Type</small>
                        <strong>{{ ucfirst($customer->payment_type ?? '-') }}</strong>
                    </div>
                    @if($customer->work_type && is_array($customer->work_type))
                    <div class="col-md-3">
                        <small class="text-muted d-block">Work Type</small>
                        @foreach($customer->work_type as $wt)
                            <span class="badge bg-primary">{{ $wt }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
                @if($customer->gst_number || $customer->address)
                <hr class="my-2">
                <div class="row mb-3">
                    @if($customer->gst_number)
                    <div class="col-md-3">
                        <small class="text-muted d-block">GST Number</small>
                        <strong>{{ $customer->gst_number }}</strong>
                    </div>
                    @endif
                    @if($customer->address)
                    <div class="col-md-6">
                        <small class="text-muted d-block">Address</small>
                        <strong>{{ $customer->address }}</strong>
                    </div>
                    @endif
                </div>
                @endif
                @if($customer->lead)
                <hr class="my-2">
                <div class="row">
                    <div class="col-md-3">
                        <small class="text-muted d-block">From Lead</small>
                        <a href="{{ route('admin.leads.show', $customer->lead) }}"><strong>{{ $customer->lead->name }}</strong></a>
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
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-cash-coin me-2"></i> Income Ledger</span>
                <div class="d-flex align-items-center gap-2">
                    <select id="incomeProjectFilter" class="form-select form-select-sm" style="min-width: 200px;">
                        <option value="">All Projects</option>
                        @foreach($customer->projects as $project)
                            <option value="{{ $project->id }}">{{ $project->project_number }} - {{ Str::limit($project->name, 30) }}</option>
                        @endforeach
                    </select>
                    <a href="{{ route('admin.customers.income.export', $customer) }}" id="incomeExportBtn" class="btn btn-sm btn-success">
                        <i class="bi bi-download me-1"></i> Export
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="incomeLedgerTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Receipt #</th>
                            <th>Project</th>
                            <th>Type</th>
                            <th>Payment Mode</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incomes as $income)
                        <tr class="income-row" data-project-id="{{ $income->project_id }}" data-amount="{{ $income->amount }}">
                            <td>{{ optional($income->income_date)->format('d-m-Y') ?? '-' }}</td>
                            <td>{{ $income->receipt_number ?? '-' }}</td>
                            <td>{{ $income->project->name ?? '-' }}</td>
                            <td>{{ $income->income_type_display }}</td>
                            <td>{{ $income->paymentMode->name ?? '-' }}</td>
                            <td>{{ $income->description ?? '-' }}</td>
                            <td class="text-end">{{ formatMoney($income->amount) }}</td>
                        </tr>
                        @empty
                        <tr id="incomeEmptyRow">
                            <td colspan="7" class="text-center text-muted py-3">No income records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr id="incomeNoMatchRow" style="display: none;">
                            <td colspan="7" class="text-center text-muted py-3">No income records for the selected project.</td>
                        </tr>
                        <tr class="fw-bold">
                            <td colspan="6" class="text-end">Total</td>
                            <td class="text-end" id="incomeTotal">{{ formatMoney($incomes->sum('amount')) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filter = document.getElementById('incomeProjectFilter');
    const exportBtn = document.getElementById('incomeExportBtn');
    const rows = document.querySelectorAll('#incomeLedgerTable tbody tr.income-row');
    const totalCell = document.getElementById('incomeTotal');
    const noMatchRow = document.getElementById('incomeNoMatchRow');
    const exportBaseUrl = exportBtn ? exportBtn.getAttribute('href') : null;

    function formatMoneyJs(value) {
        return '₹' + Number(value).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function applyFilter() {
        const selected = filter.value;
        let total = 0;
        let visibleCount = 0;

        rows.forEach(function (row) {
            const match = !selected || row.dataset.projectId === selected;
            row.style.display = match ? '' : 'none';
            if (match) {
                total += parseFloat(row.dataset.amount) || 0;
                visibleCount++;
            }
        });

        if (totalCell) totalCell.textContent = formatMoneyJs(total);
        if (noMatchRow) noMatchRow.style.display = (rows.length > 0 && visibleCount === 0) ? '' : 'none';

        if (exportBtn && exportBaseUrl) {
            exportBtn.setAttribute('href', selected ? exportBaseUrl + '?project_id=' + selected : exportBaseUrl);
        }
    }

    if (filter) {
        filter.addEventListener('change', applyFilter);
    }
});
</script>
@endpush
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
                            <th style="white-space: nowrap">Purpose</th>
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
                            <td colspan="{{ (!isset($isTrashed) || !$isTrashed) ? 7 : 5 }}" class="text-center py-4 text-muted">No upcoming meetings scheduled.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        
        <div class="main-card mb-3 card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-receipt me-2"></i> Invoices</span>
                @if(!isset($isTrashed) || !$isTrashed)
                @can('invoices.create')
                <a href="{{ route('admin.invoices.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-success">
                    <i class="bi bi-plus-lg me-1"></i> Create Invoice
                </a>
                @endcan
                @endif
            </div>
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Received</th>
                        <th class="text-end">Balance</th>
                        <th>Status</th>
                        <th width="80">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customer->invoices->sortByDesc('date') as $invoice)
                    <tr>
                        <td><a href="{{ route('admin.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                        <td>{{ formatDate($invoice->date) }}</td>
                        <td class="text-end">{{ formatMoney($invoice->grand_total) }}</td>
                        <td class="text-end text-success">{{ formatMoney($invoice->amount_paid) }}</td>
                        <td class="text-end {{ $invoice->balance_due > 0 ? 'text-danger' : '' }}">{{ formatMoney($invoice->balance_due) }}</td>
                        <td>
                            @php
                            $statusColors = ['sent' => 'info', 'partial' => 'warning', 'paid' => 'success', 'overdue' => 'danger', 'cancelled' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$invoice->status] ?? 'secondary' }}">{{ ucfirst($invoice->status) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-info" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No invoices found.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($customer->invoices->count() > 0)
                <tfoot class="table-dark">
                    <tr>
                        @php
                            $invoiceTotal = $customer->invoices->sum('grand_total');
                            $invoiceReceived = $customer->invoices->sum('amount_paid');
                            $invoiceBalance = $customer->invoices->sum('balance_due');
                        @endphp
                        <td colspan="2" class="text-end"><strong>Total</strong></td>
                        <td class="text-end"><strong>{{ formatMoney($invoiceTotal) }}</strong></td>
                        <td class="text-end text-success"><strong>{{ formatMoney($invoiceReceived) }}</strong></td>
                        <td class="text-end {{ $invoiceBalance > 0 ? 'text-warning' : '' }}"><strong>{{ formatMoney($invoiceBalance) }}</strong></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <div class="main-card mb-3 card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-kanban me-2"></i> Projects</span>
                @if(!isset($isTrashed) || !$isTrashed)
                @can('projects.create')
                <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addProjectModal" style="color: #ffffff !important">
                    <i class="bi bi-plus-lg me-1"></i> Add Project
                </button>
                @endcan
                @endif
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="250">Project</th>
                            <th width="120">Budget</th>
                            <th width="120">Status</th>
                            <th width="90">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customer->projects as $project)
                        <tr class="project-row" data-href="{{ route('admin.projects.show', $project->id) }}" style="cursor: pointer;">
                            <td>
                                <strong>{{ $project->project_number }}</strong>
                                <br><small class="text-muted text-truncate-cell" title="{{ $project->name }}" style="max-width: 200px;">{{ Str::limit($project->name, 30) }}</small>
                            </td>
                            <td>{{ formatMoney($project->budget) }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'planning' => 'bg-secondary',
                                        'in_progress' => 'bg-warning',
                                        'on_hold' => 'bg-info',
                                        'completed' => 'bg-success',
                                        'cancelled' => 'bg-danger',
                                    ];
                                @endphp
                                <span class="badge {{ $statusColors[$project->status] ?? 'bg-secondary' }}">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span>
                            </td>
                            <td class="action-buttons" onclick="event.stopPropagation();">
                                <a href="{{ route('admin.projects.show', $project->id) }}" class="btn btn-sm btn-outline-info" title="Details">
                                    <i class="bi bi-info-circle"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No projects found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-list-task me-2"></i> Tasks
                <span class="badge bg-warning text-dark ms-2">{{ $tasks->count() }}</span>
            </div>
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Project</th>
                        <th>Assigned To</th>
                        <th>Start Date</th>
                        <th>Due Date</th>
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
                        <td>
                            {{ $task->assignee_name }}
                            @if($task->assignee_type)
                                <small class="text-muted">({{ ucfirst($task->assignee_type) }})</small>
                            @endif
                        </td>
                        <td>{{ $task->start_at ? $task->start_at->format('d-m-Y') : '-' }}</td>
                        <td>
                            {{ $task->due_at ? $task->due_at->format('d-m-Y') : '-' }}
                            @if($task->is_overdue)
                                <span class="badge bg-danger ms-1">Overdue</span>
                            @endif
                        </td>
                        <td><span class="badge bg-{{ $task->status_color }}">{{ $task->status_name }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-list-task fs-1 d-block mb-2"></i>
                            No tasks found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="main-card mb-3 card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2"></i> Activity Timeline</span>
                @if(!isset($isTrashed) || !$isTrashed)
                @can('customers.edit')
                <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addUpdateModal" style="color: #ffffff !important">
                    <i class="bi bi-plus-lg me-1"></i> Add Update
                </button>
                @endcan
                @endif
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Follow-up</th>
                            <th>Type</th>
                            <th>Details</th>
                            <th>Added By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                            @if($activity['type'] === 'update')
                                @php $update = $activity['data']; @endphp
                                <tr>
                                    <td>{{ $update->follow_up_date ? formatDate($update->follow_up_date, true) : '-' }}</td>
                                    <td><span class="badge bg-{{ $update->updateType->color ?? 'primary' }}"><i class="bi {{ $update->updateType->icon ?? 'bi-sticky' }} me-1"></i>{{ $update->updateType->name ?? 'Update' }}</span></td>
                                    <td><span class="text-truncate-cell" title="{{ $update->notes }}">{{ Str::limit($update->notes, 50) }}</span></td>
                                    <td><span class="text-truncate-cell" title="{{ $update->user->name ?? 'System' }}">{{ Str::limit($update->user->name ?? 'System', 15) }}</span></td>
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
            <form action="{{ route('admin.customers.meetings.store', $customer) }}" method="POST">
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
                        <label class="form-label">Customer</label>
                        <div class="form-control bg-light">{{ $customer->name }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label">Meeting Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" id="customerMeetingTitle" value="{{ old('title') }}" placeholder="e.g., Quotation Discussion" maxlength="40">
                        <div class="invalid-feedback">Please enter meeting title</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="meeting_at" class="form-label">Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="meeting_at" id="customerMeetingDatetime" value="{{ old('meeting_at', date('Y-m-d') . 'T10:00') }}">
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
                        <input type="text" class="form-control" name="location" id="location" value="{{ old('location') }}" placeholder="e.g., Office, Video Call" maxlength="20">
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

<div class="modal fade" id="addProjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-kanban me-2"></i>Add Project</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.customers.projects.link', $customer) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Customer</label>
                        <div class="form-control bg-light">{{ $customer->name }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="project_id" class="form-label">Select Project <span class="text-danger">*</span></label>
                        <select class="form-select" name="project_id" id="project_id" required>
                            <option value="">Select a Project</option>
                            @foreach($availableProjects ?? [] as $project)
                                <option value="{{ $project->id }}">{{ $project->project_number }} - {{ $project->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select an existing project to link to this customer</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-link me-1"></i> Link Project</button>
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
                            <strong>{{ $customer->name }}</strong> (Customer)<br>
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
                <input type="hidden" name="meeting_with" value="customer">
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Customer</label>
                        <div class="form-control bg-light">{{ $customer->name }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_title{{ $meeting->id }}" class="form-label">Meeting Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control customer-edit-title" name="title" id="edit_title{{ $meeting->id }}" value="{{ $meeting->title }}" maxlength="40">
                        <div class="invalid-feedback">Please enter meeting title</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_meeting_at{{ $meeting->id }}" class="form-label">Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control customer-edit-datetime" name="meeting_at" id="edit_meeting_at{{ $meeting->id }}" value="{{ $meeting->meeting_at->format('Y-m-d\TH:i') }}">
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
                            <strong>{{ $customer->name }}</strong> (Customer)<br>
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
            <form action="{{ route('admin.customers.updates.store', $customer) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="update_type_id" class="form-label">Update Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="update_type_id" id="update_type_id" required>
                            <option value="">Select Type</option>
                            @foreach($updateTypes as $type)
                                <option value="{{ $type->id }}" data-icon="{{ $type->icon }}" data-color="{{ $type->color }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="follow_up_date" class="form-label">Next Follow-up Date</label>
                        <input type="datetime-local" class="form-control" name="follow_up_date" id="follow_up_date" placeholder="Select date and time">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes / Update Details <span class="text-danger">*</span> <small class="text-muted">(max 150 chars)</small></label>
                        <textarea class="form-control" name="notes" id="notes" rows="3" maxlength="150" placeholder="Enter update details..." required></textarea>
                        <small class="text-muted"><span id="notesCharCount">0</span>/150</small>
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
@push('styles')
<style>
.summary-row {
    display: flex;
    gap: 0.5rem;
}
.summary-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 0.5rem;
    cursor: pointer;
    flex: 1;
    border-radius: 6px;
    color: #fff;
    text-align: center;
}
.summary-item:hover {
    opacity: 0.9;
}
.summary-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #fff !important;
}
.summary-value {
    font-size: 1.1rem;
    font-weight: 700;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #fff !important;
}
@media (max-width: 768px) {
    .summary-row { flex-wrap: wrap; }
    .summary-item { min-width: 45%; }
    .summary-value { font-size: 0.95rem; }
}
.text-truncate-cell {
    display: inline-block;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    vertical-align: middle;
}
.table td {
    vertical-align: middle;
}
.project-row:hover {
    background-color: rgba(0,0,0,.075) !important;
    --bs-table-hover-bg: rgba(0,0,0,.075) !important;
    --bs-table-accent-bg: rgba(0,0,0,.075) !important;
}
.project-row:hover td {
    background-color: transparent !important;
}
.project-row:hover td,
.project-row:hover td *,
.project-row:hover td strong,
.project-row:hover td small,
.project-row:hover td .text-muted,
.project-row:hover td .badge {
    color: inherit !important;
}
.table-hover tbody tr.project-row:hover {
    --bs-table-hover-color: inherit !important;
}
</style>
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    document.querySelectorAll('.project-row').forEach(function(row) {
        row.addEventListener('click', function() {
            window.location.href = this.dataset.href;
        });
    });

    var notesTextarea = document.getElementById('notes');
    var notesCharCount = document.getElementById('notesCharCount');
    if (notesTextarea && notesCharCount) {
        notesCharCount.textContent = notesTextarea.value.length;
        notesTextarea.addEventListener('input', function() {
            notesCharCount.textContent = this.value.length;
        });
        notesTextarea.addEventListener('keyup', function() {
            notesCharCount.textContent = this.value.length;
        });
    }

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
            var title = document.getElementById('customerMeetingTitle');
            var datetime = document.getElementById('customerMeetingDatetime');

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
            var title = form.querySelector('.customer-edit-title');
            var datetime = form.querySelector('.customer-edit-datetime');

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
