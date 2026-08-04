@extends('layouts.app')
@section('title', 'Salary Payment Details')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-receipt icon-gradient bg-success"></i>
            </div>
            <div>
                Salary Payment Details
                <div class="page-title-subheading">{{ $payment->expense_number }}</div>
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.staff.salary-payments.index', $staff) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i> Payment Information
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Staff Member</h6>
                        <p class="mb-0"><strong>{{ $staff->name }}</strong></p>
                        <small class="text-muted">{{ $staff->position_name }}</small>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Payment Date</h6>
                        <p class="mb-0"><strong>{{ formatDate($payment->expense_date) }}</strong></p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Payment Mode</h6>
                        <p class="mb-0">{{ $payment->payment_mode_display }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Reference Number</h6>
                        <p class="mb-0">{{ $payment->reference_number ?? '-' }}</p>
                    </div>
                </div>

                @if($payment->bank)
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h6 class="text-muted mb-1">Bank Account</h6>
                        <p class="mb-0">{{ $payment->bank->bank_name }} - {{ $payment->bank->account_number }}</p>
                    </div>
                </div>
                @endif

                <hr>

                <h6 class="mb-3"><i class="bi bi-list-ul me-2"></i>Salary Breakdown</h6>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th class="text-end" style="width: 150px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $additions = collect($items)->where('type', 'addition');
                            $deductions = collect($items)->whereIn('type', ['deduction', 'advance']);
                            $grossTotal = $additions->sum('amount');
                            $totalDeductions = $deductions->sum('amount');
                        @endphp

                        @foreach($additions as $item)
                        <tr>
                            <td>{{ $item['description'] }}</td>
                            <td class="text-end text-success">+ {{ formatMoney($item['amount']) }}</td>
                        </tr>
                        @endforeach

                        <tr class="table-secondary">
                            <td><strong>Gross Salary</strong></td>
                            <td class="text-end"><strong>{{ formatMoney($grossTotal) }}</strong></td>
                        </tr>

                        @foreach($deductions as $item)
                        <tr>
                            <td>{{ $item['description'] }}</td>
                            <td class="text-end text-danger">- {{ formatMoney($item['amount']) }}</td>
                        </tr>
                        @endforeach

                        @if($totalDeductions > 0)
                        <tr class="table-warning">
                            <td><strong>Total Deductions</strong></td>
                            <td class="text-end text-danger"><strong>- {{ formatMoney($totalDeductions) }}</strong></td>
                        </tr>
                        @endif

                        <tr class="table-success">
                            <td><strong>Net Amount Paid</strong></td>
                            <td class="text-end"><strong>{{ formatMoney($payment->grand_total) }}</strong></td>
                        </tr>
                    </tbody>
                </table>

                @if($payment->notes)
                <hr>
                <h6 class="mb-2"><i class="bi bi-sticky me-2"></i>Notes</h6>
                <p class="mb-0">{{ $payment->notes }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="main-card mb-3 card">
            <div class="card-header bg-success text-white">
                <i class="bi bi-check-circle me-2"></i> Payment Summary
            </div>
            <div class="card-body text-center">
                <h2 class="text-success mb-1">{{ formatMoney($payment->grand_total) }}</h2>
                <p class="text-muted mb-0">Net Amount Paid</p>
            </div>
        </div>

        <div class="main-card mb-3 card">
            <div class="card-body">
                <h6 class="text-muted mb-3">Quick Info</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span>Expense Number</span>
                    <strong>{{ $payment->expense_number }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Status</span>
                    <span class="badge bg-success">Paid</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Created</span>
                    <span>{{ $payment->created_at->format('d-m-Y, h:i A') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
