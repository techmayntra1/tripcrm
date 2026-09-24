@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.app')
@section('title', 'View Invoice')
@section('currency_symbol', currencySymbol($invoice))
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-receipt-cutoff icon-gradient bg-grow-early"></i>
            </div>
            <div>
                Invoice #{{ $invoice->invoice_number }}
            </div>
        </div>
        <div class="page-title-actions">
            @if(isset($fromTrip) && $fromTrip)
                <a href="{{ route('admin.trips.show', $fromTrip) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Trip
                </a>
            @elseif($invoice->trip_id)
                <a href="{{ route('admin.trips.show', $invoice->trip_id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Trip
                </a>
            @else
                <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            @endif
            @can('invoices.edit')
            @if($invoice->status !== 'paid')
            <a href="{{ route('admin.invoices.edit', $invoice) }}{{ isset($fromTrip) && $fromTrip ? '?from_trip='.$fromTrip : '' }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            @endif
            @endcan
            <a href="{{ route('admin.invoices.download', $invoice) }}" class="btn btn-secondary">
                <i class="bi bi-download me-1"></i> Download
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
    <div class="col-md-8">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        @if($invoice->company)
                        @if($invoice->company->logo_url)
                        <img src="{{ $invoice->company->logo_url }}" alt="{{ $invoice->company->name }}" class="mb-2" style="height: 70px; width: auto;">
                        @endif
                        <h4 class="mb-1">{{ $invoice->company->name }}</h4>
                        @if($invoice->company->address)
                        <p class="text-muted mb-0">{{ $invoice->company->address }}</p>
                        @endif
                        @if($invoice->company->phone)
                        <p class="text-muted mb-0">Phone: {{ $invoice->company->phone }}</p>
                        @endif
                        @if($invoice->company->email)
                        <p class="text-muted mb-0">Email: {{ $invoice->company->email }}</p>
                        @endif
                        @if($invoice->company->gst_number)
                        <p class="text-muted mb-0">GST: {{ $invoice->company->gst_number }}</p>
                        @endif
                        @endif
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h2 class="text-primary mb-1" style="color: #405189 !important;">TAX INVOICE</h2>
                        <p class="mb-0"><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
                        <p class="mb-0"><strong>Date:</strong> {{ formatDate($invoice->date) }}</p>
                        @if($invoice->due_date)
                        <p class="mb-0"><strong>Due Date:</strong> {{ formatDate($invoice->due_date) }}</p>
                        @endif
                        <p class="mb-0"><span class="badge bg-{{ $invoice->status_color }}">{{ ucfirst($invoice->status) }}</span></p>
                    </div>
                </div>
                <hr>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">BILL TO:</h6>
                        @if($invoice->customer)
                        <h5 class="mb-1">{{ $invoice->customer->name }}</h5>
                        @if($invoice->customer->company_name)
                        <p class="mb-0 fw-semibold">{{ $invoice->customer->company_name }}</p>
                        @endif
                        @if($invoice->customer->address)
                        <p class="mb-0">{{ $invoice->customer->address }}</p>
                        @endif
                        @if($invoice->customer->mobile)
                        <p class="mb-0">Phone: {{ $invoice->customer->mobile }}</p>
                        @endif
                        @if($invoice->customer->gst_number)
                        <p class="mb-0">GST: {{ $invoice->customer->gst_number }}</p>
                        @endif
                        @if($invoice->customer->company_trn)
                        <p class="mb-0">TRN: {{ $invoice->customer->company_trn }}</p>
                        @endif
                        @endif
                    </div>
                    <div class="col-md-6 text-md-end">
                        @if($invoice->trip)
                        <h6 class="text-muted mb-2">TRIP:</h6>
                        <p class="mb-0"><a href="{{ route('admin.trips.show', $invoice->trip) }}">{{ $invoice->trip->trip_number }} - {{ $invoice->trip->name }}</a></p>
                        @endif
                        @if($invoice->quotation)
                        <h6 class="text-muted mb-2 mt-2">FROM QUOTATION:</h6>
                        <p class="mb-0"><a href="{{ route('admin.quotations.show', $invoice->quotation) }}">{{ $invoice->quotation->quotation_number }}</a></p>
                        @endif
                    </div>
                </div>
                @if($invoice->invoice_type == 'pdf')
                <div class="card mb-4" style="border-left: 3px solid #405189;">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>PDF Invoice Uploaded</h6>
                            <p class="text-muted mb-0 small">{{ $invoice->pdf_description ?? 'No description provided.' }}</p>
                        </div>
                        @if($invoice->invoice_pdf)
                        <a href="{{ Storage::url($invoice->invoice_pdf) }}" target="_blank" class="btn btn-outline-primary">
                            <i class="bi bi-eye me-1"></i> View Uploaded PDF
                        </a>
                        @endif
                    </div>
                </div>
                @elseif($invoice->items && count($invoice->items) > 0)
                @php
                    $hasDimensions = collect($invoice->items)->contains(function ($item) {
                        return strtolower($item['unit'] ?? '') === 'sqft' || ($item['height'] ?? '') !== '' || ($item['width'] ?? '') !== '' || ($item['total'] ?? '') !== '';
                    });

                    $formatDimension = function ($value) {
                        if ($value === null || $value === '') {
                            return '-';
                        }

                        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
                    };

                    // Per-line tax rollup (GST/VAT summed across line items).
                    $lineTaxByType = ['gst' => 0, 'vat' => 0];
                    $hasLineTax = false;
                    foreach ($invoice->items as $taxItem) {
                        $tType = $taxItem['tax_type'] ?? 'none';
                        $tRate = (float) ($taxItem['tax_rate'] ?? 0);
                        if ($tType === 'none' || $tRate <= 0) {
                            continue;
                        }
                        $tAmount = $taxItem['amount'] ?? null;
                        if ($tAmount === null || $tAmount === '') {
                            $tAmount = strtolower($taxItem['unit'] ?? '') === 'sqft'
                                ? ($taxItem['total'] ?? 0) * ($taxItem['qty'] ?? 0) * ($taxItem['rate'] ?? 0)
                                : ($taxItem['qty'] ?? 0) * ($taxItem['rate'] ?? 0);
                        }
                        $tAmount = (float) $tAmount;
                        $hasLineTax = true;
                        if ($tType === 'gst') {
                            $lineTaxByType['gst'] += $invoice->gst_inclusive
                                ? ($tAmount * $tRate) / (100 + $tRate)
                                : ($tAmount * $tRate) / 100;
                        } else {
                            $lineTaxByType['vat'] += ($tAmount * $tRate) / 100;
                        }
                    }
                @endphp
                <table class="table mb-4" style="border-collapse: collapse;">
                    <thead style="background-color: #405189; color: #fff;">
                        <tr>
                            <th width="40" style="border: 1px solid #405189; padding: 10px;">#</th>
                            <th style="border: 1px solid #405189; padding: 10px;">DESCRIPTION</th>
                            <th width="80" style="border: 1px solid #405189; padding: 10px;">HSN</th>
                            <th width="60" class="text-center" style="border: 1px solid #405189; padding: 10px;">UNIT</th>
                            @if($hasDimensions)
                            <th width="70" class="text-end" style="border: 1px solid #405189; padding: 10px;">HEIGHT</th>
                            <th width="70" class="text-end" style="border: 1px solid #405189; padding: 10px;">WIDTH</th>
                            <th width="70" class="text-end" style="border: 1px solid #405189; padding: 10px;">SQFT</th>
                            @endif
                            <th width="60" class="text-end" style="border: 1px solid #405189; padding: 10px;">QTY</th>
                            <th width="100" class="text-end" style="border: 1px solid #405189; padding: 10px;">RATE ({{ currencySymbol($invoice) }})</th>
                            <th width="120" class="text-end" style="border: 1px solid #405189; padding: 10px;">AMOUNT ({{ currencySymbol($invoice) }})</th>
                            @if($hasLineTax)
                            <th width="100" class="text-end" style="border: 1px solid #405189; padding: 10px;">TAX</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $index => $item)
                        @php
                            $itemAmount = $item['amount'] ?? null;
                            if ($itemAmount === null || $itemAmount === '') {
                                $itemAmount = strtolower($item['unit'] ?? '') === 'sqft'
                                    ? ($item['total'] ?? 0) * ($item['qty'] ?? 0) * ($item['rate'] ?? 0)
                                    : ($item['qty'] ?? 0) * ($item['rate'] ?? 0);
                            }
                        @endphp
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 10px;">{{ $index + 1 }}</td>
                            <td style="border: 1px solid #ddd; padding: 10px;">{{ $item['description'] ?? '-' }}@if(!empty($item['passenger_type']))<br><span style="font-size: 11px; color: #888;">{{ $item['passenger_type'] }}</span>@endif</td>
                            <td style="border: 1px solid #ddd; padding: 10px;">{{ $item['hsn'] ?? '-' }}</td>
                            <td class="text-center" style="border: 1px solid #ddd; padding: 10px;">{{ strtoupper($item['unit'] ?? '-') }}</td>
                            @if($hasDimensions)
                            <td class="text-end" style="border: 1px solid #ddd; padding: 10px;">{{ $formatDimension($item['height'] ?? null) }}</td>
                            <td class="text-end" style="border: 1px solid #ddd; padding: 10px;">{{ $formatDimension($item['width'] ?? null) }}</td>
                            <td class="text-end" style="border: 1px solid #ddd; padding: 10px;">{{ $formatDimension($item['total'] ?? null) }}</td>
                            @endif
                            <td class="text-end" style="border: 1px solid #ddd; padding: 10px;">{{ number_format($item['qty'] ?? 0, 0) }}</td>
                            <td class="text-end" style="border: 1px solid #ddd; padding: 10px;">{{ number_format($item['rate'] ?? 0, 0) }}</td>
                            <td class="text-end" style="border: 1px solid #ddd; padding: 10px;">{{ number_format($itemAmount, 0) }}</td>
                            @if($hasLineTax)
                            @php $rowTaxType = $item['tax_type'] ?? 'none'; $rowTaxRate = (float) ($item['tax_rate'] ?? 0); @endphp
                            <td class="text-end" style="border: 1px solid #ddd; padding: 10px;">@if($rowTaxType !== 'none' && $rowTaxRate > 0){{ strtoupper($rowTaxType) }} {{ rtrim(rtrim(number_format($rowTaxRate, 2), '0'), '.') }}%@else-@endif</td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="alert alert-warning mb-4">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    No items or PDF attached to this invoice.
                </div>
                @endif
                <div class="row">
                    <div class="col-md-6">
                        @if($invoice->notes)
                        <h6>Notes:</h6>
                        <p class="text-muted small mb-0" style="white-space: pre-line;">{{ $invoice->notes }}</p>
                        @endif
                        @if($invoice->terms)
                        <h6 class="mt-3">Terms & Conditions:</h6>
                        <p class="text-muted small mb-0" style="white-space: pre-line;">{{ $invoice->terms }}</p>
                        @endif
                        @if($invoice->payment_terms)
                        <h6 class="mt-3">Payment Terms:</h6>
                        <p class="text-muted small mb-0" style="white-space: pre-line;">{{ $invoice->payment_terms }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td>Subtotal:</td>
                                <td class="text-end">{{ formatMoney($invoice->subtotal, 0, $invoice) }}</td>
                            </tr>
                            @if($invoice->discount > 0)
                            <tr>
                                <td>Discount:</td>
                                <td class="text-end text-danger">- {{ formatMoney($invoice->discount, 0, $invoice) }}</td>
                            </tr>
                            @endif
                            @php $hasLineTax = $hasLineTax ?? false; $lineTaxByType = $lineTaxByType ?? ['gst' => 0, 'vat' => 0]; @endphp
                            @if($hasLineTax)
                                @if($lineTaxByType['gst'] > 0)
                                    @if($invoice->gst_split)
                                    <tr>
                                        <td>CGST{{ $invoice->gst_inclusive ? ' - Inclusive' : '' }}:</td>
                                        <td class="text-end {{ $invoice->gst_inclusive ? '' : 'text-success' }}">{{ $invoice->gst_inclusive ? '' : '+ ' }}{{ formatMoney($lineTaxByType['gst'] / 2, 0, $invoice) }}</td>
                                    </tr>
                                    <tr>
                                        <td>SGST{{ $invoice->gst_inclusive ? ' - Inclusive' : '' }}:</td>
                                        <td class="text-end {{ $invoice->gst_inclusive ? '' : 'text-success' }}">{{ $invoice->gst_inclusive ? '' : '+ ' }}{{ formatMoney($lineTaxByType['gst'] / 2, 0, $invoice) }}</td>
                                    </tr>
                                    @else
                                    <tr>
                                        <td>GST{{ $invoice->gst_inclusive ? ' - Inclusive' : '' }}:</td>
                                        <td class="text-end {{ $invoice->gst_inclusive ? '' : 'text-success' }}">{{ $invoice->gst_inclusive ? '' : '+ ' }}{{ formatMoney($lineTaxByType['gst'], 0, $invoice) }}</td>
                                    </tr>
                                    @endif
                                @endif
                                @if($lineTaxByType['vat'] > 0)
                                <tr>
                                    <td>VAT:</td>
                                    <td class="text-end text-success">+ {{ formatMoney($lineTaxByType['vat'], 0, $invoice) }}</td>
                                </tr>
                                @endif
                            @elseif($invoice->gst > 0)
                                @if($invoice->gst_split)
                                <tr>
                                    <td>CGST ({{ $invoice->gst_percent / 2 }}%){{ $invoice->gst_inclusive ? ' - Inclusive' : '' }}:</td>
                                    <td class="text-end {{ $invoice->gst_inclusive ? '' : 'text-success' }}">{{ $invoice->gst_inclusive ? '' : '+ ' }}{{ formatMoney($invoice->gst / 2, 0, $invoice) }}</td>
                                </tr>
                                <tr>
                                    <td>SGST ({{ $invoice->gst_percent / 2 }}%){{ $invoice->gst_inclusive ? ' - Inclusive' : '' }}:</td>
                                    <td class="text-end {{ $invoice->gst_inclusive ? '' : 'text-success' }}">{{ $invoice->gst_inclusive ? '' : '+ ' }}{{ formatMoney($invoice->gst / 2, 0, $invoice) }}</td>
                                </tr>
                                @else
                                <tr>
                                    <td>GST ({{ $invoice->gst_percent }}%){{ $invoice->gst_inclusive ? ' - Inclusive' : '' }}:</td>
                                    <td class="text-end {{ $invoice->gst_inclusive ? '' : 'text-success' }}">{{ $invoice->gst_inclusive ? '' : '+ ' }}{{ formatMoney($invoice->gst, 0, $invoice) }}</td>
                                </tr>
                                @endif
                            @endif
                            <tr>
                                <td><strong>Grand Total:</strong></td>
                                <td class="text-end"><strong>{{ formatMoney($invoice->grand_total, 0, $invoice) }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        @if($invoice->status !== 'paid')
                        @can('invoices.edit')
                        <form action="{{ route('admin.invoices.update', $invoice) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="date" value="{{ $invoice->date->format('Y-m-d') }}">
                            <input type="hidden" name="customer_id" value="{{ $invoice->customer_id }}">
                            <input type="hidden" name="invoice_type" value="{{ $invoice->invoice_type }}">
                            <input type="hidden" name="grand_total" value="{{ $invoice->grand_total }}">
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label mb-0">Update Status:</label>
                                <select name="status" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                    <option value="sent" {{ $invoice->status == 'sent' ? 'selected' : '' }}>Sent</option>
                                    <option value="partial" {{ $invoice->status == 'partial' ? 'selected' : '' }}>Partial</option>
                                    <option value="paid" {{ $invoice->status == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="overdue" {{ $invoice->status == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                    <option value="cancelled" {{ $invoice->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                        </form>
                        @endcan
                        @else
                        <span class="badge bg-success fs-6"><i class="bi bi-check-circle me-1"></i> Fully Paid</span>
                        @endif
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="mb-0"><strong>For {{ $invoice->company->name ?? '-' }}</strong></p>
                        <p class="text-muted small">Authorized Signatory</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="main-card mb-3 card">
            <div class="card-header" style="background-color: #405189; color: #fff;">
                <i class="bi bi-cash-stack me-2"></i> Payment Summary
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Invoice Total:</span>
                    <strong>{{ formatMoney($invoice->grand_total, 0, $invoice) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-success">Amount Paid:</span>
                    <strong class="text-success">{{ formatMoney($invoice->amount_paid, 0, $invoice) }}</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="{{ $invoice->balance_due > 0 ? 'text-danger' : 'text-success' }}">Balance Due:</span>
                    <strong class="{{ $invoice->balance_due > 0 ? 'text-danger' : 'text-success' }}">{{ formatMoney($invoice->balance_due, 0, $invoice) }}</strong>
                </div>
            </div>
        </div>
        @if($invoice->incomes && $invoice->incomes->count() > 0)
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-clock-history me-2"></i> Payment History
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($invoice->incomes as $income)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>{{ formatMoney($income->amount, 0, $invoice) }}</strong>
                                <br><small class="text-muted">{{ formatDate($income->income_date) }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success">{{ ucfirst(str_replace('_', ' ', $income->payment_mode)) }}</span>
                                <br><small class="text-muted">{{ $income->receipt_number }}</small>
                                <br><a href="{{ route('admin.income.receipt', $income) }}" class="btn btn-outline-secondary btn-sm mt-1" target="_blank"><i class="bi bi-download me-1"></i> Receipt</a>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
            @if($invoice->status != 'paid')
            @can('income.create')
            <div class="card-footer">
                <a href="{{ route('admin.income.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-success btn-sm w-100">
                    <i class="bi bi-plus-lg me-1"></i> Add Payment
                </a>
            </div>
            @endcan
            @endif
        </div>
        @elseif($invoice->status != 'paid')
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-clock-history me-2"></i> Payment History
            </div>
            <div class="card-body text-center text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                No payments recorded yet
            </div>
            @can('income.create')
            <div class="card-footer">
                <a href="{{ route('admin.income.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-success btn-sm w-100">
                    <i class="bi bi-plus-lg me-1"></i> Add Payment
                </a>
            </div>
            @endcan
        </div>
        @endif
        @if($invoice->status != 'paid')
        @can('invoices.edit')
        <form action="{{ route('admin.invoices.mark-paid', $invoice) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Mark this invoice as fully paid?')">
                <i class="bi bi-check-circle me-1"></i> Mark as Fully Paid
            </button>
        </form>
        @endcan
        @endif
    </div>
</div>
@endsection
