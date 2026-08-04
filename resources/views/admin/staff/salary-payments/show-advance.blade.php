@extends('layouts.app')
@section('title', 'Advance Details')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-cash icon-gradient bg-warning"></i>
            </div>
            <div>
                Salary Advance Details
                <div class="page-title-subheading">{{ $staff->name }}</div>
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
                <i class="bi bi-info-circle me-2"></i> Advance Information
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Staff Member</h6>
                        <p class="mb-0"><strong>{{ $staff->name }}</strong></p>
                        <small class="text-muted">{{ $staff->position_name }}</small>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Advance Date</h6>
                        <p class="mb-0"><strong>{{ formatDate($advance->advance_date) }}</strong></p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Payment Mode</h6>
                        <p class="mb-0">{{ $advance->payment_mode_display }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Reference Number</h6>
                        <p class="mb-0">{{ $advance->reference_number ?? '-' }}</p>
                    </div>
                </div>

                @if($advance->bank)
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h6 class="text-muted mb-1">Bank Account</h6>
                        <p class="mb-0">{{ $advance->bank->bank_name }} - {{ $advance->bank->account_number }}</p>
                    </div>
                </div>
                @endif

                @if($advance->reason)
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h6 class="text-muted mb-1">Reason</h6>
                        <p class="mb-0">{{ $advance->reason }}</p>
                    </div>
                </div>
                @endif

                @if($advance->deductions->count() > 0)
                <hr>
                <h6 class="mb-3"><i class="bi bi-list-check me-2"></i>Deduction History</h6>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Salary Payment</th>
                            <th class="text-end">Amount Deducted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($advance->deductions as $deduction)
                        <tr>
                            <td>{{ formatDate($deduction->deduction_date) }}</td>
                            <td>
                                <a href="{{ route('admin.staff.salary-payments.show', [$staff, $deduction->expense]) }}">
                                    {{ $deduction->expense->expense_number }}
                                </a>
                            </td>
                            <td class="text-end text-success">{{ formatMoney($deduction->deduction_amount) }}</td>
                        </tr>
                        @endforeach
                        <tr class="table-success">
                            <td colspan="2"><strong>Total Recovered</strong></td>
                            <td class="text-end"><strong>{{ formatMoney($advance->total_deducted) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="main-card mb-3 card">
            <div class="card-header bg-{{ $advance->status_color }} text-white">
                <i class="bi bi-cash me-2"></i> Advance Summary
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <span class="badge bg-{{ $advance->status_color }} fs-6">{{ $advance->status_display }}</span>
                </div>

                <table class="table table-sm">
                    <tr>
                        <td>Total Advance</td>
                        <td class="text-end"><strong>{{ formatMoney($advance->amount) }}</strong></td>
                    </tr>
                    <tr>
                        <td>Recovered</td>
                        <td class="text-end text-success">{{ formatMoney($advance->total_deducted) }}</td>
                    </tr>
                    <tr class="table-warning">
                        <td><strong>Remaining</strong></td>
                        <td class="text-end"><strong class="text-warning">{{ formatMoney($advance->remaining_amount) }}</strong></td>
                    </tr>
                </table>

                @if($advance->remaining_amount > 0)
                <div class="progress mt-3" style="height: 20px;">
                    @php
                        $percentage = (($advance->amount - $advance->remaining_amount) / $advance->amount) * 100;
                    @endphp
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%">
                        {{ number_format($percentage, 0) }}% Recovered
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="main-card mb-3 card">
            <div class="card-body">
                <h6 class="text-muted mb-3">Timeline</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span>Created</span>
                    <span>{{ $advance->created_at->format('d-m-Y, h:i A') }}</span>
                </div>
                @if($advance->updated_at != $advance->created_at)
                <div class="d-flex justify-content-between mb-2">
                    <span>Last Updated</span>
                    <span>{{ $advance->updated_at->format('d-m-Y, h:i A') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
