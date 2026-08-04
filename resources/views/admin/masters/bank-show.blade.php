@extends('layouts.app')
@section('title', 'Bank Account - ' . $bank->bank_name)
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-bank icon-gradient bg-midnight-bloom"></i>
            </div>
            <div>
                {{ $bank->bank_name }}
                <div class="page-title-subheading">Bank Account Transactions</div>
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.banks.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            <button class="btn btn-success" onclick="openTransactionModal({{ $bank->id }}, '{{ $bank->bank_name }}')" data-bs-toggle="modal" data-bs-target="#transactionModal">
                <i class="bi bi-plus-lg me-1"></i> Add Transaction
            </button>
        </div>
    </div>
</div>
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <small class="text-muted d-block">Opening Balance</small>
                <strong class="fs-5">{{ formatMoney($bank->opening_balance) }}</strong>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success bg-opacity-10">
            <div class="card-body text-center">
                <small class="text-muted d-block">Total Credit</small>
                <strong class="fs-5 text-success">{{ formatMoney($bank->total_credit) }}</strong>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger bg-opacity-10">
            <div class="card-body text-center">
                <small class="text-muted d-block">Total Debit</small>
                <strong class="fs-5 text-danger">{{ formatMoney($bank->total_debit) }}</strong>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary bg-opacity-10">
            <div class="card-body text-center">
                <small class="text-muted d-block">Current Balance</small>
                <strong class="fs-5 {{ $bank->balance >= 0 ? 'text-primary' : 'text-danger' }}">{{ formatMoney($bank->balance) }}</strong>
            </div>
        </div>
    </div>
</div>
<div class="main-card mb-3 card">
    <div class="card-header">
        <i class="bi bi-list-ul me-2"></i> Transaction History
    </div>
    <table class="table table-hover table-striped mb-0">
        <thead class="table-warning">
            <tr>
                <th width="60" class="text-center">#</th>
                <th width="100">Date</th>
                <th>Description</th>
                <th width="100" class="text-center">Type</th>
                <th width="120" class="text-end">Credit</th>
                <th width="120" class="text-end">Debit</th>
                <th width="100" class="text-center">Source</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $txn)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ formatDate($txn->transaction_date) }}</td>
                <td>{{ $txn->description ?: '-' }}</td>
                <td class="text-center">
                    <span class="badge {{ $txn->type == 'credit' ? 'bg-success' : 'bg-danger' }}">
                        {{ ucfirst($txn->type) }}
                    </span>
                </td>
                <td class="text-end text-success">
                    @if($txn->type == 'credit')
                        {{ formatMoney($txn->amount) }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-end text-danger">
                    @if($txn->type == 'debit')
                        {{ formatMoney($txn->amount) }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">
                    @if($txn->reference_type == 'income')
                        <a href="{{ route('admin.income.show', $txn->reference_id) }}" class="badge bg-info text-decoration-none">Income</a>
                    @elseif($txn->reference_type == 'expense')
                        <a href="{{ route('admin.expenses.show', $txn->reference_id) }}" class="badge bg-warning text-decoration-none">Expense</a>
                    @else
                        <span class="badge bg-secondary">Manual</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                    <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                    No transactions found.
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($transactions->count() > 0)
        <tfoot class="table-dark">
            <tr>
                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                <td class="text-end text-success"><strong>{{ formatMoney($transactions->where('type', 'credit')->sum('amount')) }}</strong></td>
                <td class="text-end text-danger"><strong>{{ formatMoney($transactions->where('type', 'debit')->sum('amount')) }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>
    <div class="card-footer">
        <span class="text-muted">Showing {{ $transactions->count() }} {{ Str::plural('transaction', $transactions->count()) }}</span>
    </div>
</div>
@push('modals')
<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-plus-slash-minus me-2"></i>Add Transaction</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.banks.transactions.store', $bank) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Bank Account</label>
                        <input type="text" class="form-control" value="{{ $bank->bank_name }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="type" id="type_credit" value="credit" checked>
                            <label class="btn btn-outline-success" for="type_credit"><i class="bi bi-plus-lg me-1"></i> Credit</label>
                            <input type="radio" class="btn-check" name="type" id="type_debit" value="debit">
                            <label class="btn btn-outline-danger" for="type_debit"><i class="bi bi-dash-lg me-1"></i> Debit</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="txn_amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" name="amount" placeholder="0" min="1" step="1" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="txn_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="transaction_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="txn_description" class="form-label">Description</label>
                        <input type="text" class="form-control" name="description" placeholder="Enter description" maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> Save Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
@endsection
