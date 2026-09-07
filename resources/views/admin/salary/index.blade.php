@extends('layouts.app')
@section('title', 'Salary')
@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-cash-stack icon-gradient bg-deep-blue"></i>
            </div>
            <div>
                Salary
                <div class="page-title-subheading">All staff salary payments</div>
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.salary.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Salary
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-xl-4">
        <div class="card mb-3 bg-midnight-bloom text-white text-center" style="border-radius:14px;overflow:hidden;">
            <div class="card-body py-3">
                <div class="text-uppercase fw-bold mb-1" style="letter-spacing:.5px;">Paid This Month</div>
                <div class="fs-3 fw-bold">{{ formatMoney($totalPaidThisMonth) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card mb-3 bg-plum-plate text-white text-center" style="border-radius:14px;overflow:hidden;">
            <div class="card-body py-3">
                <div class="text-uppercase fw-bold mb-1" style="letter-spacing:.5px;">Paid This Year</div>
                <div class="fs-3 fw-bold">{{ formatMoney($totalPaidThisYear) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="main-card mb-3 card">
    <div class="card-header has-tabs d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-cash-coin me-2"></i> Salary Payments</h6>
        <form method="GET" class="d-flex gap-2 align-items-center mb-0">
            <select name="staff_id" class="form-select form-select-sm" style="width: 150px;" onchange="this.form.submit()">
                <option value="">All Staff</option>
                @foreach($staffList as $s)
                <option value="{{ $s->id }}" {{ request('staff_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
            @if(request('staff_id'))
            <a href="{{ route('admin.salary.index') }}" class="btn btn-sm btn-danger" title="Clear Filter">
                <i class="bi bi-x-lg"></i>
            </a>
            @endif
        </form>
    </div>
    <div class="card-body">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>SR</th>
                    <th>Date</th>
                    <th>Staff</th>
                    <th>Description</th>
                    <th>Payment Mode</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salaryPayments as $payment)
                <tr>
                    <td>{{ $loop->iteration + ($salaryPayments->currentPage() - 1) * $salaryPayments->perPage() }}</td>
                    <td>{{ formatDate($payment->expense_date) }}</td>
                    <td>
                        @if($payment->staff)
                        <a href="{{ route('admin.staff.show', $payment->staff) }}">{{ $payment->staff->name }}</a>
                        @else
                        -
                        @endif
                    </td>
                    <td><span class="desc-truncate">{{ $payment->description ?? '-' }}</span></td>
                    <td>{{ $payment->paymentMode->name ?? '-' }}</td>
                    <td class="text-end">{{ formatMoney($payment->grand_total) }}</td>
                    <td class="text-end">
                        @if($payment->staff)
                        <a href="{{ route('admin.staff.salary-payments.show', [$payment->staff, $payment]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i>
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        No salary payments found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($salaryPayments->hasPages())
    <div class="card-footer">
        {{ $salaryPayments->links() }}
    </div>
    @endif
</div>
@endsection
