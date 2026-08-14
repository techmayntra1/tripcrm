<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            size: A4;
            margin: 25mm 20mm;
        }
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        
        .header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #405189;
        }
        .company-section {
            float: left;
            width: 50%;
        }
        .invoice-section {
            float: right;
            width: 50%;
            text-align: right;
        }
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .company-info {
            font-size: 12px;
            color: #666;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #405189;
            margin-bottom: 8px;
        }
        .invoice-details {
            font-size: 12px;
            color: #333;
        }
        .status-badge {
            display: inline-block;
            margin-top: 10px;
            padding: 5px 15px;
            background-color: #405189;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            border-radius: 4px;
        }

        
        .info-row {
            margin-bottom: 25px;
        }
        .bill-to-section {
            float: left;
            width: 50%;
        }
        .trip-section {
            float: right;
            width: 45%;
        }
        .section-label {
            font-size: 11px;
            font-weight: bold;
            color: #888;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .customer-name {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 250px;
        }
        .customer-info {
            font-size: 12px;
            color: #555;
            line-height: 1.6;
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 250px;
        }

        
        .summary-row {
            margin-top: 20px;
        }
        .notes-section {
            float: left;
            width: 45%;
        }
        .totals-section {
            float: right;
            width: 50%;
        }
        .notes-title {
            font-size: 13px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }
        .notes-text {
            font-size: 11px;
            color: #666;
            line-height: 1.6;
        }

        
        .pdf-info {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-left: 3px solid #405189;
            padding: 15px;
            margin-bottom: 25px;
        }
        .pdf-info-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .pdf-info-text {
            color: #666;
            font-size: 11px;
        }

        
        .footer {
            margin-top: 60px;
            text-align: right;
        }
        .signature-box {
            display: inline-block;
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 10px;
            min-width: 180px;
        }
        .signature-company {
            font-size: 12px;
            font-weight: bold;
            color: #405189;
        }
        .signature-text {
            font-size: 11px;
            color: #888;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    
    <div class="header clearfix">
        <div class="company-section">
            <div class="company-name">{{ $invoice->company->name ?? '' }}</div>
            <div class="company-info">
                @if($invoice->company)
                    @if($invoice->company->address){{ $invoice->company->address }}<br>@endif
                    @if($invoice->company->phone)Phone: {{ $invoice->company->phone }}<br>@endif
                    @if($invoice->company->email)Email: {{ $invoice->company->email }}<br>@endif
                    @if($invoice->company->gst_number)GST: {{ $invoice->company->gst_number }}@endif
                @endif
            </div>
        </div>
        <div class="invoice-section">
            <div class="invoice-title">TAX INVOICE</div>
            <div class="invoice-details">
                <strong>Invoice #:</strong> {{ $invoice->invoice_number }}<br>
                <strong>Date:</strong> {{ \Carbon\Carbon::parse($invoice->date)->format('d-m-Y') }}
                @if($invoice->due_date)
                <br><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') }}
                @endif
            </div>
            <span class="status-badge">{{ strtoupper($invoice->status) }}</span>
        </div>
    </div>

    
    <div class="info-row clearfix">
        <div class="bill-to-section">
            <div class="section-label">BILL TO:</div>
            <div class="customer-name">{{ $invoice->customer->name ?? '-' }}</div>
            <div class="customer-info">
                @if($invoice->customer)
                    @if($invoice->customer->address){{ $invoice->customer->address }}<br>@endif
                    @if($invoice->customer->mobile)Phone: {{ $invoice->customer->mobile }}<br>@endif
                    @if($invoice->customer->gst_number)GST: {{ $invoice->customer->gst_number }}@endif
                @endif
            </div>
        </div>
        @if($invoice->trip)
        <div class="trip-section">
            <div class="section-label">TRIP:</div>
            <div style="font-size: 14px; font-weight: bold; color: #333;">{{ $invoice->trip->trip_number }}</div>
            <div style="font-size: 12px; color: #555;">{{ $invoice->trip->name }}</div>
        </div>
        @endif
    </div>

    
    @if($invoice->invoice_type == 'items' && $invoice->items && count($invoice->items) > 0)
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
    @endphp
    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; margin-bottom: 25px; border-color: #ccc;">
        <thead>
            <tr style="background-color: #405189; color: #fff;">
                <th style="width: {{ $hasDimensions ? '4%' : '5%' }}; text-align: center; padding: 10px; font-size: 11px; font-weight: bold; border: 1px solid #ccc;">#</th>
                <th style="width: {{ $hasDimensions ? '27%' : '35%' }}; text-align: left; padding: 10px; font-size: 11px; font-weight: bold; border: 1px solid #ccc;">DESCRIPTION</th>
                <th style="width: {{ $hasDimensions ? '8%' : '10%' }}; text-align: center; padding: 10px; font-size: 11px; font-weight: bold; border: 1px solid #ccc;">HSN</th>
                <th style="width: {{ $hasDimensions ? '8%' : '10%' }}; text-align: center; padding: 10px; font-size: 11px; font-weight: bold; border: 1px solid #ccc;">UNIT</th>
                @if($hasDimensions)
                <th style="width: 7%; text-align: right; padding: 10px; font-size: 11px; font-weight: bold; border: 1px solid #ccc;">HEIGHT</th>
                <th style="width: 7%; text-align: right; padding: 10px; font-size: 11px; font-weight: bold; border: 1px solid #ccc;">WIDTH</th>
                <th style="width: 7%; text-align: right; padding: 10px; font-size: 11px; font-weight: bold; border: 1px solid #ccc;">SQFT</th>
                @endif
                <th style="width: {{ $hasDimensions ? '7%' : '10%' }}; text-align: right; padding: 10px; font-size: 11px; font-weight: bold; border: 1px solid #ccc;">QTY</th>
                <th style="width: {{ $hasDimensions ? '11%' : '15%' }}; text-align: right; padding: 10px; font-size: 11px; font-weight: bold; border: 1px solid #ccc;">RATE (₹)</th>
                <th style="width: {{ $hasDimensions ? '14%' : '15%' }}; text-align: right; padding: 10px; font-size: 11px; font-weight: bold; border: 1px solid #ccc;">AMOUNT (₹)</th>
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
                <td style="text-align: center; padding: 10px; font-size: 12px; font-weight: bold; border: 1px solid #ccc;">{{ $index + 1 }}</td>
                <td style="text-align: left; padding: 10px; font-size: 12px; border: 1px solid #ccc;">{{ $item['description'] ?? '-' }}</td>
                <td style="text-align: center; padding: 10px; font-size: 12px; border: 1px solid #ccc;">{{ $item['hsn'] ?? '-' }}</td>
                <td style="text-align: center; padding: 10px; font-size: 12px; border: 1px solid #ccc;">{{ strtoupper($item['unit'] ?? '-') }}</td>
                @if($hasDimensions)
                <td style="text-align: right; padding: 10px; font-size: 12px; border: 1px solid #ccc;">{{ $formatDimension($item['height'] ?? null) }}</td>
                <td style="text-align: right; padding: 10px; font-size: 12px; border: 1px solid #ccc;">{{ $formatDimension($item['width'] ?? null) }}</td>
                <td style="text-align: right; padding: 10px; font-size: 12px; border: 1px solid #ccc;">{{ $formatDimension($item['total'] ?? null) }}</td>
                @endif
                <td style="text-align: right; padding: 10px; font-size: 12px; border: 1px solid #ccc;">{{ number_format($item['qty'] ?? 0, 0) }}</td>
                <td style="text-align: right; padding: 10px; font-size: 12px; border: 1px solid #ccc;">{{ number_format($item['rate'] ?? 0, 0) }}</td>
                <td style="text-align: right; padding: 10px; font-size: 12px; border: 1px solid #ccc;">{{ number_format($itemAmount, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @elseif($invoice->invoice_type == 'pdf')
    <div class="pdf-info">
        <div class="pdf-info-title">PDF Invoice Uploaded</div>
        @if($invoice->pdf_description)
        <div class="pdf-info-text">{{ $invoice->pdf_description }}</div>
        @endif
    </div>
    @endif

    
    <div class="summary-row clearfix">
        <div class="notes-section">
            @if($invoice->notes)
            <div class="notes-title">Notes:</div>
            <div class="notes-text">{{ $invoice->notes }}</div>
            @endif
        </div>
        <div class="totals-section">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 10px 8px; font-size: 13px; border-bottom: 1px solid #eee; text-align: right; color: #666; width: 60%;">Subtotal:</td>
                    <td style="padding: 10px 8px; font-size: 13px; border-bottom: 1px solid #eee; text-align: right; font-weight: bold; color: #333;">₹ {{ number_format($invoice->subtotal, 0) }}</td>
                </tr>
                @if($invoice->discount > 0)
                <tr>
                    <td style="padding: 10px 8px; font-size: 13px; border-bottom: 1px solid #eee; text-align: right; color: #666; width: 60%;">Discount:</td>
                    <td style="padding: 10px 8px; font-size: 13px; border-bottom: 1px solid #eee; text-align: right; font-weight: bold; color: #e74c3c;">- ₹ {{ number_format($invoice->discount, 0) }}</td>
                </tr>
                @endif
                @if($invoice->gst_percent > 0)
                    @if($invoice->gst_split)
                    <tr>
                        <td style="padding: 10px 8px; font-size: 13px; border-bottom: 1px solid #eee; text-align: right; color: #666; width: 60%;">CGST ({{ $invoice->gst_percent / 2 }}%){{ $invoice->gst_inclusive ? ' - Inclusive' : '' }}:</td>
                        <td style="padding: 10px 8px; font-size: 13px; border-bottom: 1px solid #eee; text-align: right; font-weight: bold; color: {{ $invoice->gst_inclusive ? '#333' : '#27ae60' }};">{{ $invoice->gst_inclusive ? '' : '+ ' }}₹ {{ number_format($invoice->gst / 2, 0) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 8px; font-size: 13px; border-bottom: 1px solid #eee; text-align: right; color: #666; width: 60%;">SGST ({{ $invoice->gst_percent / 2 }}%){{ $invoice->gst_inclusive ? ' - Inclusive' : '' }}:</td>
                        <td style="padding: 10px 8px; font-size: 13px; border-bottom: 1px solid #eee; text-align: right; font-weight: bold; color: {{ $invoice->gst_inclusive ? '#333' : '#27ae60' }};">{{ $invoice->gst_inclusive ? '' : '+ ' }}₹ {{ number_format($invoice->gst / 2, 0) }}</td>
                    </tr>
                    @else
                    <tr>
                        <td style="padding: 10px 8px; font-size: 13px; border-bottom: 1px solid #eee; text-align: right; color: #666; width: 60%;">GST ({{ $invoice->gst_percent }}%){{ $invoice->gst_inclusive ? ' - Inclusive' : '' }}:</td>
                        <td style="padding: 10px 8px; font-size: 13px; border-bottom: 1px solid #eee; text-align: right; font-weight: bold; color: {{ $invoice->gst_inclusive ? '#333' : '#27ae60' }};">{{ $invoice->gst_inclusive ? '' : '+ ' }}₹ {{ number_format($invoice->gst, 0) }}</td>
                    </tr>
                    @endif
                @endif
                <tr style="background-color: #f0f4f8;">
                    <td style="padding: 12px 8px; font-size: 14px; text-align: right; font-weight: bold; color: #333;">Grand Total:</td>
                    <td style="padding: 12px 8px; font-size: 16px; text-align: right; font-weight: bold; color: #333; white-space: nowrap;">₹{{ number_format($invoice->grand_total, 0) }}</td>
                </tr>
            </table>
        </div>
    </div>

    
    @if($invoice->amount_paid > 0 || $invoice->balance_due > 0)
    <div style="margin-top: 30px; padding: 15px; background-color: #f8f9fa; border: 1px solid #ddd;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 33%; text-align: center;">
                    <div style="font-size: 11px; color: #666;">Amount Paid</div>
                    <div style="font-size: 16px; font-weight: bold; color: #27ae60;">₹ {{ number_format($invoice->amount_paid, 0) }}</div>
                </td>
                <td style="width: 33%; text-align: center;">
                    <div style="font-size: 11px; color: #666;">Balance Due</div>
                    <div style="font-size: 16px; font-weight: bold; color: {{ $invoice->balance_due > 0 ? '#e74c3c' : '#27ae60' }};">₹ {{ number_format($invoice->balance_due, 0) }}</div>
                </td>
                <td style="width: 33%; text-align: center;">
                    <div style="font-size: 11px; color: #666;">Status</div>
                    <div style="font-size: 14px; font-weight: bold; color: #333;">{{ strtoupper($invoice->status) }}</div>
                </td>
            </tr>
        </table>
    </div>
    @endif

    
    <div class="footer">
        <div class="signature-box">
            <div class="signature-company">For {{ $invoice->company->name ?? '' }}</div>
            <div class="signature-text">Authorized Signatory</div>
        </div>
    </div>
</body>
</html>
