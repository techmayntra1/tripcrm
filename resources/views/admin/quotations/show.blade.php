@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.app')
@section('title', 'View Quotation')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-file-earmark-text-fill icon-gradient bg-premium-dark"></i>
            </div>
            <div>
                Quotation #{{ $quotation->quotation_number }}

            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.quotations.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            @can('quotations.edit')
            @if($quotation->status !== 'accepted')
            <a href="{{ route('admin.quotations.edit', $quotation) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            @endif
            @endcan
            @if($quotation->hasInvoice())
            <a href="{{ route('admin.invoices.show', $quotation->invoices->first()) }}" class="btn btn-success">
                <i class="bi bi-receipt me-1"></i> View Invoice
            </a>
            @else
            @can('invoices.create')
            <a href="{{ route('admin.invoices.create') }}?quotation_id={{ $quotation->id }}" class="btn btn-success">
                <i class="bi bi-receipt me-1"></i> Convert to Invoice
            </a>
            @endcan
            @endif
            <a href="{{ route('admin.quotations.download', $quotation) }}" class="btn btn-secondary">
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
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div class="main-card mb-3 card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h4 class="mb-1">{{ $quotation->company->name ?? '-' }}</h4>
                @if($quotation->company)
                <p class="text-muted mb-0">{{ $quotation->company->address ?? '' }}</p>
                <p class="text-muted mb-0">Phone: {{ $quotation->company->phone ?? '-' }}</p>
                @if($quotation->company->gst_number)
                <p class="text-muted mb-0">GST: {{ $quotation->company->gst_number }}</p>
                @endif
                @endif
            </div>
            <div class="col-md-6 text-md-end">
                <h2 class="text-primary mb-1">QUOTATION</h2>
                <p class="mb-0"><strong>Quotation #:</strong> {{ $quotation->quotation_number }}</p>
                <p class="mb-0"><strong>Date:</strong> {{ formatDate($quotation->date) }}</p>
                <p class="mb-0">
                    <span class="badge bg-{{ $quotation->status_color }}">{{ ucfirst($quotation->status) }}</span>
                    @if($quotation->hasInvoice())
                    <a href="{{ route('admin.invoices.show', $quotation->invoices->first()) }}" class="badge bg-success text-decoration-none">
                        <i class="bi bi-receipt-cutoff me-1"></i>Invoice Created
                    </a>
                    @endif
                </p>
            </div>
        </div>
        <hr>
        <div class="row mb-4">
            <div class="col-md-6">
                <h6 class="text-muted mb-2">BILL TO:</h6>
                <h5 class="mb-1">{{ $quotation->customer->name ?? '-' }}</h5>
                @if($quotation->customer)
                @if($quotation->customer->address)
                <p class="mb-0">{{ $quotation->customer->address }}</p>
                @endif
                <p class="mb-0">Phone: {{ $quotation->customer->mobile ?? '-' }}</p>
                @if($quotation->customer->gst_number)
                <p class="mb-0">GST: {{ $quotation->customer->gst_number }}</p>
                @endif
                @endif
            </div>
            <div class="col-md-6">
                @if($quotation->subject)
                <h6 class="text-muted mb-2">SUBJECT:</h6>
                <p>{{ $quotation->subject }}</p>
                @endif
            </div>
        </div>
        @if($quotation->quotation_type == 'items' && $quotation->items && count($quotation->items) > 0)
        @php
            $hasDimensions = collect($quotation->items)->contains(function ($item) {
                return strtolower($item['unit'] ?? '') === 'sqft' || ($item['height'] ?? '') !== '' || ($item['width'] ?? '') !== '' || ($item['total'] ?? '') !== '';
            });

            $formatDimension = function ($value) {
                if ($value === null || $value === '') {
                    return '-';
                }

                return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
            };
        @endphp
        <table class="table mb-4" style="border-collapse: collapse;">
            <thead style="background-color: #0EA5A4; color: #fff;">
                <tr>
                    <th width="40" style="border: 1px solid #0EA5A4; padding: 10px;">#</th>
                    <th style="border: 1px solid #0EA5A4; padding: 10px;">DESCRIPTION</th>
                    <th width="80" class="text-center" style="border: 1px solid #0EA5A4; padding: 10px;">UNIT</th>
                    @if($hasDimensions)
                    <th width="80" class="text-end" style="border: 1px solid #0EA5A4; padding: 10px;">HEIGHT</th>
                    <th width="80" class="text-end" style="border: 1px solid #0EA5A4; padding: 10px;">WIDTH</th>
                    <th width="80" class="text-end" style="border: 1px solid #0EA5A4; padding: 10px;">SQFT</th>
                    @endif
                    <th width="100" class="text-end" style="border: 1px solid #0EA5A4; padding: 10px;">QTY</th>
                    <th width="120" class="text-end" style="border: 1px solid #0EA5A4; padding: 10px;">RATE (₹)</th>
                    <th width="130" class="text-end" style="border: 1px solid #0EA5A4; padding: 10px;">AMOUNT (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $index => $item)
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
                    <td style="border: 1px solid #ddd; padding: 10px;">{{ $item['description'] ?? '-' }}</td>
                    <td class="text-center" style="border: 1px solid #ddd; padding: 10px;">{{ strtoupper($item['unit'] ?? '-') }}</td>
                    @if($hasDimensions)
                    <td class="text-end" style="border: 1px solid #ddd; padding: 10px;">{{ $formatDimension($item['height'] ?? null) }}</td>
                    <td class="text-end" style="border: 1px solid #ddd; padding: 10px;">{{ $formatDimension($item['width'] ?? null) }}</td>
                    <td class="text-end" style="border: 1px solid #ddd; padding: 10px;">{{ $formatDimension($item['total'] ?? null) }}</td>
                    @endif
                    <td class="text-end" style="border: 1px solid #ddd; padding: 10px;">{{ number_format($item['qty'] ?? 0, 0) }}</td>
                    <td class="text-end" style="border: 1px solid #ddd; padding: 10px;">{{ number_format($item['rate'] ?? 0, 0) }}</td>
                    <td class="text-end" style="border: 1px solid #ddd; padding: 10px;">{{ number_format($itemAmount, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @elseif($quotation->quotation_type == 'pdf')
        <div class="card mb-4" style="border-left: 3px solid #0EA5A4;">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-1"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>PDF Quotation Uploaded</h6>
                    <p class="text-muted mb-0 small">{{ $quotation->pdf_description ?? 'No description provided.' }}</p>
                </div>
                @if($quotation->quotation_pdf)
                <a href="{{ Storage::url($quotation->quotation_pdf) }}" target="_blank" class="btn btn-outline-primary">
                    <i class="bi bi-eye me-1"></i> View Uploaded PDF
                </a>
                @endif
            </div>
        </div>
        @else
        <div class="alert alert-warning mb-4">
            <i class="bi bi-exclamation-triangle me-2"></i>
            No items or PDF attached to this quotation.
        </div>
        @endif
        <div class="row">
            <div class="col-md-6">
                @if($quotation->terms)
                <h6>Terms & Conditions:</h6>
                <p class="text-muted small" style="white-space: pre-line;">{{ $quotation->terms }}</p>
                @endif
            </div>
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr>
                        <td>Subtotal:</td>
                        <td class="text-end">{{ formatMoney($quotation->subtotal) }}</td>
                    </tr>
                    @if($quotation->discount > 0)
                    <tr>
                        <td>Discount:</td>
                        <td class="text-end text-danger">- {{ formatMoney($quotation->discount) }}</td>
                    </tr>
                    @endif
                    @if($quotation->gst_percent > 0)
                        @if($quotation->gst_split)
                        <tr>
                            <td>CGST ({{ $quotation->gst_percent / 2 }}%){{ $quotation->gst_inclusive ? ' - Inclusive' : '' }}:</td>
                            <td class="text-end {{ $quotation->gst_inclusive ? '' : 'text-success' }}">{{ $quotation->gst_inclusive ? '' : '+ ' }}{{ formatMoney($quotation->gst / 2) }}</td>
                        </tr>
                        <tr>
                            <td>SGST ({{ $quotation->gst_percent / 2 }}%){{ $quotation->gst_inclusive ? ' - Inclusive' : '' }}:</td>
                            <td class="text-end {{ $quotation->gst_inclusive ? '' : 'text-success' }}">{{ $quotation->gst_inclusive ? '' : '+ ' }}{{ formatMoney($quotation->gst / 2) }}</td>
                        </tr>
                        @else
                        <tr>
                            <td>GST ({{ $quotation->gst_percent }}%){{ $quotation->gst_inclusive ? ' - Inclusive' : '' }}:</td>
                            <td class="text-end {{ $quotation->gst_inclusive ? '' : 'text-success' }}">{{ $quotation->gst_inclusive ? '' : '+ ' }}{{ formatMoney($quotation->gst) }}</td>
                        </tr>
                        @endif
                    @endif
                    <tr>
                        <td><strong>Grand Total:</strong></td>
                        <td class="text-end"><strong>{{ formatMoney($quotation->grand_total) }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6">
                @can('quotations.edit')
                <form action="{{ route('admin.quotations.status', $quotation) }}" method="POST" class="d-inline">
                    @csrf
                    <div class="d-flex align-items-center gap-2">
                        <label class="form-label mb-0">Update Status:</label>
                        <select name="status" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                            <option value="sent" {{ $quotation->status == 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="accepted" {{ $quotation->status == 'accepted' ? 'selected' : '' }}>Accepted</option>
                            <option value="rejected" {{ $quotation->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="expired" {{ $quotation->status == 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                </form>
                @endcan
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0"><strong>For {{ $quotation->company->name ?? '-' }}</strong></p>
                <p class="text-muted small">Authorized Signatory</p>
            </div>
        </div>
    </div>
</div>
@endsection
