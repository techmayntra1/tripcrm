@extends('layouts.app')
@section('title', 'Vendor Ledger')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-journal-text icon-gradient bg-arielle-smile"></i>
            </div>
            <div title="Vendor Ledger - {{ $vendor->name }}">
                Vendor Ledger - {{ Str::limit($vendor->name, 30) }}
            </div>
        </div>
        <div class="page-title-actions">
            @if($vendor->trashed())
                <a href="{{ route('admin.vendors.trashed') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
                @can('vendors.delete')
                <form action="{{ route('admin.vendors.restore', $vendor->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                    </button>
                </form>
                @endcan
            @else
                <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
                @can('vendors.edit')
                <a href="{{ route('admin.vendors.edit', $vendor) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                @endcan
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
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div class="row mb-3">
    <div class="col-md-12">
        <div class="main-card card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Total Purchase</small>
                        <strong class="text-info">{{ formatMoney($vendor->total_purchase, true) }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Total Paid</small>
                        <strong class="text-success">{{ formatMoney($vendor->total_paid, true) }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Outstanding</small>
                        <strong class="{{ $vendor->outstanding > 0 ? 'text-danger' : '' }}">{{ formatMoney($vendor->outstanding, true) }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Opening Balance</small>
                        <strong>{{ formatMoney($vendor->opening_balance, true) }}</strong>
                    </div>
                </div>
                <hr class="my-2">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Categories</small>
                        @php $categoryNames = $vendor->category_names; @endphp
                        @if(count($categoryNames))
                            @foreach($categoryNames as $catName)
                                <span class="badge bg-primary">{{ $catName }}</span>
                            @endforeach
                        @else
                            <strong>-</strong>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Contact Person</small>
                        <strong>{{ $vendor->contact_person ?? '-' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Mobile</small>
                        <strong>{{ $vendor->mobile }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Email</small>
                        <strong>{{ $vendor->email ?? '-' }}</strong>
                    </div>
                </div>
                @if($vendor->gst_number || $vendor->pan_number || $vendor->address)
                <hr class="my-2">
                <div class="row mb-3">
                    @if($vendor->gst_number)
                    <div class="col-md-3">
                        <small class="text-muted d-block">GST Number</small>
                        <strong>{{ $vendor->gst_number }}</strong>
                    </div>
                    @endif
                    @if($vendor->pan_number)
                    <div class="col-md-3">
                        <small class="text-muted d-block">PAN Number</small>
                        <strong>{{ $vendor->pan_number }}</strong>
                    </div>
                    @endif
                    @if($vendor->address)
                    <div class="col-md-6">
                        <small class="text-muted d-block">Address</small>
                        <strong>{{ $vendor->address }}</strong>
                    </div>
                    @endif
                </div>
                @endif
                @if($vendor->bank_name || $vendor->account_number)
                <hr class="my-2">
                <div class="row">
                    @if($vendor->bank_name)
                    <div class="col-md-3">
                        <small class="text-muted d-block">Bank Name</small>
                        <strong>{{ $vendor->bank_name }}</strong>
                    </div>
                    @endif
                    @if($vendor->account_number)
                    <div class="col-md-3">
                        <small class="text-muted d-block">Account Number</small>
                        <strong>{{ $vendor->account_number }}</strong>
                    </div>
                    @endif
                    @if($vendor->ifsc_code)
                    <div class="col-md-3">
                        <small class="text-muted d-block">IFSC Code</small>
                        <strong>{{ $vendor->ifsc_code }}</strong>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="main-card mb-3 card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-journal-text me-2"></i> Expenses & Payments</span>
                @if(!$vendor->trashed())
                @can('expenses.create')
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.expenses.create') }}?vendor_id={{ $vendor->id }}&expense_type=vendor" class="btn btn-sm btn-danger">
                        <i class="bi bi-plus-lg me-1"></i> Add Expense
                    </a>
                </div>
                @endcan
                @endif
            </div>
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-warning">
                        <tr>
                            <th width="100">Date</th>
                            <th width="200">Description</th>
                            <th width="100" class="text-center">Project</th>
                            <th width="120" class="text-end">Amount</th>
                            <th width="120" class="text-end">Paid</th>
                            <th width="120" class="text-end">Balance</th>
                            <th width="100" class="text-center">Status</th>
                            @if(!$vendor->trashed())
                            <th width="100" class="text-center">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @if($vendor->opening_balance > 0)
                        <tr class="table-secondary">
                            <td>{{ formatDate($vendor->created_at) }}</td>
                            <td><strong>Opening Balance</strong></td>
                            <td class="text-center">-</td>
                            <td class="text-end">{{ formatMoney($vendor->opening_balance) }}</td>
                            <td class="text-end">-</td>
                            <td class="text-end">{{ formatMoney($vendor->opening_balance) }}</td>
                            <td class="text-center"><span class="badge bg-warning">Carried Forward</span></td>
                            @if(!$vendor->trashed())
                            <td class="text-center">-</td>
                            @endif
                        </tr>
                        @endif
                        @forelse($vendor->expenses->sortByDesc('expense_date') as $expense)
                        <tr>
                            <td>{{ formatDate($expense->expense_date) }}</td>
                            <td>
                                <strong>{{ $expense->description ?? $expense->expense_number }}</strong>
                                @if($expense->reference_number)
                                <br><small class="text-muted">Ref: {{ $expense->reference_number }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($expense->project)
                                    <span class="badge bg-info">{{ $expense->project->project_number }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end">{{ formatMoney($expense->grand_total) }}</td>
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
                            @if(!$vendor->trashed())
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    @can('expenses.edit')
                                    @if($expense->balance > 0)
                                    <button type="button" class="btn btn-sm btn-outline-success" title="Record Payment" data-bs-toggle="modal" data-bs-target="#paymentModal{{ $expense->id }}">
                                        <i class="bi bi-cash"></i>
                                    </button>
                                    @endif
                                    @endcan
                                    <a href="{{ route('admin.expenses.show', $expense) }}" class="btn btn-sm btn-outline-info" title="Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $vendor->trashed() ? 7 : 8 }}" class="text-center py-4 text-muted">
                                <i class="bi bi-journal-x fs-3 d-block mb-2"></i>
                                No expenses recorded yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($vendor->expenses->count() > 0 || $vendor->opening_balance > 0)
                    <tfoot class="table-dark">
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total</strong></td>
                            <td class="text-end"><strong>{{ formatMoney($vendor->opening_balance + $vendor->total_purchase) }}</strong></td>
                            <td class="text-end text-success"><strong>{{ formatMoney($vendor->total_paid) }}</strong></td>
                            <td class="text-end {{ $vendor->outstanding > 0 ? 'text-danger' : '' }}"><strong>{{ formatMoney($vendor->outstanding) }}</strong></td>
                            <td colspan="{{ $vendor->trashed() ? 1 : 2 }}"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            <div class="card-footer text-muted">
                Showing {{ $vendor->expenses->count() }} {{ Str::plural('expense', $vendor->expenses->count()) }}
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
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
    </div>
</div>
@if($vendor->notes)
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-sticky me-2"></i> Notes
            </div>
            <div class="card-body">
                {{ $vendor->notes }}
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('modals')
@if(!$vendor->trashed())
@foreach($vendor->expenses->where('payment_status', '!=', 'paid') as $expense)
<div class="modal fade" id="paymentModal{{ $expense->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-cash me-2"></i>Record Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.vendors.expenses.payment', [$vendor, $expense]) }}" method="POST">
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
                        <input type="number" name="amount" class="form-control" step="1" min="1" max="{{ $expense->balance }}" value="{{ $expense->balance }}" required>
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
@endif
@endpush
