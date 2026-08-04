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
            @if(auth()->user()->hasPermission('banks', 'create'))
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBankModal">
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
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div class="main-card mb-3 card">
    <div class="card-header has-tabs d-flex justify-content-between align-items-center">
        <ul class="nav lead-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.banks.index') }}">
                    <i class="bi bi-bank me-1"></i> Active ({{ $banks->total() }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.banks.trashed') }}">
                    <i class="bi bi-archive me-1"></i> Deleted ({{ $inactiveCount }})
                </a>
            </li>
        </ul>
        <form method="GET" action="{{ route('admin.banks.index') }}" class="d-flex align-items-center gap-2">
            <div class="input-group input-group-sm" style="width: 320px;">
                <input type="text" name="search" class="form-control" placeholder="Search banks" value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            @if(request('search'))
                <a href="{{ route('admin.banks.index') }}" class="btn btn-sm btn-danger" title="Clear">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </form>
    </div>
    <div class="card-body">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th width="40" class="text-center">#</th>
                    <th>Account Name</th>
                    <th>Bank Details</th>
                    <th width="100" class="text-end">Opening</th>
                    <th width="100" class="text-end">Credit</th>
                    <th width="100" class="text-end">Debit</th>
                    <th width="120" class="text-end">Balance</th>
                    <th width="140" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banks as $index => $bank)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <a href="{{ route('admin.banks.show', $bank) }}"><strong>{{ $bank->bank_name }}</strong></a>
                        @if($bank->account_holder)
                        <br><small class="text-muted">{{ $bank->account_holder }}</small>
                        @endif
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
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <button class="btn btn-sm btn-outline-primary" title="Edit" onclick="openEditModal({{ json_encode($bank) }})" data-bs-toggle="modal" data-bs-target="#editBankModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="{{ route('admin.banks.show', $bank) }}" class="btn btn-sm btn-outline-info" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(!$bank->is_protected)
                            <form action="{{ route('admin.banks.destroy', $bank) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this bank account?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-archive"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="bi bi-bank fs-1 d-block mb-2"></i>
                        No bank accounts found.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($banks->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                    <td class="text-end"><strong>{{ formatMoney($banks->sum('opening_balance')) }}</strong></td>
                    <td class="text-end"><strong>{{ formatMoney($banks->sum('total_credit')) }}</strong></td>
                    <td class="text-end"><strong>{{ formatMoney($banks->sum('total_debit')) }}</strong></td>
                    <td class="text-end"><strong>{{ formatMoney($banks->sum('balance')) }}</strong></td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    <div class="card-footer">
        @include('partials.pagination', ['paginator' => $banks])
    </div>
</div>

@push('modals')

<div class="modal fade" id="addBankModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Bank Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.banks.store') }}" method="POST" id="addBankForm" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="account_type" class="form-label">Account Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="account_type" name="account_type">
                            <option value="">Select Type</option>
                            <option value="savings">Savings Account</option>
                            <option value="current">Current Account</option>
                            <option value="recurring">Recurring Account</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="bank_name" class="form-label">Account Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="bank_name" name="bank_name" placeholder="e.g., HDFC Current Account" maxlength="40">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="account_holder" class="form-label">Account Holder</label>
                        <input type="text" class="form-control" id="account_holder" name="account_holder" placeholder="Account holder name" maxlength="40">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="account_number" class="form-label">Account Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="account_number" name="account_number" placeholder="Account Number" maxlength="20">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ifsc_code" class="form-label">IFSC Code</label>
                            <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" placeholder="IFSC Code" maxlength="11">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="branch" class="form-label">Branch</label>
                        <input type="text" class="form-control" id="branch" name="branch" placeholder="Branch name" maxlength="30">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="opening_balance" class="form-label">Opening Balance</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" id="opening_balance" name="opening_balance" placeholder="0" step="1" max="999999999">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save Account</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="editBankModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Bank Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editBankForm" method="POST" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3" id="edit_account_type_section">
                        <label for="edit_account_type" class="form-label">Account Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_account_type" name="account_type">
                            <option value="">Select Type</option>
                            <option value="savings">Savings Account</option>
                            <option value="current">Current Account</option>
                            <option value="recurring">Recurring Account</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3" id="edit_bank_name_section">
                        <label for="edit_bank_name" class="form-label">Account Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_bank_name" name="bank_name" placeholder="e.g., HDFC Current Account" maxlength="40">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3" id="edit_account_holder_section">
                        <label for="edit_account_holder" class="form-label">Account Holder</label>
                        <input type="text" class="form-control" id="edit_account_holder" name="account_holder" placeholder="Account holder name" maxlength="40">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="row" id="edit_account_details_section">
                        <div class="col-md-6 mb-3">
                            <label for="edit_account_number" class="form-label">Account Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_account_number" name="account_number" placeholder="Account Number" maxlength="20">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_ifsc_code" class="form-label">IFSC Code</label>
                            <input type="text" class="form-control" id="edit_ifsc_code" name="ifsc_code" placeholder="IFSC Code" maxlength="11">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_branch_section">
                        <label for="edit_branch" class="form-label">Branch</label>
                        <input type="text" class="form-control" id="edit_branch" name="branch" placeholder="Branch name" maxlength="30">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_opening_balance" class="form-label">Opening Balance</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" id="edit_opening_balance" name="opening_balance" placeholder="0" step="1" max="999999999">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Update Account</button>
                </div>
            </form>
        </div>
    </div>
</div>


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
@endpush
@endsection

@push('styles')
<style>
    .card-header .form-select,
    .card-header .form-control {
        background-color: #fff !important;
        color: #333 !important;
    }
</style>
@endpush

@push('scripts')
<script>
const bankMaxLengths = {
    bank_name: 40,
    account_holder: 40,
    account_number: 20,
    ifsc_code: 11,
    branch: 30
};

const bankValidationRules = {
    account_type: { required: true },
    bank_name: { required: true },
    account_number: { required: true },
    opening_balance: { min: 0, max: 99999999 }
};

let editingProtectedBank = false;

document.querySelectorAll('#addBankForm input, #editBankForm input').forEach(input => {
    input.addEventListener('input', function() {
        const maxLen = bankMaxLengths[this.name];
        if (maxLen && this.value.length > maxLen) {
            this.value = this.value.substring(0, maxLen);
        }
    });
    input.addEventListener('keydown', function(e) {
        const maxLen = bankMaxLengths[this.name];
        if (maxLen && this.value.length >= maxLen && !['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'].includes(e.key)) {
            e.preventDefault();
        }
    });
});

function validateBankForm(form) {
    let isValid = true;
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    const isEditForm = form.id === 'editBankForm';
    const skipRequiredValidation = isEditForm && editingProtectedBank;

    form.querySelectorAll('input[name], select[name]').forEach(input => {
        const value = input.value.trim();
        const rule = bankValidationRules[input.name];

        if (rule?.required && !value && !skipRequiredValidation) {
            input.classList.add('is-invalid');
            isValid = false;
        } else if (input.name === 'opening_balance' && value) {
            const num = parseFloat(value);
            if (num < 0 || num > 9999999999) {
                input.classList.add('is-invalid');
                isValid = false;
            }
        }
    });
    return isValid;
}

document.getElementById('addBankForm').addEventListener('submit', function(e) {
    if (!validateBankForm(this)) e.preventDefault();
});

document.getElementById('editBankForm').addEventListener('submit', function(e) {
    if (!validateBankForm(this)) e.preventDefault();
});

document.getElementById('addBankModal').addEventListener('hidden.bs.modal', function() {
    const form = document.getElementById('addBankForm');
    form.reset();
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
});

document.getElementById('editBankModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('editBankForm').querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
});

function openEditModal(bank) {
    document.getElementById('editBankForm').action = '/admin/banks/' + bank.id;
    document.getElementById('edit_account_type').value = bank.account_type || 'current';
    document.getElementById('edit_bank_name').value = bank.bank_name || '';
    document.getElementById('edit_account_holder').value = bank.account_holder || '';
    document.getElementById('edit_account_number').value = bank.account_number || '';
    document.getElementById('edit_ifsc_code').value = bank.ifsc_code || '';
    document.getElementById('edit_branch').value = bank.branch || '';
    document.getElementById('edit_opening_balance').value = bank.opening_balance || 0;

    const isProtected = bank.is_protected;
    editingProtectedBank = isProtected;
    const protectedSections = ['edit_account_type_section', 'edit_bank_name_section', 'edit_account_holder_section', 'edit_account_details_section', 'edit_branch_section'];

    protectedSections.forEach(id => {
        const section = document.getElementById(id);
        if (section) {
            section.style.display = isProtected ? 'none' : '';
        }
    });
    
    const modalTitle = document.querySelector('#editBankModal .modal-title');
    if (modalTitle) {
        modalTitle.innerHTML = isProtected
            ? '<i class="bi bi-pencil me-2"></i>Edit Cash Account Balance'
            : '<i class="bi bi-pencil me-2"></i>Edit Bank Account';
    }
}

function openTransactionModal(bankId, bankName) {
    document.getElementById('transactionForm').action = '/admin/banks/' + bankId + '/transactions';
    document.getElementById('txn_bank_name').value = bankName;
    document.getElementById('txn_amount').value = '';
    document.getElementById('txn_description').value = '';
    document.getElementById('type_credit').checked = true;
}
</script>
@endpush
