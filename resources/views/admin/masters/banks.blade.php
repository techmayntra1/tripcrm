@extends('layouts.app')
@section('title', 'Bank Accounts')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-bank icon-gradient bg-midnight-bloom"></i>
            </div>
            <div>
                Bank Accounts

            </div>
        </div>
        <div class="page-title-actions">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBankModal">
                <i class="bi bi-plus-lg me-1"></i> Add Bank Account
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
<div class="row">
    <div class="col-md-12">
        <div class="main-card mb-3 card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div><i class="bi bi-list-ul me-2"></i> All Bank Accounts</div>
            </div>
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-warning">
                        <tr>
                            <th width="40" class="text-center">#</th>
                            <th>Account Name</th>
                            <th>Bank Details</th>
                            <th width="100" class="text-end">Opening</th>
                            <th width="100" class="text-end">Credit</th>
                            <th width="100" class="text-end">Debit</th>
                            <th width="120" class="text-end">Balance</th>
                            <th width="80" class="text-center">Status</th>
                            <th width="120" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($banks as $index => $bank)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-icon-wrapper me-2">
                                        <div class="avatar-icon avatar-icon-sm {{ $bank->account_type == 'cash' ? 'bg-success' : 'bg-primary' }} text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="bi {{ $bank->account_type == 'cash' ? 'bi-cash-stack' : 'bi-bank' }}"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <strong>{{ $bank->bank_name }}</strong>
                                        @if($bank->account_holder)
                                        <br><small class="text-muted">{{ $bank->account_holder }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($bank->account_number)
                                <small>A/C: XXXX{{ substr($bank->account_number, -4) }}</small>
                                @else
                                <small>{{ ucfirst($bank->account_type ?? 'Bank') }} Account</small>
                                @endif
                                @if($bank->ifsc_code)
                                <br><small class="text-muted">IFSC: {{ $bank->ifsc_code }}</small>
                                @endif
                            </td>
                            <td class="text-end">{{ formatMoney($bank->opening_balance) }}</td>
                            <td class="text-end text-success">{{ formatMoney($bank->total_credit) }}</td>
                            <td class="text-end text-danger">{{ formatMoney($bank->total_debit) }}</td>
                            <td class="text-end"><strong class="{{ $bank->balance >= 0 ? 'text-primary' : 'text-danger' }}">{{ formatMoney($bank->balance) }}</strong></td>
                            <td class="text-center"><span class="badge {{ $bank->trashed() ? 'bg-secondary' : 'bg-success' }}">{{ $bank->trashed() ? 'Inactive' : 'Active' }}</span></td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('admin.banks.show', $bank) }}" class="btn btn-sm btn-outline-secondary" title="View Transactions">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-success" title="Add Credit/Debit" onclick="openTransactionModal({{ $bank->id }}, '{{ $bank->bank_name }}')" data-bs-toggle="modal" data-bs-target="#transactionModal">
                                        <i class="bi bi-plus-slash-minus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-bank fs-1 d-block mb-2"></i>
                                No bank accounts found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($banks->count() > 0)
                    <tfoot class="table-dark">
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td class="text-end"><strong>{{ formatMoney($banks->sum('opening_balance')) }}</strong></td>
                            <td class="text-end text-success"><strong>{{ formatMoney($banks->sum('total_credit')) }}</strong></td>
                            <td class="text-end text-danger"><strong>{{ formatMoney($banks->sum('total_debit')) }}</strong></td>
                            <td class="text-end text-primary"><strong>{{ formatMoney($banks->sum('balance')) }}</strong></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            <div class="card-footer">
                <span class="text-muted">Showing {{ $banks->count() }} {{ Str::plural('account', $banks->count()) }}</span>
            </div>
        </div>
    </div>
</div>
@push('modals')
<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-plus-slash-minus me-2"></i>Add Credit / Debit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="transactionForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Bank Account</label>
                        <input type="text" class="form-control" id="txn_bank_name" readonly>
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
                                <input type="number" class="form-control" id="txn_amount" name="amount" placeholder="0" min="1" step="1" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="txn_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="txn_date" name="transaction_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="txn_description" class="form-label">Description</label>
                        <input type="text" class="form-control" id="txn_description" name="description" placeholder="Enter description" maxlength="255">
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
<div class="modal fade" id="addBankModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Bank Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="account_type" class="form-label">Account Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="account_type" name="account_type" required>
                            <option value="">Select Type</option>
                            <option value="bank">Bank Account</option>
                            <option value="cash">Cash Account</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="account_name" class="form-label">Account Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="account_name" name="account_name" placeholder="e.g., HDFC Current Account" required maxlength="100">
                    </div>
                    <div class="mb-3 bank-fields">
                        <label for="bank_name" class="form-label">Bank Name</label>
                        <input type="text" class="form-control" id="bank_name" name="bank_name" placeholder="e.g., HDFC Bank" maxlength="100">
                    </div>
                    <div class="row bank-fields">
                        <div class="col-md-6 mb-3">
                            <label for="account_number" class="form-label">Account Number</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" placeholder="Account Number" maxlength="20">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ifsc_code" class="form-label">IFSC Code</label>
                            <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" placeholder="IFSC Code" maxlength="11">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save Account</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editBankModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Bank Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="edit_account_type" class="form-label">Account Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_account_type" name="account_type" required>
                            <option value="">Select Type</option>
                            <option value="bank">Bank Account</option>
                            <option value="cash">Cash Account</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_account_name" class="form-label">Account Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_account_name" name="account_name" placeholder="e.g., HDFC Current Account" required maxlength="100">
                    </div>
                    <div class="mb-3 bank-fields">
                        <label for="edit_bank_name" class="form-label">Bank Name</label>
                        <input type="text" class="form-control" id="edit_bank_name" name="bank_name" placeholder="e.g., HDFC Bank" maxlength="100">
                    </div>
                    <div class="row bank-fields">
                        <div class="col-md-6 mb-3">
                            <label for="edit_account_number" class="form-label">Account Number</label>
                            <input type="text" class="form-control" id="edit_account_number" name="account_number" placeholder="Account Number" maxlength="20">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_ifsc_code" class="form-label">IFSC Code</label>
                            <input type="text" class="form-control" id="edit_ifsc_code" name="ifsc_code" placeholder="IFSC Code" maxlength="11">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Update Account</button>
            </div>
        </div>
    </div>
</div>
@endpush
@endsection
@push('styles')
<style>
    .bank-filter, .bank-filter option {
        background-color: #fff !important;
        color: #333 !important;
    }
</style>
@endpush
@push('scripts')
<script>
function openTransactionModal(bankId, bankName) {
    document.getElementById('transactionForm').action = '/admin/banks/' + bankId + '/transactions';
    document.getElementById('txn_bank_name').value = bankName;
    document.getElementById('txn_amount').value = '';
    document.getElementById('txn_description').value = '';
    document.getElementById('type_credit').checked = true;
}
</script>
@endpush
