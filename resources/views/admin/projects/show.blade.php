@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.app')
@section('title', 'Trip Details')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-kanban icon-gradient bg-strong-bliss"></i>
            </div>
            <div title="{{ $project->project_number }} - {{ $project->name }}">
                {{ $project->project_number }} - {{ Str::limit($project->name, 30) }}

            </div>
        </div>
        <div class="page-title-actions">
            @if($project->customer_id && str_contains(url()->previous(), '/customers/'))
                <a href="{{ route('admin.customers.show', $project->customer_id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Customer
                </a>
            @else
                <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            @endif
            <a href="{{ route('admin.projects.export', $project) }}" class="btn btn-success">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </a>
            @can('projects.edit')
            <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            @endcan
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i> Trip Info
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <small class="text-muted d-block">Trip Number</small>
                        <strong>{{ $project->project_number }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Client</small>
                        @if($project->customer)
                            <strong>{{ $project->customer->name }}</strong> <small class="text-muted">{{ $project->customer->mobile }}</small>
                        @else
                            <strong>-</strong>
                        @endif
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">Work Type</small>
                        @if($project->work_type && is_array($project->work_type))
                            @foreach($project->work_type as $type)
                                <span class="badge bg-primary">{{ $type }}</span>
                            @endforeach
                        @else
                            -
                        @endif
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">Status</small>
                        @php
                            $statusColors = [
                                'planning' => 'bg-secondary',
                                'in_progress' => 'bg-warning',
                                'on_hold' => 'bg-info',
                                'completed' => 'bg-success',
                                'cancelled' => 'bg-danger',
                            ];
                            $statusLabels = [
                                'planning' => 'Planning',
                                'in_progress' => 'In Progress',
                                'on_hold' => 'On Hold',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ];
                        @endphp
                        <span class="badge {{ $statusColors[$project->status] ?? 'bg-secondary' }}">{{ $statusLabels[$project->status] ?? ucfirst($project->status) }}</span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Timeline</small>
                        <strong>
                            {{ $project->start_date ? formatDate($project->start_date) : 'Not set' }}
                            -
                            {{ $project->expected_end_date ? formatDate($project->expected_end_date) : 'Not set' }}
                        </strong>
                    </div>
                </div>
                <hr class="my-2">
                <div class="row">
                    <div class="col-md-2">
                        <small class="text-muted d-block">Company</small>
                        <strong>{{ $project->company->name ?? '-' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Site Address</small>
                        <strong>{{ $project->site_address ?? '-' }}</strong>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">Assigned Staff</small>
                        @if($project->assigned_staff_ids && count($project->assigned_staff_ids) > 0)
                            @foreach($project->assigned_staff_list as $staffMember)
                                <span class="badge bg-info">{{ $staffMember->name }}</span>
                            @endforeach
                        @else
                            <strong>-</strong>
                        @endif
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">Assigned Vendors</small>
                        @if($project->assigned_vendor_ids && count($project->assigned_vendor_ids) > 0)
                            @foreach($project->assigned_vendor_list as $vendor)
                                <span class="badge bg-secondary">{{ $vendor->name }}</span>
                            @endforeach
                        @else
                            <strong>-</strong>
                        @endif
                    </div>
                </div>
                <hr class="my-2">
                <div class="row text-center">
                    <div class="col-3">
                        <small class="text-muted d-block">Budget</small>
                        <strong class="text-primary">{{ formatMoney($project->budget, true) }}</strong>
                    </div>
                    <div class="col-3">
                        <small class="text-muted d-block">Total Spent</small>
                        <strong class="text-danger">{{ formatMoney($project->total_spent, true) }}</strong>
                    </div>
                    <div class="col-3">
                        <small class="text-muted d-block">Income</small>
                        <strong class="text-success">{{ formatMoney($project->total_income, true) }}</strong>
                    </div>
                    <div class="col-3">
                        <small class="text-muted d-block">Profit</small>
                        <strong class="{{ $project->profit >= 0 ? 'text-success' : 'text-danger' }}">{{ formatMoney($project->profit, true) }}</strong>
                    </div>
                </div>
                @if($project->gst_percent > 0 && $project->gst_amount > 0)
                <hr class="my-2">
                <div class="row text-center">
                    <div class="col-3">
                        <small class="text-muted d-block">GST ({{ rtrim(rtrim(number_format($project->gst_percent, 2), '0'), '.') }}%){{ $project->gst_inclusive ? ' incl.' : '' }}</small>
                        <strong class="text-info">{{ formatMoney($project->gst_amount, true) }}</strong>
                    </div>
                    <div class="col-3">
                        <small class="text-muted d-block">Total with GST</small>
                        <strong class="text-primary">{{ formatMoney($project->total_with_gst, true) }}</strong>
                    </div>
                    @if($project->gst_split)
                    <div class="col-3">
                        <small class="text-muted d-block">CGST</small>
                        <strong>{{ formatMoney($project->gst_amount / 2, true) }}</strong>
                    </div>
                    <div class="col-3">
                        <small class="text-muted d-block">SGST</small>
                        <strong>{{ formatMoney($project->gst_amount / 2, true) }}</strong>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@if($project->files->count() > 0)
<div class="row">
    <div class="col-md-12">
        <div class="main-card mb-3 card animated fadeInUp">
            <div class="card-header py-2">
                <i class="bi bi-folder me-2"></i> Trip Files ({{ $project->files->count() }})
            </div>
            <div class="card-body py-2">
                <div class="d-flex flex-wrap gap-2">
                    @foreach($project->files as $file)
                    @php
                        $icon = 'bi-file-earmark';
                        if (str_contains($file->mime_type, 'pdf')) $icon = 'bi-file-earmark-pdf text-danger';
                        elseif (str_contains($file->mime_type, 'image')) $icon = 'bi-file-earmark-image text-success';
                        elseif (str_contains($file->mime_type, 'word') || str_contains($file->original_name, '.doc')) $icon = 'bi-file-earmark-word text-primary';
                        elseif (str_contains($file->mime_type, 'excel') || str_contains($file->mime_type, 'spreadsheet') || str_contains($file->original_name, '.xls')) $icon = 'bi-file-earmark-excel text-success';
                    @endphp
                    <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="btn btn-outline-secondary btn-sm" title="{{ $file->original_name }}">
                        <i class="bi {{ $icon }} me-1"></i> {{ Str::limit($file->original_name, 25) }}
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif
<div class="row">
    <div class="col-md-12">
        <div class="main-card mb-3 card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <ul class="nav nav-tabs card-header-tabs project-tabs" id="projectTabs">
                    <li class="nav-item">
                        <button class="nav-link {{ request('tab') != 'addons' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#expenses" data-tab="expenses">
                            <i class="bi bi-graph-down-arrow me-1"></i> Expenses ({{ $project->expenses->count() }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#income" data-tab="income">
                            <i class="bi bi-graph-up-arrow me-1"></i> Income ({{ $project->incomes->count() }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#invoices" data-tab="invoices">
                            <i class="bi bi-receipt me-1"></i> Invoices ({{ $project->invoices->count() }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {{ request('tab') == 'addons' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#addons" data-tab="addons">
                            <i class="bi bi-plus-circle me-1"></i> Add-Ons ({{ $project->addons->count() }})
                        </button>
                    </li>
                </ul>
                <div class="tab-add-buttons">
                    @can('expenses.create')
                    <a href="{{ route('admin.expenses.create') }}?project_id={{ $project->id }}" class="btn btn-sm btn-primary tab-add-btn" data-for="expenses">
                        <i class="bi bi-plus-lg"></i> Add Expense
                    </a>
                    @endcan
                    @can('income.create')
                    <a href="{{ route('admin.income.create') }}?project_id={{ $project->id }}" class="btn btn-sm btn-success tab-add-btn" data-for="income" style="display: none;">
                        <i class="bi bi-plus-lg"></i> Add Income
                    </a>
                    @endcan
                    @can('invoices.create')
                    <a href="{{ route('admin.invoices.create') }}?project_id={{ $project->id }}" class="btn btn-sm btn-info tab-add-btn" data-for="invoices" style="display: none;">
                        <i class="bi bi-plus-lg"></i> Create Invoice
                    </a>
                    @endcan
                    @can('projects.edit')
                    <a href="#" class="btn btn-sm btn-primary tab-add-btn" data-for="addons" data-bs-toggle="modal" data-bs-target="#addAddonModal" style="{{ request('tab') == 'addons' ? '' : 'display: none;' }}">
                        <i class="bi bi-plus-lg"></i> Add Add-On
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane fade {{ request('tab') != 'addons' ? 'show active' : '' }}" id="expenses">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Vendor</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Unpaid</th>
                                    <th class="text-center">Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                                <tbody>
                                    @php $nonServiceExpenses = $project->expenses->where('expense_type', '!=', 'service'); @endphp
                                    @forelse($nonServiceExpenses->sortByDesc('expense_date') as $expense)
                                    <tr>
                                        <td>{{ formatDate($expense->expense_date) }}</td>
                                        <td>
                                            @if($expense->category)
                                                <span class="badge bg-primary">{{ $expense->category->name }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($expense->vendor)
                                                <a href="{{ route('admin.vendors.show', $expense->vendor) }}">{{ $expense->vendor->name }}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-end text-danger">{{ formatMoney($expense->grand_total) }}</td>
                                        <td class="text-end text-success">{{ formatMoney($expense->paid_amount) }}</td>
                                        <td class="text-end {{ $expense->balance > 0 ? 'text-danger' : '' }}">{{ formatMoney($expense->balance) }}</td>
                                        <td class="text-center">
                                            @if($expense->payment_status == 'paid')
                                                <span class="badge bg-success">Paid</span>
                                            @elseif($expense->payment_status == 'partial')
                                                <span class="badge bg-warning">Partial</span>
                                            @else
                                                <span class="badge bg-danger">Unpaid</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group-actions">
                                                @can('expenses.edit')
                                                @if($expense->balance > 0)
                                                <button type="button" class="btn btn-sm btn-outline-success" title="Record Payment" data-bs-toggle="modal" data-bs-target="#projectExpensePaymentModal{{ $expense->id }}">
                                                    <i class="bi bi-cash"></i>
                                                </button>
                                                @endif
                                                @if($expense->payment_status !== 'paid')
                                                <a href="{{ route('admin.expenses.edit', $expense) }}?from_project={{ $project->id }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @endif
                                                @endcan
                                                <a href="{{ route('admin.expenses.show', $expense) }}?from_project={{ $project->id }}" class="btn btn-sm btn-outline-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @can('expenses.delete')
                                                <form action="{{ route('admin.expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this expense?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="from_project" value="{{ $project->id }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="bi bi-archive"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No expenses recorded</td>
                                    </tr>
                                    @endforelse
                                    @if($nonServiceExpenses->count() > 0)
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total</strong></td>
                                        <td class="text-end text-danger"><strong>{{ formatMoney($nonServiceExpenses->sum('grand_total')) }}</strong></td>
                                        <td class="text-end text-success"><strong>{{ formatMoney($nonServiceExpenses->sum('paid_amount')) }}</strong></td>
                                        <td class="text-end {{ $nonServiceExpenses->sum('grand_total') - $nonServiceExpenses->sum('paid_amount') > 0 ? 'text-danger' : '' }}"><strong>{{ formatMoney($nonServiceExpenses->sum('grand_total') - $nonServiceExpenses->sum('paid_amount')) }}</strong></td>
                                        <td colspan="2"></td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                    </div>
                    <div class="tab-pane fade" id="income">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Payment Mode</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                                <tbody>
                                    @forelse($project->incomes->sortByDesc('income_date') as $income)
                                    <tr>
                                        <td>{{ formatDate($income->income_date) }}</td>
                                        <td>
                                            @if($income->income_type)
                                                <span class="badge bg-primary">{{ ucfirst($income->income_type) }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $income->description ?? '-' }}</td>
                                        <td>{{ $income->payment_mode ?? '-' }}</td>
                                        <td class="text-success">{{ formatMoney($income->amount) }}</td>
                                        <td>
                                            <div class="btn-group-actions">
                                                @can('income.edit')
                                                <a href="{{ route('admin.income.edit', $income) }}?from_project={{ $project->id }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @endcan
                                                <a href="{{ route('admin.income.show', $income) }}?from_project={{ $project->id }}" class="btn btn-sm btn-outline-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @can('income.delete')
                                                <form action="{{ route('admin.income.destroy', $income) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this income?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="from_project" value="{{ $project->id }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="bi bi-archive"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No income recorded</td>
                                    </tr>
                                    @endforelse
                                    @if($project->incomes->count() > 0)
                                    <tr class="table-success">
                                        <td colspan="4"><strong>Total Income</strong></td>
                                        <td class="text-success"><strong>{{ formatMoney($project->total_income) }}</strong></td>
                                        <td></td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                    </div>
                    <div class="tab-pane fade" id="invoices">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                                <tbody>
                                    @forelse($project->invoices->sortByDesc('invoice_date') as $invoice)
                                    <tr>
                                        <td><strong>{{ $invoice->invoice_number }}</strong></td>
                                        <td>{{ formatDate($invoice->invoice_date) }}</td>
                                        <td>{{ $invoice->due_date ? formatDate($invoice->due_date) : '-' }}</td>
                                        <td>
                                            @php
                                                $invoiceStatusColors = [
                                                    'draft' => 'bg-secondary',
                                                    'sent' => 'bg-primary',
                                                    'paid' => 'bg-success',
                                                    'overdue' => 'bg-danger',
                                                    'cancelled' => 'bg-dark',
                                                ];
                                            @endphp
                                            <span class="badge {{ $invoiceStatusColors[$invoice->status] ?? 'bg-secondary' }}">{{ ucfirst($invoice->status) }}</span>
                                        </td>
                                        <td class="text-end">{{ formatMoney($invoice->total_amount) }}</td>
                                        <td>
                                            <div class="btn-group-actions">
                                                @can('invoices.edit')
                                                @if($invoice->status !== 'paid')
                                                <a href="{{ route('admin.invoices.edit', $invoice) }}?from_project={{ $project->id }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @endif
                                                @endcan
                                                <a href="{{ route('admin.invoices.show', $invoice) }}?from_project={{ $project->id }}" class="btn btn-sm btn-outline-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @can('invoices.delete')
                                                <form action="{{ route('admin.invoices.destroy', $invoice) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this invoice?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="from_project" value="{{ $project->id }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="bi bi-archive"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No invoices created</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                    </div>
                    <div class="tab-pane fade {{ request('tab') == 'addons' ? 'show active' : '' }}" id="addons">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Requested</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($project->addons->sortByDesc('created_at') as $addon)
                                <tr>
                                    <td><strong>{{ $addon->title }}</strong></td>
                                    <td>{{ Str::limit($addon->description, 50) ?? '-' }}</td>
                                    <td class="text-info">{{ formatMoney($addon->amount) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $addon->status_badge }}">{{ ucfirst($addon->status) }}</span>
                                    </td>
                                    <td>{{ $addon->requested_date ? formatDate($addon->requested_date) : '-' }}</td>
                                    <td>
                                        <div class="btn-group-actions">
                                            @if(auth()->user()->hasPermission('projects', 'edit'))
                                            <button type="button" class="btn btn-sm btn-outline-primary" title="Edit"
                                                data-bs-toggle="modal" data-bs-target="#editAddonModal{{ $addon->id }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('admin.projects.addons.destroy', [$project, $addon]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this add-on?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No add-ons recorded</td>
                                </tr>
                                @endforelse
                                @if($project->addons->count() > 0)
                                <tr>
                                    <td colspan="2"><strong>Total Add-Ons</strong></td>
                                    <td class="text-info"><strong>{{ formatMoney($project->add_on_total) }}</strong></td>
                                    <td colspan="3"></td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-list-task me-2"></i> Tasks
                <span class="badge bg-warning text-dark ms-2">{{ $tasks->count() }}</span>
            </div>
            <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Assigned To</th>
                        <th style="white-space: nowrap">Start Date</th>
                        <th style="white-space: nowrap">Due Date</th>
                        <th>Location</th>
                        <th style="white-space: nowrap">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                    <tr class="{{ $task->is_overdue ? 'table-danger' : '' }}">
                        <td><a href="{{ route('admin.tasks.show', $task) }}"><strong class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $task->title }}">{{ $task->title }}</strong></a></td>
                        <td style="white-space: nowrap">
                            {{ $task->assignee_name }}
                            @if($task->assignee_type)
                                <small class="text-muted">({{ ucfirst($task->assignee_type) }})</small>
                            @endif
                        </td>
                        <td style="white-space: nowrap">{{ $task->start_at ? $task->start_at->format('d-m-Y') : '-' }}</td>
                        <td style="white-space: nowrap">
                            {{ $task->due_at ? $task->due_at->format('d-m-Y') : '-' }}
                            @if($task->is_overdue)
                                <span class="badge bg-danger ms-1">Overdue</span>
                            @endif
                        </td>
                        <td><span class="text-truncate d-inline-block" style="max-width: 120px;" title="{{ $task->location }}">{{ $task->location ? Str::limit($task->location, 30) : '-' }}</span></td>
                        <td style="white-space: nowrap"><span class="badge bg-{{ $task->status_color }}">{{ $task->status_name }}</span></td>
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
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="main-card mb-3 card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-tools me-2"></i> Services
                    <span class="badge bg-info ms-2">{{ $project->projectServices->count() }}</span>
                </span>
                @can('projects.edit')
                <a href="#" class="btn btn-sm btn-warning" style="text-transform: none;" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Service
                </a>
                @endcan
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Services</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Pending</th>
                            <th>Due Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($project->projectServices->sortByDesc('created_at') as $projectService)
                        <tr>
                            <td>
                                @foreach($projectService->service_names as $sName)
                                    <span class="badge bg-info">{{ $sName }}</span>
                                @endforeach
                            </td>
                            <td class="text-end">{{ formatMoney($projectService->total_amount) }}</td>
                            <td class="text-end text-success">{{ formatMoney($projectService->paid_amount) }}</td>
                            <td class="text-end {{ $projectService->balance > 0 ? 'text-danger' : '' }}">{{ formatMoney($projectService->balance) }}</td>
                            <td>
                                {{ $projectService->due_date ? formatDate($projectService->due_date) : '-' }}
                                @if($projectService->isOverdue())
                                    <span class="badge bg-danger">Overdue</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="button" class="btn btn-sm btn-outline-info" title="View Details" data-bs-toggle="modal" data-bs-target="#viewServiceModal{{ $projectService->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @can('projects.edit')
                                    @if($projectService->balance > 0)
                                    <button type="button" class="btn btn-sm btn-outline-success" title="Add Payment" data-bs-toggle="modal" data-bs-target="#servicePaymentModal{{ $projectService->id }}">
                                        <i class="bi bi-cash"></i>
                                    </button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-primary" title="Add Service Add-On" data-bs-toggle="modal" data-bs-target="#serviceAddonModal{{ $projectService->id }}">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                    <form action="{{ route('admin.projects.services.destroy', [$project, $projectService]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this service?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @if($projectService->addons->count() > 0)
                        @foreach($projectService->addons as $sAddon)
                        <tr class="table-light">
                            <td class="ps-4">
                                <small class="text-muted d-block"><i class="bi bi-arrow-return-right me-1"></i> Add-On</small>
                                @if($sAddon->service_names && count($sAddon->service_names) > 0)
                                    @foreach($sAddon->service_names as $svcName)
                                        <span class="badge bg-secondary" style="font-size: 0.7rem;">{{ $svcName }}</span>
                                    @endforeach
                                @endif
                                <small class="d-block text-muted">{{ $sAddon->description }}</small>
                            </td>
                            <td class="text-end"><small>{{ formatMoney($sAddon->amount) }}</small></td>
                            <td colspan="3"></td>
                            <td class="text-center">
                                @can('projects.edit')
                                <form action="{{ route('admin.projects.services.addon.destroy', [$project, $projectService, $sAddon]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this add-on?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                        @endif
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-tools fs-1 d-block mb-2"></i>
                                No services assigned
                            </td>
                        </tr>
                        @endforelse

                        @if($project->projectServices->count() > 0)
                        @php
                            $totalServiceAmount = $project->projectServices->sum(fn($s) => $s->total_amount);
                            $totalServicePaid = $project->projectServices->sum(fn($s) => $s->paid_amount);
                            $totalServiceBalance = $totalServiceAmount - $totalServicePaid;
                        @endphp
                        <tr>
                            <td><strong>Total Services</strong></td>
                            <td class="text-end"><strong>{{ formatMoney($totalServiceAmount) }}</strong></td>
                            <td class="text-end text-success"><strong>{{ formatMoney($totalServicePaid) }}</strong></td>
                            <td class="text-end {{ $totalServiceBalance > 0 ? 'text-danger' : '' }}"><strong>{{ formatMoney($totalServiceBalance) }}</strong></td>
                            <td colspan="2"></td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@if($project->description || $project->notes)
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-sticky me-2"></i> Notes & Description
            </div>
            <div class="card-body">
                @if($project->description)
                <p><strong>Description:</strong> {{ $project->description }}</p>
                @endif
                @if($project->notes)
                <p><strong>Notes:</strong> {{ $project->notes }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('modals')
@if(auth()->user()->hasPermission('projects', 'edit'))
<!-- Add Addon Modal -->
<div class="modal fade" id="addAddonModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.projects.addons.store', $project) }}" method="POST" novalidate>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Add-On</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" maxlength="1000"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" required min="0" step="0.01">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Requested Date</label>
                            <input type="date" name="requested_date" class="form-control" value="{{ date('Y-m-d') }}" placeholder="dd-mm-yyyy">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Approved Date</label>
                            <input type="date" name="approved_date" class="form-control" placeholder="dd-mm-yyyy">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Completed Date</label>
                            <input type="date" name="completed_date" class="form-control" placeholder="dd-mm-yyyy">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Add-On</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Addon Modals -->
@foreach($project->addons as $addon)
<div class="modal fade" id="editAddonModal{{ $addon->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.projects.addons.update', [$project, $addon]) }}" method="POST" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Add-On</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required maxlength="50" value="{{ $addon->title }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" maxlength="1000">{{ $addon->description }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" required min="0" step="0.01" value="{{ $addon->amount }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="pending" {{ $addon->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ $addon->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="in_progress" {{ $addon->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $addon->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $addon->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Requested Date</label>
                            <input type="date" name="requested_date" class="form-control" value="{{ $addon->requested_date?->format('Y-m-d') }}" placeholder="dd-mm-yyyy">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Approved Date</label>
                            <input type="date" name="approved_date" class="form-control" value="{{ $addon->approved_date?->format('Y-m-d') }}" placeholder="dd-mm-yyyy">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Completed Date</label>
                            <input type="date" name="completed_date" class="form-control" value="{{ $addon->completed_date?->format('Y-m-d') }}" placeholder="dd-mm-yyyy">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Add-On</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endif

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.projects.services.store', $project) }}" method="POST" id="addServiceForm">
                @csrf
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bi bi-tools me-2"></i>Add Service</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Services <span class="text-danger">*</span></label>
                            <select class="form-select select2-multiple" id="service_ids" name="service_ids[]" multiple required>
                                @foreach($services as $svc)
                                <option value="{{ $svc->id }}">{{ $svc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" name="amount" class="form-control" required min="0" step="1">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Advance</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" name="advance" id="add_service_advance" class="form-control" min="0" step="1" value="0">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Bank <span class="text-danger d-none" id="add_service_bank_star">*</span></label>
                            <select name="bank_id" id="add_service_bank" class="form-select">
                                <option value="">Select Bank</option>
                                @foreach($banks as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->bank_name }} - {{ $bank->account_number }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Note</label>
                            <input type="text" name="note" class="form-control" maxlength="500">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info"><i class="bi bi-check-lg me-1"></i> Save Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Service View/Payment/AddOn Modals -->
@foreach($project->projectServices as $projectService)
<!-- View Service Modal -->
<div class="modal fade" id="viewServiceModal{{ $projectService->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-tools me-2"></i>Service Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <small class="text-muted">Services</small>
                        <div>
                            @foreach($projectService->service_names as $sName)
                                <span class="badge bg-info">{{ $sName }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-3">
                        <small class="text-muted">Base Amount</small>
                        <div><strong>{{ formatMoney($projectService->amount) }}</strong></div>
                    </div>
                    <div class="col-3">
                        <small class="text-muted">Add-Ons</small>
                        <div><strong>{{ formatMoney($projectService->addons_total) }}</strong></div>
                    </div>
                    <div class="col-3">
                        <small class="text-muted">Total</small>
                        <div><strong class="text-primary">{{ formatMoney($projectService->total_amount) }}</strong></div>
                    </div>
                    <div class="col-3">
                        <small class="text-muted">Balance</small>
                        <div><strong class="{{ $projectService->balance > 0 ? 'text-danger' : 'text-success' }}">{{ formatMoney($projectService->balance) }}</strong></div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4">
                        <small class="text-muted">Due Date</small>
                        <div><strong>{{ $projectService->due_date ? formatDate($projectService->due_date) : '-' }}</strong></div>
                    </div>
                    <div class="col-8">
                        <small class="text-muted">Note</small>
                        <div><strong>{{ $projectService->note ?? '-' }}</strong></div>
                    </div>
                </div>
                @if($projectService->addons->count() > 0)
                <hr>
                <h6>Add-Ons</h6>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projectService->addons as $sAddon)
                        <tr>
                            <td>{{ $sAddon->description }}</td>
                            <td class="text-end">{{ formatMoney($sAddon->amount) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
                @if($projectService->serviceExpenses->count() > 0)
                <hr>
                <h6>Payments</h6>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Account</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projectService->serviceExpenses->sortBy('expense_date') as $payment)
                        <tr>
                            <td>{{ formatDate($payment->expense_date) }}</td>
                            <td>{{ $payment->bank->bank_name ?? '-' }}</td>
                            <td class="text-end">{{ formatMoney($payment->paid_amount) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Service Payment Modal -->
@if($projectService->balance > 0)
<div class="modal fade" id="servicePaymentModal{{ $projectService->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-cash me-2"></i>Add Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.projects.services.payment', [$project, $projectService]) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3 p-3 bg-light rounded">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Services</small>
                                <div>
                                    @foreach($projectService->service_names as $sName)
                                        <span class="badge bg-info">{{ $sName }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-6 text-end">
                                <small class="text-muted">Balance Due</small>
                                <div class="text-danger"><strong>{{ formatMoney($projectService->balance) }}</strong></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="1" min="1" max="{{ $projectService->balance }}" required>
                        <small class="text-muted">Max: {{ formatMoney($projectService->balance) }}</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bank/Cash Account <span class="text-danger">*</span></label>
                        <select name="bank_id" class="form-select" required>
                            <option value="">Select Account</option>
                            @foreach($banks as $bank)
                            <option value="{{ $bank->id }}">{{ $bank->bank_name }} - {{ $bank->account_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <input type="text" name="note" class="form-control" maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Service Add-On Modal -->
<div class="modal fade" id="serviceAddonModal{{ $projectService->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-lg me-2"></i>Add Service Add-On</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.projects.services.addon', [$project, $projectService]) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3 p-3 bg-light rounded">
                        <small class="text-muted">Services</small>
                        <div>
                            @foreach($projectService->service_names as $sName)
                                <span class="badge bg-info">{{ $sName }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Services</label>
                        <select class="form-select addon-service-select" name="service_ids[]" multiple data-modal-id="serviceAddonModal{{ $projectService->id }}">
                            @foreach($services as $svc)
                                <option value="{{ $svc->id }}">{{ $svc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-control" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" name="amount" class="form-control" required min="0" step="1">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Add</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@foreach($project->expenses->where('payment_status', '!=', 'paid') as $expense)
<div class="modal fade" id="projectExpensePaymentModal{{ $expense->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-cash me-2"></i>Record Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.projects.expenses.payment', [$project, $expense]) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3 p-3 bg-light rounded">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Expense</small>
                                <div><strong>{{ $expense->expense_number }}</strong></div>
                            </div>
                            <div class="col-6 text-end">
                                <small class="text-muted">Balance Due</small>
                                <div class="text-danger"><strong>{{ formatMoney($expense->balance) }}</strong></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="1" min="1" max="{{ $expense->balance }}" placeholder="0" required>
                        <small class="text-muted">Max: {{ formatMoney($expense->balance) }}</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bank/Cash Account <span class="text-danger">*</span></label>
                        <select name="bank_id" class="form-select" required>
                            <option value="">Select Account</option>
                            @foreach($banks as $bank)
                            <option value="{{ $bank->id }}">{{ $bank->bank_name }} - {{ $bank->account_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tabButtons = document.querySelectorAll('#projectTabs .nav-link');
    var addButtons = document.querySelectorAll('.tab-add-btn');

    function showButtonForTab(tabName) {
        addButtons.forEach(function(btn) {
            if (btn.getAttribute('data-for') === tabName) {
                btn.style.display = 'inline-block';
            } else {
                btn.style.display = 'none';
            }
        });
    }

    function updateActiveTabStyle() {
        tabButtons.forEach(function(btn) {
            if (btn.classList.contains('active')) {
                btn.style.color = '#000';
            } else {
                btn.style.color = '';
            }
        });
    }

    // Show correct button on page load based on active tab
    var activeTab = document.querySelector('#projectTabs .nav-link.active');
    if (activeTab) {
        showButtonForTab(activeTab.getAttribute('data-tab'));
    }

    updateActiveTabStyle();

    tabButtons.forEach(function(btn) {
        btn.addEventListener('shown.bs.tab', function(e) {
            var tabName = e.target.getAttribute('data-tab');
            showButtonForTab(tabName);
            updateActiveTabStyle();
        });
    });

    // Add-On Form Validation
    function validateAddonForm(form) {
        var isValid = true;
        var errors = [];

        // Clear previous errors
        form.querySelectorAll('.is-invalid').forEach(function(el) {
            el.classList.remove('is-invalid');
        });
        form.querySelectorAll('.invalid-feedback').forEach(function(el) {
            el.remove();
        });

        // Title validation
        var title = form.querySelector('input[name="title"]');
        if (!title.value.trim()) {
            isValid = false;
            title.classList.add('is-invalid');
            addError(title, 'Title is required');
        } else if (title.value.length > 50) {
            isValid = false;
            title.classList.add('is-invalid');
            addError(title, 'Title must be less than 50 characters');
        }

        // Amount validation
        var amount = form.querySelector('input[name="amount"]');
        if (!amount.value) {
            isValid = false;
            amount.classList.add('is-invalid');
            addError(amount, 'Amount is required');
        } else if (parseFloat(amount.value) < 0) {
            isValid = false;
            amount.classList.add('is-invalid');
            addError(amount, 'Amount must be positive');
        }

        // Status validation
        var status = form.querySelector('select[name="status"]');
        if (!status.value) {
            isValid = false;
            status.classList.add('is-invalid');
            addError(status, 'Status is required');
        }

        return isValid;
    }

    function addError(element, message) {
        var feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        feedback.textContent = message;
        element.parentNode.appendChild(feedback);
    }

    // Attach validation to all addon forms
    document.querySelectorAll('#addAddonModal form, [id^="editAddonModal"] form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!validateAddonForm(form)) {
                e.preventDefault();
            }
        });
    });

    // Clear validation on modal close
    document.querySelectorAll('#addAddonModal, [id^="editAddonModal"]').forEach(function(modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            var form = modal.querySelector('form');
            form.querySelectorAll('.is-invalid').forEach(function(el) {
                el.classList.remove('is-invalid');
            });
            form.querySelectorAll('.invalid-feedback').forEach(function(el) {
                el.remove();
            });
        });
    });

    // Initialize Select2 for services
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#service_ids').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select Services',
            allowClear: true,
            dropdownParent: $('#addServiceModal')
        });

        // Bank conditional on advance
        function toggleAddServiceBank() {
            var adv = parseFloat($('#add_service_advance').val()) || 0;
            if (adv > 0) {
                $('#add_service_bank').prop('required', true);
                $('#add_service_bank_star').removeClass('d-none');
            } else {
                $('#add_service_bank').prop('required', false);
                $('#add_service_bank_star').addClass('d-none');
            }
        }
        toggleAddServiceBank();
        $('#add_service_advance').on('input change', toggleAddServiceBank);

        // Initialize Select2 for addon service selects
        $('.addon-service-select').each(function() {
            var modalId = $(this).data('modal-id');
            $(this).select2({
                theme: 'bootstrap-5',
                placeholder: 'Select services',
                allowClear: true,
                dropdownParent: $('#' + modalId)
            });
        });
    }

    // Add Service Form Validation
    $('#addServiceForm').on('submit', function(e) {
        var isValid = true;
        var errors = [];

        // Clear previous errors
        $(this).find('.is-invalid').removeClass('is-invalid');
        $(this).find('.invalid-feedback').remove();

        // Services validation
        var serviceIds = $('#service_ids').val();
        if (!serviceIds || serviceIds.length === 0) {
            isValid = false;
            $('#service_ids').next('.select2-container').find('.select2-selection').css('border-color', '#dc3545');
            $('#service_ids').closest('.mb-3').append('<div class="invalid-feedback d-block">Please select at least one service.</div>');
        }

        // Amount validation
        var amount = parseFloat($('#addServiceForm input[name="amount"]').val()) || 0;
        if (amount <= 0) {
            isValid = false;
            $('#addServiceForm input[name="amount"]').addClass('is-invalid');
            $('#addServiceForm input[name="amount"]').closest('.input-group').after('<div class="invalid-feedback d-block">Amount must be greater than 0.</div>');
        }

        // Bank validation (only required when advance > 0)
        var advanceVal = parseFloat($('#addServiceForm input[name="advance"]').val()) || 0;
        var bankId = $('#addServiceForm select[name="bank_id"]').val();
        if (advanceVal > 0 && !bankId) {
            isValid = false;
            $('#addServiceForm select[name="bank_id"]').addClass('is-invalid');
            $('#addServiceForm select[name="bank_id"]').after('<div class="invalid-feedback">Bank is required when advance is greater than 0.</div>');
        }

        if (!isValid) {
            e.preventDefault();
            return false;
        }

        return true;
    });

    // Clear validation errors on input change
    $('#service_ids').on('change', function() {
        $(this).next('.select2-container').find('.select2-selection').css('border-color', '');
        $(this).closest('.mb-3').find('.invalid-feedback').remove();
    });

    $('#addServiceForm input[name="amount"]').on('input', function() {
        $(this).removeClass('is-invalid');
        $(this).closest('.input-group').next('.invalid-feedback').remove();
    });

    $('#addServiceForm select[name="bank_id"]').on('change', function() {
        $(this).removeClass('is-invalid');
        $(this).next('.invalid-feedback').remove();
    });
});
</script>
@endpush
