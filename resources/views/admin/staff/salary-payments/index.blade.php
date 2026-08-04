@extends('layouts.app')
@section('title', 'Salary Payments - ' . $staff->name)
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
                Salary Payments - {{ $staff->name }}
                <div class="page-title-subheading">{{ $staff->position_name }} | Monthly Salary: {{ formatMoney($staff->salary_amount) }}</div>
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            <a href="{{ route('admin.staff.advances.create', $staff) }}" class="btn btn-warning">
                <i class="bi bi-plus-lg me-1"></i> Make Advance
            </a>
            <a href="{{ route('admin.staff.salary-payments.create', $staff) }}" class="btn btn-success">
                <i class="bi bi-plus-lg me-1"></i> Make Salary
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="main-card mb-3 card">
            <div class="card-header bg-success text-white">
                <i class="bi bi-cash-coin me-2"></i> Salary Payments
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th >SR</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $salaryOnly = $salaryPayments->filter(fn($p) => !str_contains($p->description, 'Advance'));
                        @endphp
                        @forelse($salaryOnly as $payment)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ formatDate($payment->expense_date) }}</td>
                            <td><span class="desc-truncate">{{ $payment->description ?? '-' }}</span></td>
                            <td class="text-end text-success"><strong>{{ formatMoney($payment->grand_total) }}</strong></td>
                            <td>
                                <div class="d-flex gap-1">
                                <a href="{{ route('admin.staff.salary-payments.show', [$staff, $payment]) }}" class="btn btn-sm btn-outline-info" title="Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.staff.salary-payments.edit', [$staff, $payment]) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.staff.salary-payments.destroy', [$staff, $payment]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this salary payment? Any advance deductions will be reversed.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="empty-state py-3">
                                <i class="bi bi-cash-coin"></i>
                                No salary payments found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="main-card mb-3 card">
            <div class="card-header bg-success text-white">
                <i class="bi bi-cash me-2"></i> Advance Payments
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th >SR</th>
                            <th>Date</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Remaining</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($advances as $advance)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ formatDate($advance->advance_date) }}</td>
                            <td class="text-end">{{ formatMoney($advance->amount) }}</td>
                            <td class="text-end">
                                @if($advance->remaining_amount > 0)
                                    <span class="text-warning">{{ formatMoney($advance->remaining_amount) }}</span>
                                @else
                                    <span class="text-success">{{ formatMoney(0) }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $advance->status_color }}">{{ $advance->status_display }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.staff.advances.show', [$staff, $advance]) }}" class="btn btn-sm btn-outline-info" title="Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="empty-state py-3">
                                <i class="bi bi-cash"></i>
                                No advances given
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
