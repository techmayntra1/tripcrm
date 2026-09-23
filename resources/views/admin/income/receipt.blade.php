@php $currencySymbol = currencySymbol($income); @endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Receipt {{ $income->receipt_number }}</title>
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
            border-bottom: 3px solid #0ab39c;
        }
        .company-section {
            float: left;
            width: 55%;
        }
        .receipt-section {
            float: right;
            width: 45%;
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
        .receipt-title {
            font-size: 28px;
            font-weight: bold;
            color: #0ab39c;
            margin-bottom: 8px;
        }
        .receipt-details {
            font-size: 12px;
            color: #333;
        }
        .paid-badge {
            display: inline-block;
            margin-top: 10px;
            padding: 5px 15px;
            background-color: #0ab39c;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            border-radius: 4px;
        }
        .info-row {
            margin-bottom: 25px;
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
        }
        .customer-info {
            font-size: 12px;
            color: #555;
            line-height: 1.6;
        }
        .amount-box {
            background-color: #f0fbf9;
            border: 1px solid #0ab39c;
            border-left: 4px solid #0ab39c;
            padding: 18px 20px;
            margin-bottom: 25px;
        }
        .amount-box .label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
        }
        .amount-box .value {
            font-size: 30px;
            font-weight: bold;
            color: #0ab39c;
        }
        .amount-box .words {
            font-size: 12px;
            color: #555;
            font-style: italic;
            margin-top: 4px;
        }
        table.detail {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        table.detail th, table.detail td {
            border: 1px solid #ccc;
            padding: 9px 12px;
            font-size: 12px;
            text-align: left;
        }
        table.detail th {
            background-color: #f5f5f5;
            width: 40%;
            color: #555;
        }
        table.summary {
            width: 55%;
            float: right;
            border-collapse: collapse;
            margin-top: 5px;
        }
        table.summary td {
            padding: 8px 12px;
            font-size: 12px;
            border-bottom: 1px solid #eee;
        }
        table.summary td.label {
            color: #666;
        }
        table.summary td.value {
            text-align: right;
            font-weight: bold;
            color: #333;
        }
        table.summary tr.balance td {
            border-top: 2px solid #333;
            border-bottom: none;
            font-size: 14px;
        }
        .footer {
            margin-top: 70px;
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
            color: #0ab39c;
        }
        .signature-text {
            font-size: 11px;
            color: #888;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    @php
        $company = $invoice->company ?? null;
        $amountWords = 'Rupees ' . ucfirst(trim(numberToWords((int) round($income->amount)))) . ' only';
    @endphp
    <div class="header clearfix">
        <div class="company-section">
            @php $logoDataUri = $company?->logo_data_uri; @endphp
            @if($logoDataUri)
            <img src="{{ $logoDataUri }}" style="height: 70px; width: auto; margin-bottom: 8px;" alt="">
            @endif
            <div class="company-name">{{ $company->name ?? config('app.name') }}</div>
            <div class="company-info">
                @if($company)
                    @if($company->address){{ $company->address }}<br>@endif
                    @if($company->phone)Phone: {{ $company->phone }}<br>@endif
                    @if($company->email)Email: {{ $company->email }}<br>@endif
                    @if($company->gst_number)GST: {{ $company->gst_number }}@endif
                @endif
            </div>
        </div>
        <div class="receipt-section">
            <div class="receipt-title">RECEIPT</div>
            <div class="receipt-details">
                <strong>Receipt #:</strong> {{ $income->receipt_number }}<br>
                <strong>Date:</strong> {{ \Carbon\Carbon::parse($income->income_date)->format('d-m-Y') }}
                @if($invoice)
                <br><strong>Against Invoice #:</strong> {{ $invoice->invoice_number }}
                @endif
            </div>
            <span class="paid-badge">PAYMENT RECEIVED</span>
        </div>
    </div>

    <div class="info-row clearfix">
        <div class="section-label">RECEIVED FROM:</div>
        <div class="customer-name">{{ $income->customer->name ?? '-' }}</div>
        <div class="customer-info">
            @if($income->customer)
                @if($income->customer->address){{ $income->customer->address }}<br>@endif
                @if($income->customer->mobile)Phone: {{ $income->customer->mobile }}<br>@endif
                @if($income->customer->gst_number)GST: {{ $income->customer->gst_number }}@endif
            @endif
        </div>
    </div>

    <div class="amount-box">
        <div class="label">Amount Received</div>
        <div class="value">{{ $currencySymbol }} {{ number_format($income->amount, 2) }}</div>
        <div class="words">{{ $amountWords }}</div>
    </div>

    <table class="detail">
        <tr>
            <th>Payment Mode</th>
            <td>{{ $income->paymentMode->name ?? '-' }}</td>
        </tr>
        @if($income->cheque_number)
        <tr>
            <th>Cheque / Reference No.</th>
            <td>{{ $income->cheque_number }}@if($income->cheque_date) (dated {{ \Carbon\Carbon::parse($income->cheque_date)->format('d-m-Y') }})@endif</td>
        </tr>
        @endif
        @if($income->bank_name)
        <tr>
            <th>Bank</th>
            <td>{{ $income->bank_name }}</td>
        </tr>
        @endif
        @if($income->trip)
        <tr>
            <th>Trip</th>
            <td>{{ $income->trip->trip_number }} — {{ $income->trip->name }}</td>
        </tr>
        @endif
        @if($income->description)
        <tr>
            <th>Note</th>
            <td>{{ $income->description }}</td>
        </tr>
        @endif
    </table>

    @if($invoice)
    <div class="clearfix">
        <table class="summary">
            <tr>
                <td class="label">Invoice Total</td>
                <td class="value">{{ $currencySymbol }} {{ number_format($invoice->grand_total, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Paid To Date</td>
                <td class="value">{{ $currencySymbol }} {{ number_format($paidToDate, 2) }}</td>
            </tr>
            <tr class="balance">
                <td class="label">Balance Due</td>
                <td class="value">{{ $currencySymbol }} {{ number_format($balanceAfter, 2) }}</td>
            </tr>
        </table>
    </div>
    @endif

    <div class="footer">
        <div class="signature-box">
            <div class="signature-company">{{ $company->name ?? config('app.name') }}</div>
            <div class="signature-text">Authorised Signatory</div>
        </div>
    </div>
</body>
</html>
