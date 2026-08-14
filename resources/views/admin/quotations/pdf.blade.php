<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Quotation {{ $quotation->quotation_number }}</title>
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
        .quotation-section {
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
        .quotation-title {
            font-size: 28px;
            font-weight: bold;
            color: #405189;
            margin-bottom: 8px;
        }
        .quotation-details {
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
        .subject-section {
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
        .subject-text {
            font-size: 12px;
            color: #333;
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 200px;
        }

        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .items-table th {
            background-color: #405189;
            color: #fff;
            padding: 10px 8px;
            font-size: 11px;
            font-weight: bold;
            text-align: left;
            border: 1px solid #405189;
        }
        .items-table th.center { text-align: center; }
        .items-table th.right { text-align: right; }
        .items-table td {
            padding: 12px 8px;
            font-size: 12px;
            border: 1px solid #ddd;
        }
        .items-table td.center { text-align: center; }
        .items-table td.right { text-align: right; }

        
        .summary-row {
            margin-top: 20px;
        }
        .terms-section {
            float: left;
            width: 45%;
        }
        .totals-section {
            float: right;
            width: 50%;
        }
        .terms-title {
            font-size: 13px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }
        .terms-text {
            font-size: 11px;
            color: #666;
            line-height: 1.6;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 10px 8px;
            font-size: 13px;
            border-bottom: 1px solid #eee;
        }
        .totals-table .label {
            text-align: right;
            color: #666;
            width: 60%;
        }
        .totals-table .value {
            text-align: right;
            font-weight: bold;
            color: #333;
        }
        .totals-table .discount .value {
            color: #e74c3c;
        }
        .totals-table .gst .value {
            color: #27ae60;
        }
        .totals-table .grand-total {
            background-color: #f0f4f8;
        }
        .totals-table .grand-total td {
            padding: 12px 8px;
            font-size: 14px;
            border-bottom: none;
        }
        .totals-table .grand-total .label {
            font-weight: bold;
            color: #333;
        }
        .totals-table .grand-total .value {
            font-size: 16px;
            color: #333;
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
    </style>
</head>
<body>
    
    <div class="header clearfix">
        <div class="company-section">
            <div class="company-name">{{ $quotation->company->name ?? '' }}</div>
            <div class="company-info">
                @if($quotation->company)
                    @if($quotation->company->phone)Phone: {{ $quotation->company->phone }}<br>@endif
                    @if($quotation->company->gst_number)GST: {{ $quotation->company->gst_number }}@endif
                @endif
            </div>
        </div>
        <div class="quotation-section">
            <div class="quotation-title">QUOTATION</div>
            <div class="quotation-details">
                <strong>Quotation #:</strong> {{ $quotation->quotation_number }}<br>
                <strong>Date:</strong> {{ \Carbon\Carbon::parse($quotation->date)->format('d-m-Y') }}
            </div>
            <span class="status-badge">{{ strtoupper($quotation->status) }}</span>
        </div>
    </div>

    
    <div class="info-row clearfix">
        <div class="bill-to-section">
            <div class="section-label">BILL TO:</div>
            <div class="customer-name">{{ $quotation->customer->name ?? '-' }}</div>
            <div class="customer-info">
                @if($quotation->customer)
                    @if($quotation->customer->address){{ $quotation->customer->address }}<br>@endif
                    @if($quotation->customer->mobile)Phone: {{ $quotation->customer->mobile }}@endif
                @endif
            </div>
        </div>
        @if($quotation->subject)
        <div class="subject-section">
            <div class="section-label">SUBJECT:</div>
            <div class="subject-text">{{ $quotation->subject }}</div>
        </div>
        @endif
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
    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; margin-bottom: 25px; border-color: #ccc;">
        <thead>
            <tr style="background-color: #405189; color: #fff;">
                <th style="width: {{ $hasDimensions ? '5%' : '6%' }}; text-align: center; padding: 10px; font-size: 11px; font-weight: bold; border: 1px solid #ccc;">#</th>
                <th style="width: {{ $hasDimensions ? '31%' : '44%' }}; text-align: left; padding: 10px; font-size: 11px; font-weight: bold; border: 1px solid #ccc;">DESCRIPTION</th>
                <th style="width: {{ $hasDimensions ? '8%' : '10%' }}; text-align: center; padding: 10px; font-size: 11px; font-weight: bold; border: 1px solid #ccc;">UNIT</th>
                @if($hasDimensions)
                <th style="width: 8%; text-align: right; padding: 10px; font-size: 11px; font-weight: bold; border: 1px solid #ccc;">HEIGHT</th>
                <th style="width: 8%; text-align: right; padding: 10px; font-size: 11px; font-weight: bold; border: 1px solid #ccc;">WIDTH</th>
                <th style="width: 8%; text-align: right; padding: 10px; font-size: 11px; font-weight: bold; border: 1px solid #ccc;">SQFT</th>
                @endif
                <th style="width: {{ $hasDimensions ? '8%' : '13%' }}; text-align: right; padding: 10px; font-size: 11px; font-weight: bold; border: 1px solid #ccc;">QTY</th>
                <th style="width: {{ $hasDimensions ? '12%' : '13%' }}; text-align: right; padding: 10px; font-size: 11px; font-weight: bold; border: 1px solid #ccc;">RATE (₹)</th>
                <th style="width: {{ $hasDimensions ? '12%' : '14%' }}; text-align: right; padding: 10px; font-size: 11px; font-weight: bold; border: 1px solid #ccc;">AMOUNT (₹)</th>
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
                <td style="text-align: center; padding: 10px; font-size: 12px; font-weight: bold; border: 1px solid #ccc;">{{ $index + 1 }}</td>
                <td style="text-align: left; padding: 10px; font-size: 12px; border: 1px solid #ccc;">{{ $item['description'] ?? '-' }}</td>
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
    @elseif($quotation->quotation_type == 'pdf')
    <div class="pdf-info">
        <div class="pdf-info-title">PDF Quotation Uploaded</div>
        @if($quotation->pdf_description)
        <div class="pdf-info-text">{{ $quotation->pdf_description }}</div>
        @endif
    </div>
    @endif

    
    <div class="summary-row clearfix">
        <div class="terms-section">
            @if($quotation->terms)
            <div class="terms-title">Terms & Conditions:</div>
            <div class="terms-text">{{ $quotation->terms }}</div>
            @endif
        </div>
        <div class="totals-section">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 10px 8px; font-size: 13px; border-bottom: 1px solid #eee; text-align: right; color: #666; width: 60%;">Subtotal:</td>
                    <td style="padding: 10px 8px; font-size: 13px; border-bottom: 1px solid #eee; text-align: right; font-weight: bold; color: #333;">₹ {{ number_format($quotation->subtotal, 0) }}</td>
                </tr>
                @if($quotation->discount > 0)
                <tr>
                    <td style="padding: 10px 8px; font-size: 13px; border-bottom: 1px solid #eee; text-align: right; color: #666; width: 60%;">Discount:</td>
                    <td style="padding: 10px 8px; font-size: 13px; border-bottom: 1px solid #eee; text-align: right; font-weight: bold; color: #e74c3c;">- ₹ {{ number_format($quotation->discount, 0) }}</td>
                </tr>
                @endif
                @if($quotation->gst_percent > 0)
                    @if($quotation->gst_split)
                    <tr>
                        <td style="padding: 10px 8px; font-size: 13px; border-bottom: 1px solid #eee; text-align: right; color: #666; width: 60%;">CGST ({{ $quotation->gst_percent / 2 }}%){{ $quotation->gst_inclusive ? ' - Inclusive' : '' }}:</td>
                        <td style="padding: 10px 8px; font-size: 13px; border-bottom: 1px solid #eee; text-align: right; font-weight: bold; color: {{ $quotation->gst_inclusive ? '#333' : '#27ae60' }};">{{ $quotation->gst_inclusive ? '' : '+ ' }}₹ {{ number_format($quotation->gst / 2, 0) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 8px; font-size: 13px; border-bottom: 1px solid #eee; text-align: right; color: #666; width: 60%;">SGST ({{ $quotation->gst_percent / 2 }}%){{ $quotation->gst_inclusive ? ' - Inclusive' : '' }}:</td>
                        <td style="padding: 10px 8px; font-size: 13px; border-bottom: 1px solid #eee; text-align: right; font-weight: bold; color: {{ $quotation->gst_inclusive ? '#333' : '#27ae60' }};">{{ $quotation->gst_inclusive ? '' : '+ ' }}₹ {{ number_format($quotation->gst / 2, 0) }}</td>
                    </tr>
                    @else
                    <tr>
                        <td style="padding: 10px 8px; font-size: 13px; border-bottom: 1px solid #eee; text-align: right; color: #666; width: 60%;">GST ({{ $quotation->gst_percent }}%){{ $quotation->gst_inclusive ? ' - Inclusive' : '' }}:</td>
                        <td style="padding: 10px 8px; font-size: 13px; border-bottom: 1px solid #eee; text-align: right; font-weight: bold; color: {{ $quotation->gst_inclusive ? '#333' : '#27ae60' }};">{{ $quotation->gst_inclusive ? '' : '+ ' }}₹ {{ number_format($quotation->gst, 0) }}</td>
                    </tr>
                    @endif
                @endif
                <tr style="background-color: #f0f4f8;">
                    <td style="padding: 12px 8px; font-size: 14px; text-align: right; font-weight: bold; color: #333;">Grand Total:</td>
                    <td style="padding: 12px 8px; font-size: 16px; text-align: right; font-weight: bold; color: #333;">₹ {{ number_format($quotation->grand_total, 0) }}</td>
                </tr>
            </table>
        </div>
    </div>

    
    <div class="footer">
        <div class="signature-box">
            <div class="signature-company">For {{ $quotation->company->name ?? '' }}</div>
            <div class="signature-text">Authorized Signatory</div>
        </div>
    </div>
</body>
</html>
