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
                <div class="page-title-subheading">
                    @if($bank->account_number)
                        A/C: XXXX{{ substr($bank->account_number, -4) }}
                        | {{ $bank->country_label }} ({{ $bank->currency_code }}) @if($bank->bank_code) | {{ $bank->bank_code_label }}: {{ $bank->bank_code }} @endif
                    @else
                        {{ ucfirst($bank->account_type) }} Account
                    @endif
                </div>
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.banks.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            @can('banks.edit')
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#transactionModal">
                <i class="bi bi-plus-lg me-1"></i> Add Transaction
            </button>
            @endcan
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
<div class="main-card mb-3 card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span><i class="bi bi-list-ul me-1"></i> Statement ({{ $activeCount }})</span>
        <div class="d-flex flex-wrap gap-3 small">
            <span class="text-muted">Opening: <strong>{{ formatMoney($bank->opening_balance ?? 0, 0, $bank) }}</strong></span>
            <span class="text-success">Credits: <strong>{{ formatMoney($bank->total_credit, 0, $bank) }}</strong></span>
            <span class="text-danger">Debits: <strong>{{ formatMoney($bank->total_debit, 0, $bank) }}</strong></span>
            <span>Balance: <strong>{{ formatMoney($bank->balance, 0, $bank) }}</strong></span>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th width="60" class="text-center">#</th>
                    <th width="100">Date</th>
                    <th>Description</th>
                    <th width="100" class="text-center">Type</th>
                    <th width="120" class="text-end">Credit</th>
                    <th width="120" class="text-end">Debit</th>
                    <th width="140" class="text-center">Source</th>
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
                            {{ formatMoney($txn->amount, 0, $bank) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-end text-danger">
                        @if($txn->type == 'debit')
                            {{ formatMoney($txn->amount, 0, $bank) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        @if($txn->source == 'income')
                            <a href="{{ route('admin.income.show', $txn->id) }}" class="badge bg-info text-decoration-none">{{ $txn->reference }}</a>
                        @else
                            <a href="{{ route('admin.expenses.show', $txn->id) }}" class="badge bg-warning text-decoration-none">{{ $txn->reference }}</a>
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
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                    <td class="text-end"><strong>{{ formatMoney($transactions->where('type', 'credit')->sum('amount'), 0, $bank) }}</strong></td>
                    <td class="text-end"><strong>{{ formatMoney($transactions->where('type', 'debit')->sum('amount'), 0, $bank) }}</strong></td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
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
                                <span class="input-group-text">{{ $bank->currency_symbol ?? '₹' }}</span>
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
