@extends('layouts.app')
@section('title', 'Income Details')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-graph-up-arrow icon-gradient bg-success"></i>
            </div>
            <div>
                {{ $income->receipt_number ?? 'Income Details' }}
                <div class="page-title-subheading">Income Details</div>
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.income.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            @can('income.edit')
            <a href="{{ route('admin.income.edit', $income) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            @endcan
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-8">
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i> Income Information
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Income Type</small>
                        @php
                            $typeColors = [
                                'project' => 'bg-primary',
                                'advance' => 'bg-info',
                                'other' => 'bg-secondary',
                            ];
                            $typeLabels = [
                                'project' => 'Project Payment',
                                'advance' => 'Advance Payment',
                                'other' => 'Other Income',
                            ];
                        @endphp
                        <span class="badge {{ $typeColors[$income->income_type] ?? 'bg-secondary' }}">
                            {{ $typeLabels[$income->income_type] ?? ucfirst($income->income_type) }}
                        </span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Date</small>
                        <strong>{{ formatDate($income->income_date) }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Payment Mode</small>
                        <strong>{{ $income->paymentMode->name ?? '-' }}</strong>
                    </div>
                </div>
                <div class="row mb-3">
                    @if($income->customer)
                    <div class="col-md-4">
                        <small class="text-muted d-block">Customer</small>
                        <a href="{{ route('admin.customers.show', $income->customer) }}">
                            <strong>{{ $income->customer->name }}</strong>
                        </a>
                    </div>
                    @endif
                    @if($income->project)
                    <div class="col-md-4">
                        <small class="text-muted d-block">Project</small>
                        <a href="{{ route('admin.projects.show', $income->project) }}">
                            <strong>{{ $income->project->project_number }} - {{ $income->project->name }}</strong>
                        </a>
                    </div>
                    @endif
                    @if($income->invoice)
                    <div class="col-md-4">
                        <small class="text-muted d-block">Against Invoice</small>
                        <a href="{{ route('admin.invoices.show', $income->invoice) }}">
                            <strong>{{ $income->invoice->invoice_number }}</strong>
                        </a>
                    </div>
                    @endif
                </div>
                @if(optional($income->paymentMode)->slug == 'cheque' && ($income->cheque_number || $income->cheque_date || $income->bank_name))
                <div class="row mb-3">
                    @if($income->cheque_number)
                    <div class="col-md-4">
                        <small class="text-muted d-block">Cheque Number</small>
                        <strong>{{ $income->cheque_number }}</strong>
                    </div>
                    @endif
                    @if($income->cheque_date)
                    <div class="col-md-4">
                        <small class="text-muted d-block">Cheque Date</small>
                        <strong>{{ formatDate($income->cheque_date) }}</strong>
                    </div>
                    @endif
                    @if($income->bank_name)
                    <div class="col-md-4">
                        <small class="text-muted d-block">Bank Name</small>
                        <strong>{{ $income->bank_name }}</strong>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
        @if($income->attachment)
        <div class="main-card mb-3 card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-receipt me-2"></i> Bill/Receipt</span>
                <a href="{{ asset('storage/' . $income->attachment) }}" target="_blank" class="btn btn-sm btn-success" download>
                    <i class="bi bi-download me-1"></i> Download
                </a>
            </div>
            <div class="card-body">
                @php
                    $ext = strtolower(pathinfo($income->attachment, PATHINFO_EXTENSION));
                @endphp
                @if(in_array($ext, ['jpg', 'jpeg', 'png']))
                    <a href="{{ asset('storage/' . $income->attachment) }}" target="_blank">
                        <img src="{{ asset('storage/' . $income->attachment) }}" alt="Bill/Receipt" class="img-fluid rounded" style="max-height: 300px;">
                    </a>
                @else
                    <a href="{{ asset('storage/' . $income->attachment) }}" target="_blank" class="btn btn-success">
                        <i class="bi bi-file-earmark-pdf me-1"></i> View PDF
                    </a>
                @endif
            </div>
        </div>
        @endif
        @if($income->description)
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-card-text me-2"></i> Description
            </div>
            <div class="card-body">
                {{ $income->description }}
            </div>
        </div>
        @endif
    </div>
    <div class="col-md-4">
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-calculator me-2"></i> Amount
            </div>
            <div class="card-body text-center py-4">
                <span class="fs-3 text-success fw-bold">{{ formatMoney($income->amount) }}</span>
            </div>
        </div>
        @if($income->bank)
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-bank me-2"></i> Deposited To
            </div>
            <div class="card-body">
                <a href="{{ route('admin.banks.show', $income->bank) }}">
                    <strong>{{ $income->bank->bank_name }}</strong>
                </a>
                @if($income->bank->account_number)
                <div class="text-muted">A/C: XXXX{{ substr($income->bank->account_number, -4) }}</div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
