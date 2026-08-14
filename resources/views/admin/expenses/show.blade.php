@extends('layouts.app')
@section('title', 'Expense Details')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-receipt icon-gradient bg-danger"></i>
            </div>
            <div>
                {{ $expense->expense_number }}
                <div class="page-title-subheading">Expense Details</div>
            </div>
        </div>
        <div class="page-title-actions">
            @if(isset($fromTrip) && $fromTrip)
                <a href="{{ route('admin.trips.show', $fromTrip) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Trip
                </a>
            @elseif($expense->trip_id)
                <a href="{{ route('admin.trips.show', $expense->trip_id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Trip
                </a>
            @elseif($expense->vendor_id)
                <a href="{{ route('admin.vendors.show', $expense->vendor_id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Vendor
                </a>
            @else
                <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            @endif
            @can('expenses.edit')
            @if($expense->payment_status !== 'paid')
            <a href="{{ route('admin.expenses.edit', $expense) }}{{ isset($fromTrip) && $fromTrip ? '?from_trip='.$fromTrip : '' }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            @endif
            @endcan
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-8">
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i> Expense Information
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Expense Type</small>
                        @php
                            $typeColors = [
                                'trip' => 'bg-warning',
                                'vendor' => 'bg-info',
                                'general' => 'bg-secondary',
                                'salary' => 'bg-primary',
                            ];
                        @endphp
                        <span class="badge {{ $typeColors[$expense->expense_type] ?? 'bg-secondary' }}">
                            {{ $expense->expense_type_display }}
                        </span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Date</small>
                        <strong>{{ formatDate($expense->expense_date) }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Payment Mode</small>
                        <strong>{{ $expense->paymentMode->name ?? '-' }}</strong>
                    </div>
                </div>
                <div class="row mb-3">
                    @if($expense->vendor)
                    <div class="col-md-4">
                        <small class="text-muted d-block">Vendor</small>
                        <a href="{{ route('admin.vendors.show', $expense->vendor) }}">
                            <strong>{{ $expense->vendor->name }}</strong>
                        </a>
                    </div>
                    @endif
                    @if($expense->trip)
                    <div class="col-md-4">
                        <small class="text-muted d-block">Trip</small>
                        <a href="{{ route('admin.trips.show', $expense->trip) }}">
                            <strong>{{ $expense->trip->trip_number }} - {{ $expense->trip->name }}</strong>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @if(is_array($expense->items) && count($expense->items) > 0)
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-list-check me-2"></i> Expense Items ({{ count($expense->items) }})
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="sticky-top bg-white">
                            <tr>
                                <th width="50">#</th>
                                <th>Description</th>
                                <th width="80">Unit</th>
                                <th width="80" class="text-end">Qty</th>
                                <th width="120" class="text-end">Rate</th>
                                <th width="120" class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expense->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><span class="d-inline-block text-truncate" style="max-width: 250px;" title="{{ $item['description'] ?? '-' }}">{{ Str::limit($item['description'] ?? '-', 40) }}</span></td>
                                <td>{{ strtoupper($item['unit'] ?? '-') }}</td>
                                <td class="text-end">{{ $item['quantity'] ?? 0 }}</td>
                                <td class="text-end">{{ formatMoney($item['rate'] ?? 0) }}</td>
                                <td class="text-end">{{ formatMoney($item['total'] ?? 0) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="5" class="text-end"><strong>Sub Total:</strong></td>
                                <td class="text-end"><strong>{{ formatMoney($expense->sub_total) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end"><strong>Grand Total:</strong></td>
                                <td class="text-end"><strong class="text-danger">{{ formatMoney($expense->grand_total) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @elseif($expense->attachment || $expense->description)
        <div class="main-card mb-3 card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-receipt me-2"></i> Bill/Receipt</span>
                @if($expense->attachment)
                <a href="{{ asset('storage/' . $expense->attachment) }}" target="_blank" class="btn btn-sm btn-warning text-dark" download>
                    <i class="bi bi-download me-1"></i> Download
                </a>
                @endif
            </div>
            <div class="card-body">
                @if($expense->description)
                <p class="mb-3">{{ $expense->description }}</p>
                @endif
                @if($expense->attachment)
                    @php
                        $ext = strtolower(pathinfo($expense->attachment, PATHINFO_EXTENSION));
                    @endphp
                    @if(in_array($ext, ['jpg', 'jpeg', 'png']))
                        <a href="{{ asset('storage/' . $expense->attachment) }}" target="_blank">
                            <img src="{{ asset('storage/' . $expense->attachment) }}" alt="Bill/Receipt" class="img-fluid rounded" style="max-height: 300px;">
                        </a>
                    @else
                        <a href="{{ asset('storage/' . $expense->attachment) }}" target="_blank" class="btn btn-warning text-dark">
                            <i class="bi bi-file-earmark-pdf me-1"></i> View PDF
                        </a>
                    @endif
                @endif
            </div>
        </div>
        @endif
    </div>
    <div class="col-md-4">
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-calculator me-2"></i> Amount Details
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td>Sub Total</td>
                        <td class="text-end">{{ formatMoney($expense->sub_total) }}</td>
                    </tr>
                    <tr class="border-top">
                        <td><strong>Grand Total</strong></td>
                        <td class="text-end text-danger"><strong>{{ formatMoney($expense->grand_total) }}</strong></td>
                    </tr>
                    <tr>
                        <td>Paid Amount</td>
                        <td class="text-end text-success">{{ formatMoney($expense->paid_amount) }}</td>
                    </tr>
                    <tr class="border-top">
                        <td><strong>Balance</strong></td>
                        <td class="text-end {{ $expense->balance > 0 ? 'text-danger' : 'text-success' }}">
                            <strong>{{ formatMoney($expense->balance) }}</strong>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-check-circle me-2"></i> Payment Status
            </div>
            <div class="card-body text-center py-4">
                @if($expense->payment_status == 'paid')
                    <span class="badge bg-success fs-5 px-4 py-2">Paid</span>
                @elseif($expense->payment_status == 'partial')
                    <span class="badge bg-warning fs-5 px-4 py-2">Partial</span>
                @else
                    <span class="badge bg-danger fs-5 px-4 py-2">Unpaid</span>
                @endif
            </div>
        </div>
        @if($expense->bank)
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-bank me-2"></i> Bank / Cash Account
            </div>
            <div class="card-body">
                <a href="{{ route('admin.banks.show', $expense->bank) }}">
                    <strong>{{ $expense->bank->bank_name }}</strong>
                </a>
                @if($expense->bank->account_number)
                <div class="text-muted">A/C: XXXX{{ substr($expense->bank->account_number, -4) }}</div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
