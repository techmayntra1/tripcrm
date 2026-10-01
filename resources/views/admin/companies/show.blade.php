@extends('layouts.app')
@section('title', 'Company Details - ' . $company->name)
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                @if($company->logo_url)
                <img src="{{ $company->logo_url }}" alt="{{ $company->name }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                @else
                <i class="bi bi-building icon-gradient bg-amy-crisp"></i>
                @endif
            </div>
            <div>
                <span class="d-flex align-items-center gap-2">
                    {{ $company->name }}
                    <span class="badge {{ $company->trashed() ? 'bg-danger' : 'bg-success' }} fs-7">
                        {{ $company->trashed() ? 'Inactive' : 'Active' }}
                    </span>
                </span>
                <div class="page-title-subheading">
                    {{ $company->country_label }} ({{ $company->currency_code }})
                    @if($company->is_uae)
                        @if($company->vat_number) | VAT: {{ $company->vat_number }}@endif
                    @else
                        @if($company->gst_number) | GST: {{ $company->gst_number }}@endif
                        @if($company->pan_number) | PAN: {{ $company->pan_number }}@endif
                    @endif
                    @if($company->lrn_number) | LRN: {{ $company->lrn_number }}@endif
                </div>
            </div>
        </div>
        <div class="page-title-actions d-flex align-items-center gap-2">
            @can('companies.edit')
            <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit Company
            </a>
            @endcan
            <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary">
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


<div class="row g-3 mb-3">
    @php
        $stats = [
            ['label' => 'Total Income', 'value' => formatMoney($totalIncome, 0, $company), 'icon' => 'bi-graph-up-arrow', 'color' => 'success'],
            ['label' => 'Receivable', 'value' => formatMoney($totalReceivable, 0, $company), 'icon' => 'bi-hourglass-split', 'color' => 'warning'],
            ['label' => 'Bank Balance', 'value' => formatMoney($company->total_bank_balance, 0, $company), 'icon' => 'bi-bank', 'color' => 'info'],
            ['label' => 'Quotations', 'value' => $company->quotations->count(), 'icon' => 'bi-file-earmark-text', 'color' => 'primary'],
        ];
    @endphp
    @foreach($stats as $stat)
    <div class="col-sm-6 col-xl-3">
        <div class="card company-stat border-0 shadow-sm h-100 mb-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="company-stat-icon bg-{{ $stat['color'] }}-subtle text-{{ $stat['color'] }}">
                    <i class="bi {{ $stat['icon'] }}"></i>
                </div>
                <div class="min-w-0">
                    <div class="company-stat-label">{{ $stat['label'] }}</div>
                    <div class="company-stat-value text-truncate" title="{{ $stat['value'] }}">{{ $stat['value'] }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row">
    
    <div class="col-md-4">
        
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i> Company Information
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-5 text-muted">Contact Person</div>
                    <div class="col-7"><strong>{{ $company->contact_person ?: '-' }}</strong></div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Mobile</div>
                    <div class="col-7">
                        @if($company->phone)
                        <a href="tel:{{ $company->phone }}">{{ $company->phone }}</a>
                        @else
                        -
                        @endif
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Email</div>
                    <div class="col-7">
                        @if($company->email)
                        <a href="mailto:{{ $company->email }}">{{ $company->email }}</a>
                        @else
                        -
                        @endif
                    </div>
                </div>
                <hr>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Address</div>
                    <div class="col-7">{{ $company->address ?: '-' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">City / State</div>
                    <div class="col-7">
                        {{ $company->city ?: '' }}{{ $company->city && $company->state ? ', ' : '' }}{{ $company->state ?: '' }}
                        {{ $company->pincode ? ' - '.$company->pincode : '' }}
                        @if(!$company->city && !$company->state)-@endif
                    </div>
                </div>
            </div>
        </div>

        
        <div class="main-card mb-3 card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bank me-2"></i> Bank Accounts</span>
                <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i> Edit
                </a>
            </div>
            <div class="card-body p-0">
                @if($company->banks->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($company->banks as $bank)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">
                                    <i class="bi bi-bank2 me-1 text-info"></i>{{ $bank->bank_name }}
                                </h6>
                                <small class="text-muted">
                                    A/C: {{ $bank->account_number }} | {{ $bank->bank_code_label }}: {{ $bank->bank_code ?: '-' }}<br>
                                    Branch: {{ $bank->branch ?: '-' }} | Type: {{ ucfirst($bank->account_type) }}
                                </small>
                            </div>
                        </div>
                        <div class="mt-2 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted me-3">Opening: {{ formatMoney($bank->opening_balance, 0, $company) }}</span>
                                <span class="text-success me-3">Credit: {{ formatMoney($bank->total_credit, 0, $company) }}</span>
                                <span class="text-danger">Debit: {{ formatMoney($bank->total_debit, 0, $company) }}</span>
                            </div>
                            <strong class="text-primary fs-5">{{ formatMoney($bank->balance, 0, $company) }}</strong>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="card-footer bg-primary text-white d-flex justify-content-between">
                    <strong>Total Bank Balance</strong>
                    <strong class="fs-5">{{ formatMoney($company->total_bank_balance, 0, $company) }}</strong>
                </div>
                @else
                <div class="p-3 text-center text-muted">
                    <i class="bi bi-bank fs-1 d-block mb-2"></i>
                    No bank accounts assigned.
                    <a href="{{ route('admin.companies.edit', $company) }}">Assign bank accounts</a>
                </div>
                @endif
            </div>
        </div>

        
        <div class="main-card card">
            <div class="card-header">
                <i class="bi bi-bar-chart me-2"></i> Quick Stats
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="p-2 bg-light rounded">
                            <h4 class="mb-0 text-info">{{ $company->quotations->count() }}</h4>
                            <small class="text-muted">Quotations</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 bg-light rounded">
                            <h4 class="mb-0 text-warning">{{ $company->invoices->count() }}</h4>
                            <small class="text-muted">Invoices</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="col-md-8">
        
        <div class="main-card mb-3 card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-receipt me-2"></i> Recent Invoices</span>
                <a href="{{ route('admin.invoices.index') }}?company={{ $company->id }}" class="text-primary small">View All</a>
            </div>
            <div class="card-body p-0">
                @if($company->invoices->count() > 0)
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($company->invoices->take(5) as $invoice)
                        <tr>
                            <td>
                                <a href="{{ route('admin.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a>
                            </td>
                            <td>{{ $invoice->customer->name ?? '-' }}</td>
                            <td>{{ $invoice->date ? $invoice->date->format('d-m-Y') : '-' }}</td>
                            <td>{{ formatMoney($invoice->grand_total, 0, $company) }}</td>
                            <td>
                                @php
                                $statusColors = ['sent' => 'info', 'partial' => 'warning', 'paid' => 'success', 'overdue' => 'danger', 'cancelled' => 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$invoice->status] ?? 'secondary' }}">{{ ucfirst($invoice->status) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                    No invoices yet.
                </div>
                @endif
            </div>
        </div>

        
        <div class="main-card mb-3 card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-text me-2"></i> Recent Quotations</span>
                <a href="{{ route('admin.quotations.index') }}?company={{ $company->id }}" class="text-primary small">View All</a>
            </div>
            <div class="card-body p-0">
                @if($company->quotations->count() > 0)
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Quotation #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($company->quotations->take(5) as $quotation)
                        <tr>
                            <td>
                                <a href="{{ route('admin.quotations.show', $quotation) }}">{{ $quotation->quotation_number }}</a>
                            </td>
                            <td>{{ $quotation->customer->name ?? '-' }}</td>
                            <td>{{ $quotation->date ? $quotation->date->format('d-m-Y') : '-' }}</td>
                            <td>{{ formatMoney($quotation->grand_total, 0, $company) }}</td>
                            <td>
                                @php
                                $statusColors = ['sent' => 'info', 'accepted' => 'success', 'rejected' => 'danger', 'expired' => 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$quotation->status] ?? 'secondary' }}">{{ ucfirst($quotation->status) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-file-earmark-text fs-1 d-block mb-2"></i>
                    No quotations yet.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
@push('styles')
<style>
.company-stat { border-radius: 10px; }
.company-stat .card-body { padding: 1.1rem 1.25rem; }
.company-stat-icon {
    width: 48px; height: 48px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    border-radius: 10px; font-size: 1.35rem;
}
.company-stat-label { font-size: 13px; color: var(--vz-secondary-color, #878a99); margin-bottom: 2px; }
.company-stat-value { font-size: 1.35rem; font-weight: 600; color: var(--vz-heading-color, #212529); line-height: 1.3; }
.min-w-0 { min-width: 0; }
</style>
@endpush
