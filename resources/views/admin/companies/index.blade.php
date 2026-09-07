@extends('layouts.app')
@section('title', 'Companies')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-building icon-gradient bg-amy-crisp"></i>
            </div>
            <div>
                Companies
                <div class="page-title-subheading">Manage your company profiles and financial data</div>
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.companies.create') }}" class="btn btn-primary" title="Add Company">
                <i class="bi bi-plus-lg me-1"></i> Add Company
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


<div class="row">
    @forelse($companies as $company)
    <div class="col-md-6 mb-3">
        <div class="main-card card {{ $company->trashed() ? 'opacity-75' : '' }}">
            <div class="card-header {{ $company->trashed() ? 'bg-secondary' : 'bg-info' }} text-white d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="bi bi-building me-2 fs-5"></i>
                    <div>
                        <h6 class="mb-0">{{ $company->name }}</h6>
                        <small>{{ $company->gst_number ? 'GST: '.$company->gst_number : 'GST: -' }}</small>
                    </div>
                </div>
                <span class="badge {{ $company->trashed() ? 'bg-danger' : 'bg-success' }}">
                    {{ $company->trashed() ? 'Inactive' : 'Active' }}
                </span>
            </div>
            <div class="card-body">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Contact Person</small>
                        <span>{{ $company->contact_person ?: '-' }}</span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Mobile</small>
                        <span>{{ $company->phone ?: '-' }}</span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Email</small>
                        <span>{{ $company->email ?: '-' }}</span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">PAN</small>
                        <span>{{ $company->pan_number ?: '-' }}</span>
                    </div>
                </div>

                
                @if($company->banks->count() > 0)
                <div class="mb-3">
                    <h6 class="mb-2"><i class="bi bi-bank me-1"></i> Bank Accounts</h6>
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Bank</th>
                                <th>Account No.</th>
                                <th class="text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($company->banks as $bank)
                            <tr>
                                <td>
                                    <i class="bi bi-bank2 me-1 text-info"></i>{{ $bank->bank_name }}
                                </td>
                                <td>XXXX {{ substr($bank->account_number, -4) }}</td>
                                <td class="text-end text-success">{{ formatMoney($bank->balance) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-primary">
                            <tr>
                                <th colspan="2">Total Balance</th>
                                <th class="text-end">{{ formatMoney($company->total_bank_balance) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="alert alert-warning mb-3 py-2">
                    <i class="bi bi-exclamation-triangle me-1"></i> No bank accounts assigned
                </div>
                @endif
            </div>
            <div class="card-footer bg-white d-flex justify-content-between">
                <div>
                    <span class="badge bg-info me-1">{{ $company->quotations->count() }} Quotations</span>
                    <span class="badge bg-warning text-dark me-1">{{ $company->invoices->count() }} Invoices</span>
                </div>
                <div class="d-flex gap-1">
                    <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-sm btn-outline-info" title="Details">
                        <i class="bi bi-eye"></i>
                    </a>
                    <form action="{{ route('admin.companies.toggle', $company->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to {{ $company->trashed() ? 'activate' : 'deactivate' }} this company?')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-{{ $company->trashed() ? 'success' : 'danger' }}" title="{{ $company->trashed() ? 'Activate' : 'Delete' }}">
                            <i class="bi bi-{{ $company->trashed() ? 'play' : 'pause' }}"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i> No companies found. <a href="{{ route('admin.companies.create') }}">Add your first company</a>.
        </div>
    </div>
    @endforelse
</div>
@endsection
