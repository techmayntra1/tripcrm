@extends('layouts.app')
@section('title', 'Salary Records - ' . $staff->name)
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
                Salary Records - {{ $staff->name }}
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.staff.show', $staff) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            <a href="{{ route('admin.expenses.create', ['expense_type' => 'salary', 'staff_id' => $staff->id, 'description' => 'Salary - ' . $staff->name]) }}" class="btn btn-success">
                <i class="bi bi-plus-lg me-1"></i> Add Salary Payment
            </a>
        </div>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card widget-content bg-midnight-bloom">
            <div class="widget-content-wrapper text-white">
                <div class="widget-content-left">
                    <div class="widget-heading">Total Paid</div>
                    <div class="widget-subheading">This Year</div>
                </div>
                <div class="widget-content-right">
                    <div class="widget-numbers text-white"><span>{{ formatMoney($totalPaidThisYear, true) }}</span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card widget-content bg-grow-early">
            <div class="widget-content-wrapper text-white">
                <div class="widget-content-left">
                    <div class="widget-heading">Base Salary</div>
                    <div class="widget-subheading">{{ ucfirst($staff->salary_type) }}</div>
                </div>
                <div class="widget-content-right">
                    <div class="widget-numbers text-white"><span>{{ formatMoney($staff->salary_amount, true) }}</span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card widget-content bg-info">
            <div class="widget-content-wrapper text-white">
                <div class="widget-content-left">
                    <div class="widget-heading">Overtime Rate</div>
                    <div class="widget-subheading">Per Hour</div>
                </div>
                <div class="widget-content-right">
                    <div class="widget-numbers text-white"><span>{{ formatMoney($staff->overtime_rate, true) }}</span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card widget-content bg-happy-green">
            <div class="widget-content-wrapper text-white">
                <div class="widget-content-left">
                    <div class="widget-heading">Total Records</div>
                    <div class="widget-subheading">Salary payments</div>
                </div>
                <div class="widget-content-right">
                    <div class="widget-numbers text-white"><span>{{ $salaryRecords->count() }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-table me-2"></i> Salary Payment Records
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Payment Mode</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                        <tbody>
                            @forelse($salaryRecords as $record)
                            <tr>
                                <td>{{ formatDate($record->expense_date) }}</td>
                                <td>{{ $record->description }}</td>
                                <td class="text-success"><strong>{{ formatMoney($record->grand_total) }}</strong></td>
                                <td>{{ ucfirst(str_replace('_', ' ', $record->payment_mode ?? 'cash')) }}</td>
                                <td><span class="badge bg-success">Paid</span></td>
                                <td>
                                    <div class="btn-group-actions">
                                        <a href="{{ route('admin.expenses.show', $record) }}" class="btn btn-sm btn-outline-info" title="Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($record->payment_status !== 'paid')
                                        <a href="{{ route('admin.expenses.edit', $record) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="empty-state">
                                    <i class="bi bi-receipt"></i>
                                    No salary records found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($salaryRecords->count() > 0)
                        <tfoot>
                            <tr>
                                <td colspan="2"><strong>Total</strong></td>
                                <td class="text-success"><strong>{{ formatMoney($salaryRecords->sum('grand_total')) }}</strong></td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
            <div class="card-footer text-muted">
                Showing {{ $salaryRecords->count() }} {{ Str::plural('record', $salaryRecords->count()) }}
            </div>
        </div>
    </div>
</div>
@endsection
