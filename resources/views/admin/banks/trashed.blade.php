@extends('layouts.app')
@section('title', 'Deleted Bank Accounts')
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
                <i class="bi bi-plus-lg"></i>
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
<div class="main-card mb-3 card">
    <div class="card-header has-tabs d-flex justify-content-between align-items-center">
        <ul class="nav lead-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.banks.index') }}">
                    <i class="bi bi-bank me-1"></i> Active ({{ $activeCount }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.banks.trashed') }}">
                    <i class="bi bi-archive me-1"></i> Deleted ({{ $banks->total() }})
                </a>
            </li>
        </ul>
        <form method="GET" action="{{ route('admin.banks.trashed') }}" class="d-flex align-items-center gap-2">
            <div class="input-group input-group-sm" style="width: 320px;">
                <input type="text" name="search" class="form-control" placeholder="Search banks" value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            @if(request('search'))
                <a href="{{ route('admin.banks.trashed') }}" class="btn btn-sm btn-danger" title="Clear">
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
                    <th width="120" class="text-end">Balance</th>
                    <th width="80" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banks as $index => $bank)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $bank->bank_name }}</strong>
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
                        @if($bank->bank_code)
                        <br><small class="text-muted">{{ $bank->bank_code_label }}: {{ $bank->bank_code }}</small>
                        @endif
                    </td>
                    <td class="text-end">{{ formatMoney($bank->opening_balance, 0, $bank) }}</td>
                    <td class="text-end"><strong class="{{ $bank->balance >= 0 ? 'text-primary' : 'text-danger' }}">{{ formatMoney($bank->balance, 0, $bank) }}</strong></td>
                    <td class="text-center">
                        <form action="{{ route('admin.banks.restore', $bank->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Restore this bank account?')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        <i class="bi bi-archive fs-1 d-block mb-2"></i>
                        No deleted bank accounts found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        @include('partials.pagination', ['paginator' => $banks])
    </div>
</div>

@push('modals')

<div class="modal fade" id="addBankModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Bank Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.banks.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="account_type" class="form-label">Account Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="account_type" name="account_type" required>
                            <option value="">Select Type</option>
                            <option value="savings">Savings Account</option>
                            <option value="current">Current Account</option>
                            <option value="recurring">Recurring Account</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="bank_name" class="form-label">Account Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="bank_name" name="bank_name" placeholder="e.g., HDFC Current Account" required maxlength="40">
                    </div>
                    <div class="mb-3">
                        <label for="country" class="form-label">Region <span class="text-danger">*</span></label>
                        <select class="form-select" id="country" name="country" required>
                            @foreach(\App\Models\Company::COUNTRIES as $key => $label)
                                <option value="{{ $key }}" data-currency="{{ currencySymbol($key) }}">{{ $label }} ({{ currencyCode($key) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="account_holder" class="form-label">Account Holder</label>
                        <input type="text" class="form-control" id="account_holder" name="account_holder" placeholder="Account holder name" maxlength="40">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="account_number" class="form-label">Account Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="account_number" name="account_number" placeholder="Account Number" maxlength="20" required>
                        </div>
                        <div class="col-md-6 mb-3 bank-field-india">
                            <label for="ifsc_code" class="form-label">IFSC Code</label>
                            <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" placeholder="IFSC Code" maxlength="11">
                        </div>
                        <div class="col-md-6 mb-3 bank-field-uae">
                            <label for="iban" class="form-label">IBAN</label>
                            <input type="text" class="form-control" id="iban" name="iban" placeholder="AE07 0331 2345 6789 0123 456" maxlength="34">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="branch" class="form-label">Branch</label>
                        <input type="text" class="form-control" id="branch" name="branch" placeholder="Branch name" maxlength="30">
                    </div>
                    <div class="mb-3">
                        <label for="opening_balance" class="form-label">Opening Balance</label>
                        <div class="input-group">
                            <span class="input-group-text" id="opening_balance_symbol">₹</span>
                            <input type="number" class="form-control" id="opening_balance" name="opening_balance" placeholder="0" min="0" step="1">
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
@endpush
@endsection
@push('scripts')
<script>
(function () {
    const select = document.getElementById('country');
    if (!select) return;
    function apply() {
        const country = select.value || 'india';
        document.querySelectorAll('.bank-field-india').forEach(el => el.style.display = country === 'india' ? '' : 'none');
        document.querySelectorAll('.bank-field-uae').forEach(el => el.style.display = country === 'uae' ? '' : 'none');
        document.getElementById('opening_balance_symbol').textContent = select.options[select.selectedIndex]?.dataset.currency || '₹';
    }
    select.addEventListener('change', apply);
    apply();
})();
</script>
@endpush
@push('styles')
<style>
    .card-header .form-select,
    .card-header .form-control {
        background-color: #fff !important;
        color: #333 !important;
    }
</style>
@endpush
