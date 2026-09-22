@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.app')
@section('title', 'Trip Details')
@section('currency_symbol', currencySymbol($trip))
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-kanban icon-gradient bg-strong-bliss"></i>
            </div>
            <div title="{{ $trip->trip_number }} - {{ $trip->name }}">
                {{ $trip->trip_number }} - {{ Str::limit($trip->name, 30) }}

            </div>
        </div>
        <div class="page-title-actions">
            @if($trip->customer_id && str_contains(url()->previous(), '/customers/'))
                <a href="{{ route('admin.customers.show', $trip->customer_id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Customer
                </a>
            @else
                <a href="{{ route('admin.trips.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            @endif
            <a href="{{ route('admin.trips.export', $trip) }}" class="btn btn-success">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </a>
            @can('trips.edit')
            <a href="{{ route('admin.trips.edit', $trip) }}" class="btn btn-primary">
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
                        <strong>{{ $trip->trip_number }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Client</small>
                        @if($trip->customer)
                            <strong>{{ $trip->customer->name }}</strong> <small class="text-muted">{{ $trip->customer->mobile }}</small>
                        @else
                            <strong>-</strong>
                        @endif
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">Work Type</small>
                        @if($trip->work_type && is_array($trip->work_type))
                            @foreach($trip->work_type as $type)
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
                        <span class="badge {{ $statusColors[$trip->status] ?? 'bg-secondary' }}">{{ $statusLabels[$trip->status] ?? ucfirst($trip->status) }}</span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Timeline</small>
                        <strong>
                            {{ $trip->start_date ? formatDate($trip->start_date) : 'Not set' }}
                            -
                            {{ $trip->expected_end_date ? formatDate($trip->expected_end_date) : 'Not set' }}
                        </strong>
                    </div>
                </div>
                <hr class="my-2">
                <div class="row">
                    <div class="col-md-2">
                        <small class="text-muted d-block">Company</small>
                        <strong>{{ $trip->company->name ?? '-' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Site Address</small>
                        <strong>{{ $trip->site_address ?? '-' }}</strong>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">Assigned Staff</small>
                        @if($trip->assigned_staff_ids && count($trip->assigned_staff_ids) > 0)
                            @foreach($trip->assigned_staff_list as $staffMember)
                                <span class="badge bg-info">{{ $staffMember->name }}</span>
                            @endforeach
                        @else
                            <strong>-</strong>
                        @endif
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">Assigned Vendors</small>
                        @if($trip->assigned_vendor_ids && count($trip->assigned_vendor_ids) > 0)
                            @foreach($trip->assigned_vendor_list as $vendor)
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
                        <strong class="text-primary">{{ formatMoney($trip->budget, true, 0, $trip) }}</strong>
                    </div>
                    <div class="col-3">
                        <small class="text-muted d-block">Total Spent</small>
                        <strong class="text-danger">{{ formatMoney($trip->total_spent, true, 0, $trip) }}</strong>
                    </div>
                    <div class="col-3">
                        <small class="text-muted d-block">Income</small>
                        <strong class="text-success">{{ formatMoney($trip->total_income, true, 0, $trip) }}</strong>
                    </div>
                    <div class="col-3">
                        <small class="text-muted d-block">Profit</small>
                        <strong class="{{ $trip->profit >= 0 ? 'text-success' : 'text-danger' }}">{{ formatMoney($trip->profit, true, 0, $trip) }}</strong>
                    </div>
                </div>
                @if($trip->gst_percent > 0 && $trip->gst_amount > 0)
                <hr class="my-2">
                <div class="row text-center">
                    <div class="col-3">
                        <small class="text-muted d-block">GST ({{ rtrim(rtrim(number_format($trip->gst_percent, 2), '0'), '.') }}%){{ $trip->gst_inclusive ? ' incl.' : '' }}</small>
                        <strong class="text-info">{{ formatMoney($trip->gst_amount, true, 0, $trip) }}</strong>
                    </div>
                    <div class="col-3">
                        <small class="text-muted d-block">Total with GST</small>
                        <strong class="text-primary">{{ formatMoney($trip->total_with_gst, true, 0, $trip) }}</strong>
                    </div>
                    @if($trip->gst_split)
                    <div class="col-3">
                        <small class="text-muted d-block">CGST</small>
                        <strong>{{ formatMoney($trip->gst_amount / 2, true, 0, $trip) }}</strong>
                    </div>
                    <div class="col-3">
                        <small class="text-muted d-block">SGST</small>
                        <strong>{{ formatMoney($trip->gst_amount / 2, true, 0, $trip) }}</strong>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@if($trip->files->count() > 0)
<div class="row">
    <div class="col-md-12">
        <div class="main-card mb-3 card animated fadeInUp">
            <div class="card-header py-2">
                <i class="bi bi-folder me-2"></i> Trip Files ({{ $trip->files->count() }})
            </div>
            <div class="card-body py-2">
                <div class="d-flex flex-wrap gap-2">
                    @foreach($trip->files as $file)
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
                <ul class="nav nav-tabs card-header-tabs trip-tabs" id="tripTabs">
                    <li class="nav-item">
                        <button class="nav-link {{ request('tab') != 'addons' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#expenses" data-tab="expenses">
                            <i class="bi bi-graph-down-arrow me-1"></i> Expenses ({{ $trip->expenses->count() }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#income" data-tab="income">
                            <i class="bi bi-graph-up-arrow me-1"></i> Income ({{ $trip->incomes->count() }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#invoices" data-tab="invoices">
                            <i class="bi bi-receipt me-1"></i> Invoices ({{ $trip->invoices->count() }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {{ request('tab') == 'addons' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#addons" data-tab="addons">
                            <i class="bi bi-plus-circle me-1"></i> Add-Ons ({{ $trip->addons->count() }})
                        </button>
                    </li>
                </ul>
                <div class="tab-add-buttons">
                    @can('expenses.create')
                    <a href="{{ route('admin.expenses.create') }}?trip_id={{ $trip->id }}" class="btn btn-sm btn-primary tab-add-btn" data-for="expenses">
                        <i class="bi bi-plus-lg"></i> Add Expense
                    </a>
                    @endcan
                    @can('income.create')
                    <a href="{{ route('admin.income.create') }}?trip_id={{ $trip->id }}" class="btn btn-sm btn-success tab-add-btn" data-for="income" style="display: none;">
                        <i class="bi bi-plus-lg"></i> Add Income
                    </a>
                    @endcan
                    @can('invoices.create')
                    <a href="{{ route('admin.invoices.create') }}?trip_id={{ $trip->id }}" class="btn btn-sm btn-info tab-add-btn" data-for="invoices" style="display: none;">
                        <i class="bi bi-plus-lg"></i> Create Invoice
                    </a>
                    @endcan
                    @can('trips.edit')
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
                                    @php $nonServiceExpenses = $trip->expenses->where('expense_type', '!=', 'service'); @endphp
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
                                        <td class="text-end text-danger">{{ formatMoney($expense->grand_total, 0, $trip) }}</td>
                                        <td class="text-end text-success">{{ formatMoney($expense->paid_amount, 0, $trip) }}</td>
                                        <td class="text-end {{ $expense->balance > 0 ? 'text-danger' : '' }}">{{ formatMoney($expense->balance, 0, $trip) }}</td>
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
                                                <button type="button" class="btn btn-sm btn-outline-success" title="Record Payment" data-bs-toggle="modal" data-bs-target="#tripExpensePaymentModal{{ $expense->id }}">
                                                    <i class="bi bi-cash"></i>
                                                </button>
                                                @endif
                                                @if($expense->payment_status !== 'paid')
                                                <a href="{{ route('admin.expenses.edit', $expense) }}?from_trip={{ $trip->id }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @endif
                                                @endcan
                                                <a href="{{ route('admin.expenses.show', $expense) }}?from_trip={{ $trip->id }}" class="btn btn-sm btn-outline-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @can('expenses.delete')
                                                <form action="{{ route('admin.expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this expense?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="from_trip" value="{{ $trip->id }}">
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
                                        <td class="text-end text-danger"><strong>{{ formatMoney($nonServiceExpenses->sum('grand_total'), 0, $trip) }}</strong></td>
                                        <td class="text-end text-success"><strong>{{ formatMoney($nonServiceExpenses->sum('paid_amount'), 0, $trip) }}</strong></td>
                                        <td class="text-end {{ $nonServiceExpenses->sum('grand_total') - $nonServiceExpenses->sum('paid_amount') > 0 ? 'text-danger' : '' }}"><strong>{{ formatMoney($nonServiceExpenses->sum('grand_total') - $nonServiceExpenses->sum('paid_amount'), 0, $trip) }}</strong></td>
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
                                    @forelse($trip->incomes->sortByDesc('income_date') as $income)
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
                                        <td class="text-success">{{ formatMoney($income->amount, 0, $trip) }}</td>
                                        <td>
                                            <div class="btn-group-actions">
                                                @can('income.edit')
                                                <a href="{{ route('admin.income.edit', $income) }}?from_trip={{ $trip->id }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @endcan
                                                <a href="{{ route('admin.income.show', $income) }}?from_trip={{ $trip->id }}" class="btn btn-sm btn-outline-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @can('income.delete')
                                                <form action="{{ route('admin.income.destroy', $income) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this income?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="from_trip" value="{{ $trip->id }}">
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
                                    @if($trip->incomes->count() > 0)
                                    <tr class="table-success">
                                        <td colspan="4"><strong>Total Income</strong></td>
                                        <td class="text-success"><strong>{{ formatMoney($trip->total_income, 0, $trip) }}</strong></td>
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
                                    @forelse($trip->invoices->sortByDesc('invoice_date') as $invoice)
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
                                        <td class="text-end">{{ formatMoney($invoice->total_amount, 0, $trip) }}</td>
                                        <td>
                                            <div class="btn-group-actions">
                                                @can('invoices.edit')
                                                @if($invoice->status !== 'paid')
                                                <a href="{{ route('admin.invoices.edit', $invoice) }}?from_trip={{ $trip->id }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @endif
                                                @endcan
                                                <a href="{{ route('admin.invoices.show', $invoice) }}?from_trip={{ $trip->id }}" class="btn btn-sm btn-outline-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @can('invoices.delete')
                                                <form action="{{ route('admin.invoices.destroy', $invoice) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this invoice?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="from_trip" value="{{ $trip->id }}">
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
                                @forelse($trip->addons->sortByDesc('created_at') as $addon)
                                <tr>
                                    <td><strong>{{ $addon->title }}</strong></td>
                                    <td>{{ Str::limit($addon->description, 50) ?? '-' }}</td>
                                    <td class="text-info">{{ formatMoney($addon->amount, 0, $trip) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $addon->status_badge }}">{{ ucfirst($addon->status) }}</span>
                                    </td>
                                    <td>{{ $addon->requested_date ? formatDate($addon->requested_date) : '-' }}</td>
                                    <td>
                                        <div class="btn-group-actions">
                                            @if(auth()->user()->hasPermission('trips', 'edit'))
                                            <button type="button" class="btn btn-sm btn-outline-primary" title="Edit"
                                                data-bs-toggle="modal" data-bs-target="#editAddonModal{{ $addon->id }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('admin.trips.addons.destroy', [$trip, $addon]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this add-on?')">
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
                                @if($trip->addons->count() > 0)
                                <tr>
                                    <td colspan="2"><strong>Total Add-Ons</strong></td>
                                    <td class="text-info"><strong>{{ formatMoney($trip->add_on_total, 0, $trip) }}</strong></td>
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
                    <span class="badge bg-info ms-2">{{ $trip->tripServices->count() }}</span>
                </span>
                @can('trips.edit')
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
                        @forelse($trip->tripServices->sortByDesc('created_at') as $tripService)
                        <tr>
                            <td>
                                @foreach($tripService->service_names as $sName)
                                    <span class="badge bg-info">{{ $sName }}</span>
                                @endforeach
                            </td>
                            <td class="text-end">{{ formatMoney($tripService->total_amount, 0, $trip) }}</td>
                            <td class="text-end text-success">{{ formatMoney($tripService->paid_amount, 0, $trip) }}</td>
                            <td class="text-end {{ $tripService->balance > 0 ? 'text-danger' : '' }}">{{ formatMoney($tripService->balance, 0, $trip) }}</td>
                            <td>
                                {{ $tripService->due_date ? formatDate($tripService->due_date) : '-' }}
                                @if($tripService->isOverdue())
                                    <span class="badge bg-danger">Overdue</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="button" class="btn btn-sm btn-outline-info" title="View Details" data-bs-toggle="modal" data-bs-target="#viewServiceModal{{ $tripService->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @can('trips.edit')
                                    @if($tripService->balance > 0)
                                    <button type="button" class="btn btn-sm btn-outline-success" title="Add Payment" data-bs-toggle="modal" data-bs-target="#servicePaymentModal{{ $tripService->id }}">
                                        <i class="bi bi-cash"></i>
                                    </button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-primary" title="Add Service Add-On" data-bs-toggle="modal" data-bs-target="#serviceAddonModal{{ $tripService->id }}">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                    <form action="{{ route('admin.trips.services.destroy', [$trip, $tripService]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this service?')">
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
                        @if($tripService->addons->count() > 0)
                        @foreach($tripService->addons as $sAddon)
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
                            <td class="text-end"><small>{{ formatMoney($sAddon->amount, 0, $trip) }}</small></td>
                            <td colspan="3"></td>
                            <td class="text-center">
                                @can('trips.edit')
                                <form action="{{ route('admin.trips.services.addon.destroy', [$trip, $tripService, $sAddon]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this add-on?')">
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

                        @if($trip->tripServices->count() > 0)
                        @php
                            $totalServiceAmount = $trip->tripServices->sum(fn($s) => $s->total_amount);
                            $totalServicePaid = $trip->tripServices->sum(fn($s) => $s->paid_amount);
                            $totalServiceBalance = $totalServiceAmount - $totalServicePaid;
                        @endphp
                        <tr>
                            <td><strong>Total Services</strong></td>
                            <td class="text-end"><strong>{{ formatMoney($totalServiceAmount, 0, $trip) }}</strong></td>
                            <td class="text-end text-success"><strong>{{ formatMoney($totalServicePaid, 0, $trip) }}</strong></td>
                            <td class="text-end {{ $totalServiceBalance > 0 ? 'text-danger' : '' }}"><strong>{{ formatMoney($totalServiceBalance, 0, $trip) }}</strong></td>
                            <td colspan="2"></td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@if($trip->description || $trip->notes)
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-sticky me-2"></i> Notes & Description
            </div>
            <div class="card-body">
                @if($trip->description)
                <p><strong>Description:</strong> {{ $trip->description }}</p>
                @endif
                @if($trip->notes)
                <p><strong>Notes:</strong> {{ $trip->notes }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('modals')
@if(auth()->user()->hasPermission('trips', 'edit'))
<!-- Add Addon Modal -->
<div class="modal fade" id="addAddonModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.trips.addons.store', $trip) }}" method="POST" novalidate>
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
@foreach($trip->addons as $addon)
<div class="modal fade" id="editAddonModal{{ $addon->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.trips.addons.update', [$trip, $addon]) }}" method="POST" novalidate>
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
            <form action="{{ route('admin.trips.services.store', $trip) }}" method="POST" id="addServiceForm">
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
                                <span class="input-group-text js-currency-symbol">₹</span>
                                <input type="number" name="amount" class="form-control" required min="0" step="1">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Advance</label>
                            <div class="input-group">
                                <span class="input-group-text js-currency-symbol">₹</span>
                                <input type="number" name="advance" id="add_service_advance" class="form-control" min="0" step="1" value="0">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Bank <span class="text-danger d-none" id="add_service_bank_star">*</span></label>
                            <select name="bank_id" id="add_service_bank" class="form-select">
                                <option value="">Select Bank</option>
                                @foreach($banks as $bank)
                                <option value="{{ $bank->id }}" data-currency="{{ $bank->currency_symbol }}">{{ $bank->bank_name }} - {{ $bank->account_number }}</option>
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
@foreach($trip->tripServices as $tripService)
<!-- View Service Modal -->
<div class="modal fade" id="viewServiceModal{{ $tripService->id }}" tabindex="-1">
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
                            @foreach($tripService->service_names as $sName)
                                <span class="badge bg-info">{{ $sName }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-3">
                        <small class="text-muted">Base Amount</small>
                        <div><strong>{{ formatMoney($tripService->amount, 0, $trip) }}</strong></div>
                    </div>
                    <div class="col-3">
                        <small class="text-muted">Add-Ons</small>
                        <div><strong>{{ formatMoney($tripService->addons_total, 0, $trip) }}</strong></div>
                    </div>
                    <div class="col-3">
                        <small class="text-muted">Total</small>
                        <div><strong class="text-primary">{{ formatMoney($tripService->total_amount, 0, $trip) }}</strong></div>
                    </div>
                    <div class="col-3">
                        <small class="text-muted">Balance</small>
                        <div><strong class="{{ $tripService->balance > 0 ? 'text-danger' : 'text-success' }}">{{ formatMoney($tripService->balance, 0, $trip) }}</strong></div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4">
                        <small class="text-muted">Due Date</small>
                        <div><strong>{{ $tripService->due_date ? formatDate($tripService->due_date) : '-' }}</strong></div>
                    </div>
                    <div class="col-8">
                        <small class="text-muted">Note</small>
                        <div><strong>{{ $tripService->note ?? '-' }}</strong></div>
                    </div>
                </div>
                @if($tripService->addons->count() > 0)
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
                        @foreach($tripService->addons as $sAddon)
                        <tr>
                            <td>{{ $sAddon->description }}</td>
                            <td class="text-end">{{ formatMoney($sAddon->amount, 0, $trip) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
                @if($tripService->serviceExpenses->count() > 0)
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
                        @foreach($tripService->serviceExpenses->sortBy('expense_date') as $payment)
                        <tr>
                            <td>{{ formatDate($payment->expense_date) }}</td>
                            <td>{{ $payment->bank->bank_name ?? '-' }}</td>
                            <td class="text-end">{{ formatMoney($payment->paid_amount, 0, $trip) }}</td>
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
@if($tripService->balance > 0)
<div class="modal fade" id="servicePaymentModal{{ $tripService->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-cash me-2"></i>Add Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.trips.services.payment', [$trip, $tripService]) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3 p-3 bg-light rounded">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Services</small>
                                <div>
                                    @foreach($tripService->service_names as $sName)
                                        <span class="badge bg-info">{{ $sName }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-6 text-end">
                                <small class="text-muted">Balance Due</small>
                                <div class="text-danger"><strong>{{ formatMoney($tripService->balance, 0, $trip) }}</strong></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="1" min="1" max="{{ $tripService->balance }}" required>
                        <small class="text-muted">Max: {{ formatMoney($tripService->balance, 0, $trip) }}</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bank/Cash Account <span class="text-danger">*</span></label>
                        <select name="bank_id" class="form-select" required>
                            <option value="">Select Account</option>
                            @foreach($banks as $bank)
                            <option value="{{ $bank->id }}" data-currency="{{ $bank->currency_symbol }}">{{ $bank->bank_name }} - {{ $bank->account_number }}</option>
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
<div class="modal fade" id="serviceAddonModal{{ $tripService->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-lg me-2"></i>Add Service Add-On</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.trips.services.addon', [$trip, $tripService]) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3 p-3 bg-light rounded">
                        <small class="text-muted">Services</small>
                        <div>
                            @foreach($tripService->service_names as $sName)
                                <span class="badge bg-info">{{ $sName }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Services</label>
                        <select class="form-select addon-service-select" name="service_ids[]" multiple data-modal-id="serviceAddonModal{{ $tripService->id }}">
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
                            <span class="input-group-text js-currency-symbol">₹</span>
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

@foreach($trip->expenses->where('payment_status', '!=', 'paid') as $expense)
<div class="modal fade" id="tripExpensePaymentModal{{ $expense->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-cash me-2"></i>Record Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.trips.expenses.payment', [$trip, $expense]) }}" method="POST">
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
                                <div class="text-danger"><strong>{{ formatMoney($expense->balance, 0, $trip) }}</strong></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="1" min="1" max="{{ $expense->balance }}" placeholder="0" required>
                        <small class="text-muted">Max: {{ formatMoney($expense->balance, 0, $trip) }}</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bank/Cash Account <span class="text-danger">*</span></label>
                        <select name="bank_id" class="form-select" required>
                            <option value="">Select Account</option>
                            @foreach($banks as $bank)
                            <option value="{{ $bank->id }}" data-currency="{{ $bank->currency_symbol }}">{{ $bank->bank_name }} - {{ $bank->account_number }}</option>
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
    var tabButtons = document.querySelectorAll('#tripTabs .nav-link');
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
    var activeTab = document.querySelector('#tripTabs .nav-link.active');
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
