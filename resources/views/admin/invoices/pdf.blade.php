@php
    $currencySymbol = pdfCurrencySymbol($invoice);
    $company = $invoice->company;
    $customer = $invoice->customer;
    $fmtDate = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('d-M-Y') : '';
    $money = fn ($n) => number_format((float) $n, 2);

    // "Tax Invoice" only when the issuing company is tax-registered (TRN / GSTIN).
    $docTitle = ($company && (($company->is_uae && $company->vat_number) || (!$company->is_uae && $company->gst_number)))
        ? 'Tax Invoice' : 'Invoice';

    $bank = $bank ?? null;
    $terms = $terms ?? $invoice->terms;

    // 971526836553 -> +971 52 683 6553 (UAE numbers saved without spacing)
    $companyPhone = $company->phone ?? '';
    $phoneDigits = preg_replace('/\D/', '', $companyPhone);
    if (preg_match('/^(?:00)?971(\d{2})(\d{3})(\d{4})$/', $phoneDigits, $m)) {
        $companyPhone = "+971 {$m[1]} {$m[2]} {$m[3]}";
    }
    $companyWebsite = $company->website ?? '';

    $companyCityLine = $company ? collect([$company->city, $company->state, $company->pincode])->filter()->implode(', ') : '';
    $companyCityLine = trim($companyCityLine . ($company ? ($companyCityLine ? ', ' : '') . $company->country_label : ''));
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $docTitle }} {{ $invoice->invoice_number }}</title>
    <style>
        {{ pdfCurrencyFontFace() }}
        @page {
            size: A4;
            margin: 36mm 10mm 20mm 10mm;
        }
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.35;
            color: #222;
            margin: 0;
            padding: 0;
        }

        /* Repeated on every page */
        .page-header {
            position: fixed;
            top: -30mm;
            left: 0;
            right: 0;
            height: 26mm;
        }
        .page-footer {
            position: fixed;
            bottom: -14mm;
            left: 0;
            right: 0;
            height: 10mm;
            border-top: 1px solid #405189;
            padding-top: 6px;
            font-size: 9.5px;
            color: #333;
        }
        .company-logo {
            height: 24mm;
            width: auto;
        }
        .doc-title {
            font-size: 22px;
            font-weight: bold;
            color: #405189;
            line-height: 1.1;
        }
        .doc-number {
            font-size: 10px;
            color: #333;
            margin-top: 2px;
        }

        table { border-collapse: collapse; }
        .layout { width: 100%; }
        .layout td { vertical-align: top; padding: 0; }

        /* Bordered boxes & grids */
        .grid {
            width: 100%;
            border: 1px solid #405189;
        }
        .party th, .grid th {
            background-color: #405189;
            color: #fff;
            font-size: 9.5px;
            font-weight: bold;
            text-align: center;
            padding: 6px;
            border: 1px solid #405189;
        }
        .party .gap { border: none; padding: 0; }
        .party .cell {
            border: 1px solid #405189;
            padding: 8px 9px;
            font-size: 10px;
            line-height: 1.6;
            vertical-align: top;
            word-wrap: break-word;
        }
        .grid td {
            padding: 6px;
            font-size: 10px;
            border: 1px solid #405189;
        }
        .box-name {
            font-size: 11px;
            font-weight: bold;
            color: #111;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .muted { color: #777; font-size: 8px; }

        .totals td {
            padding: 7px 8px;
            font-size: 10px;
            border: 1px solid #405189;
        }
        .totals .label { text-align: right; font-weight: bold; width: 70%; }
        .totals .value { text-align: right; white-space: nowrap; }
        .totals .grand td { background-color: #eef0f7; font-weight: bold; font-size: 11px; color: #405189; }

        .words {
            margin-top: 12px;
            padding: 8px 10px;
            background-color: #eef0f7;
            border-left: 4px solid #405189;
            font-size: 10px;
        }
        .words-label { font-weight: bold; color: #405189; }

        /* Titled panels: bank details, notes, terms */
        .panel {
            width: 100%;
            border: 1px solid #405189;
        }
        .panel th {
            background-color: #405189;
            color: #fff;
            font-size: 9.5px;
            font-weight: bold;
            text-align: left;
            padding: 6px 9px;
            letter-spacing: 0.3px;
        }
        .panel td { padding: 5px 9px; font-size: 10px; vertical-align: top; }
        .panel-note { font-weight: bold; color: #333; padding-top: 8px !important; }
        .kv-key { width: 22%; color: #555; border-top: 1px solid #e3e6f0; }
        .kv-val { border-top: 1px solid #e3e6f0; }
        .terms { font-size: 9px; line-height: 1.7; color: #333; padding: 8px 9px !important; }

        .declaration {
            border: 1px dashed #9aa3c4;
            padding: 8px 10px;
            font-size: 9px;
            line-height: 1.55;
            color: #333;
        }
        .declaration-title { font-weight: bold; color: #405189; font-size: 10px; margin-bottom: 3px; }
        .signature-rule { border-top: 1px solid #333; margin: 4px 15px 0 15px; }
        .signature-company { font-size: 9.5px; color: #405189; font-weight: bold; margin-top: 2px; }

        .pdf-info {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-left: 3px solid #405189;
            padding: 8px;
        }
        .signature-line {
            font-weight: bold;
            font-size: 10.5px;
            margin-top: 4px;
        }
    </style>
</head>
<body>

    {{-- Header (every page) --}}
    <div class="page-header">
        <table class="layout">
            <tr>
                <td style="width: 50%;">
                    @php $logoDataUri = $company?->logo_data_uri; @endphp
                    @if($logoDataUri)
                        <img src="{{ $logoDataUri }}" class="company-logo" alt="">
                    @else
                        <div class="box-name" style="font-size: 14px; color: #405189;">{{ $company->name ?? '' }}</div>
                    @endif
                </td>
                <td style="width: 50%; text-align: right;">
                    <div class="doc-title">{{ $docTitle }}</div>
                    <div class="doc-number">#{{ $invoice->invoice_number }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Footer (every page) --}}
    <div class="page-footer">
        <table class="layout">
            <tr>
                <td style="width: 33%; text-align: left;">{{ $companyPhone }}</td>
                <td style="width: 34%; text-align: center;">{{ $company->email ?? '' }}</td>
                <td style="width: 33%; text-align: right;">{{ $companyWebsite }}</td>
            </tr>
        </table>
    </div>

    {{-- Invoice To / Company Info --}}
    {{-- One table so both boxes always share the same height --}}
    <table class="party" style="width: 100%;">
        <tr>
            <th style="width: 49%;">Invoice To</th>
            <td class="gap" style="width: 2%;"></td>
            <th style="width: 49%;">Company Info</th>
        </tr>
        <tr>
                        <td class="cell">
                            <div class="box-name">{{ $customer->name ?? '-' }}</div>
                            @if($customer)
                                @if($customer->company_name){{ $customer->company_name }}<br>@endif
                                @if($customer->address){{ $customer->address }}<br>@endif
                                @if($customer->email){{ $customer->email }}<br>@endif
                                @if($customer->mobile){{ $customer->full_mobile }}<br>@endif
                                @if($customer->company_trn)<strong>TRN: {{ $customer->company_trn }}</strong><br>@endif
                                @if($customer->gst_number)<strong>GSTIN: {{ $customer->gst_number }}</strong><br>@endif
                                @if($customer->company_lrn)LRN No: {{ $customer->company_lrn }}@endif
                            @endif
                        </td>
                        <td class="gap"></td>
                        <td class="cell">
                            <div class="box-name">{{ strtoupper($company->name ?? '') }}</div>
                            @if($company)
                                @if($company->address){!! nl2br(e($company->address)) !!}<br>@endif
                                @if($companyCityLine){{ $companyCityLine }}<br>@endif
                                @if($company->is_uae && $company->vat_number)<strong>TRN: {{ $company->vat_number }}</strong><br>@endif
                                @if(!$company->is_uae && $company->gst_number)<strong>GSTIN: {{ $company->gst_number }}</strong><br>@endif
                                @if($company->lrn_number)LRN No: {{ $company->lrn_number }}@endif
                            @endif
                        </td>
        </tr>
    </table>

    {{-- Title / dates / agent --}}
    <table class="grid" style="margin-top: 12px;">
        <thead>
            <tr>
                <th style="width: 30%;">Title</th>
                <th style="width: 25%;">Invoice Date</th>
                <th style="width: 20%;">Due Date</th>
                <th style="width: 25%;">Agent Name</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">{{ $invoice->subject ?: ($invoice->trip->name ?? '-') }}</td>
                <td class="text-center">{{ $fmtDate($invoice->date) }}</td>
                <td class="text-center">{{ $fmtDate($invoice->due_date) }}</td>
                <td class="text-center">{{ $invoice->agent->name ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Line items --}}
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

        $hasLineVat = collect($invoice->items)->contains(fn ($i) => isset($i['vat_rate']));

        // Single rate in the header ("VAT (5%)") when every line shares it.
        $vatRates = collect($invoice->items)->pluck('vat_rate')->filter(fn ($r) => $r !== null && $r !== '')
            ->map(fn ($r) => rtrim(rtrim(number_format((float) $r, 2), '0'), '.'))->unique()->values();
        $vatHeader = $vatRates->count() === 1 ? 'VAT (' . $vatRates->first() . '%)' : 'VAT';

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
    <table class="grid" style="margin-top: 10px;">
        <thead>
            <tr>
                <th style="width: 6%;">#</th>
                <th>Description</th>
                @if($hasDimensions)
                <th style="width: 7%;">Height</th>
                <th style="width: 7%;">Width</th>
                <th style="width: 7%;">Sqft</th>
                @endif
                <th style="width: 13%;">Price ({{ $currencySymbol }})</th>
                <th style="width: 10%;">Qty / Unit</th>
                <th style="width: 14%;">Amount ({{ $currencySymbol }})</th>
                @if($hasLineVat)
                <th style="width: 11%;">{{ $vatHeader }}</th>
                <th style="width: 13%;">Total ({{ $currencySymbol }})</th>
                @endif
                @if($hasLineTax)
                <th style="width: 9%;">Tax</th>
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
                $itemRate = $item['rate'] ?? 0;
                if ($hasLineVat) {
                    // UAE: the service fee is folded into rate/amount, never shown separately
                    $vatLine = vatLineDisplay($item);
                    $itemRate = $vatLine['rate'];
                    $itemAmount = $vatLine['amount'];
                }
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-left">@if(!empty($item['service_name']))<strong>{{ $item['service_name'] }}</strong>@if(!empty($item['passenger_type'])) <span class="muted">· {{ $item['passenger_type'] }}</span>@endif @if(!empty($item['description']) && trim($item['description']) !== trim($item['service_name']))<br>{{ $item['description'] }}@endif @else{{ $item['description'] ?? '-' }}@if(!empty($item['passenger_type'])) <span class="muted">· {{ $item['passenger_type'] }}</span>@endif @endif</td>
                @if($hasDimensions)
                <td class="text-right">{{ $formatDimension($item['height'] ?? null) }}</td>
                <td class="text-right">{{ $formatDimension($item['width'] ?? null) }}</td>
                <td class="text-right">{{ $formatDimension($item['total'] ?? null) }}</td>
                @endif
                <td class="text-right">{{ $money($itemRate) }}</td>
                <td class="text-center">{{ rtrim(rtrim(number_format((float) ($item['qty'] ?? 0), 2, '.', ''), '0'), '.') }}</td>
                <td class="text-right">{{ $money($itemAmount) }}</td>
                @if($hasLineVat)
                <td class="text-right">{{ $money($vatLine['vat']) }}</td>
                <td class="text-right">{{ $money($vatLine['total']) }}</td>
                @endif
                @if($hasLineTax)
                @php $rowTaxType = $item['tax_type'] ?? 'none'; $rowTaxRate = (float) ($item['tax_rate'] ?? 0); @endphp
                <td class="text-center">@if($rowTaxType !== 'none' && $rowTaxRate > 0){{ strtoupper($rowTaxType) }} {{ rtrim(rtrim(number_format($rowTaxRate, 2), '0'), '.') }}%@else-@endif</td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
    @elseif($invoice->invoice_type == 'pdf')
    <div class="pdf-info" style="margin-top: 10px;">
        <div class="bold">PDF Invoice Uploaded</div>
        @if($invoice->pdf_description)
        <div style="color: #666; font-size: 9px;">{{ $invoice->pdf_description }}</div>
        @endif
    </div>
    @endif

    {{-- Totals --}}
    @php $hasLineTax = $hasLineTax ?? false; $lineTaxByType = $lineTaxByType ?? ['gst' => 0, 'vat' => 0]; @endphp
    <table class="totals" style="width: 100%; margin-top: 10px;">
        <tr>
            <td class="label">Sub Total</td>
            <td class="value">{{ $currencySymbol }} {{ $money($invoice->subtotal + $invoice->service_fee) }}</td>
        </tr>
        @if($invoice->discount > 0)
        <tr>
            <td class="label">Discount</td>
            <td class="value">- {{ $currencySymbol }} {{ $money($invoice->discount) }}</td>
        </tr>
        @endif
        @if($invoice->is_vat)
        <tr>
            <td class="label">Total VAT</td>
            <td class="value">{{ $currencySymbol }} {{ $money($invoice->gst) }}</td>
        </tr>
        @elseif($hasLineTax)
            @if($lineTaxByType['gst'] > 0)
                @if($invoice->gst_split)
                <tr>
                    <td class="label">CGST{{ $invoice->gst_inclusive ? ' (Inclusive)' : '' }}</td>
                    <td class="value">{{ $currencySymbol }} {{ $money($lineTaxByType['gst'] / 2) }}</td>
                </tr>
                <tr>
                    <td class="label">SGST{{ $invoice->gst_inclusive ? ' (Inclusive)' : '' }}</td>
                    <td class="value">{{ $currencySymbol }} {{ $money($lineTaxByType['gst'] / 2) }}</td>
                </tr>
                @else
                <tr>
                    <td class="label">GST{{ $invoice->gst_inclusive ? ' (Inclusive)' : '' }}</td>
                    <td class="value">{{ $currencySymbol }} {{ $money($lineTaxByType['gst']) }}</td>
                </tr>
                @endif
            @endif
            @if($lineTaxByType['vat'] > 0)
            <tr>
                <td class="label">Total VAT</td>
                <td class="value">{{ $currencySymbol }} {{ $money($lineTaxByType['vat']) }}</td>
            </tr>
            @endif
        @elseif($invoice->gst_percent > 0)
            @if($invoice->gst_split)
            <tr>
                <td class="label">CGST ({{ $invoice->gst_percent / 2 }}%){{ $invoice->gst_inclusive ? ' (Inclusive)' : '' }}</td>
                <td class="value">{{ $currencySymbol }} {{ $money($invoice->gst / 2) }}</td>
            </tr>
            <tr>
                <td class="label">SGST ({{ $invoice->gst_percent / 2 }}%){{ $invoice->gst_inclusive ? ' (Inclusive)' : '' }}</td>
                <td class="value">{{ $currencySymbol }} {{ $money($invoice->gst / 2) }}</td>
            </tr>
            @else
            <tr>
                <td class="label">GST ({{ $invoice->gst_percent }}%){{ $invoice->gst_inclusive ? ' (Inclusive)' : '' }}</td>
                <td class="value">{{ $currencySymbol }} {{ $money($invoice->gst) }}</td>
            </tr>
            @endif
        @endif
        <tr class="grand">
            <td class="label">Total</td>
            <td class="value">{{ $currencySymbol }} {{ $money($invoice->grand_total) }}</td>
        </tr>
        @foreach($invoice->incomes->sortBy('income_date') as $payment)
        <tr>
            <td class="label" style="font-weight: normal;">Payment Received ({{ $payment->income_date->format('d-M-Y') }})</td>
            <td class="value">- {{ $currencySymbol }} {{ $money($payment->amount) }}</td>
        </tr>
        @endforeach
        @if($invoice->amount_paid > 0)
        <tr class="grand">
            <td class="label">Balance Due</td>
            <td class="value">{{ $currencySymbol }} {{ $money(max($invoice->balance_due, 0)) }}</td>
        </tr>
        @endif
    </table>


    {{-- Amount in words --}}
    <div class="words">
        <span class="words-label">Amount in words:</span> {{ amountInWords($invoice->grand_total, $invoice) }}
        @if($invoice->amount_paid > 0 && $invoice->balance_due > 0)
        <br><span class="words-label">Pending amount in words:</span> {{ amountInWords($invoice->balance_due, $invoice) }}
        @endif
    </div>

    {{-- Bank details --}}
    @if($bank)
    <table class="panel" style="margin-top: 12px; page-break-inside: avoid;">
        <tr><th colspan="2">Bank Details</th></tr>
        <tr>
            <td colspan="2" class="panel-note">Make all cheques payable / online transfers to the bank account below:</td>
        </tr>
        <tr><td class="kv-key">Bank</td><td class="kv-val">{{ $bank->bank_name }}</td></tr>
        <tr><td class="kv-key">Account Name</td><td class="kv-val">{{ $bank->account_holder ?: strtoupper($company->name ?? '') }}</td></tr>
        <tr><td class="kv-key">Account No.</td><td class="kv-val bold">{{ $bank->account_number }}</td></tr>
        @if($bank->bank_code)
        {{-- IBAN printed in groups of four (AE62 0330 0000 ...) --}}
        <tr><td class="kv-key">{{ $bank->bank_code_label }}</td><td class="kv-val bold">{{ $bank->is_uae ? trim(chunk_split(str_replace(' ', '', $bank->bank_code), 4, ' ')) : $bank->bank_code }}</td></tr>
        @endif
        @if($bank->branch)
        <tr><td class="kv-key">Branch</td><td class="kv-val">{{ $bank->branch }}</td></tr>
        @endif
        <tr><td class="kv-key">Currency</td><td class="kv-val">{{ $bank->currency_code }}</td></tr>
    </table>
    @endif

    {{-- Notes --}}
    @if($invoice->notes)
    <table class="panel" style="margin-top: 12px;">
        <tr><th>Notes</th></tr>
        <tr><td class="terms">{!! nl2br(e($invoice->notes)) !!}</td></tr>
    </table>
    @endif

    {{-- Terms & Conditions --}}
    @if($terms)
    {{-- Kept in one block so the heading never strands at the foot of a page --}}
    <div style="margin-top: 12px; page-break-inside: avoid;">
        <table class="panel">
            <tr><th>Terms &amp; Conditions</th></tr>
            <tr><td class="terms">{!! nl2br(e($terms)) !!}</td></tr>
        </table>
    </div>
    @endif

    {{-- Payment terms --}}
    @if($invoice->payment_terms)
    <table class="panel" style="margin-top: 12px;">
        <tr><th>Payment Terms</th></tr>
        <tr><td class="terms">{!! nl2br(e($invoice->payment_terms)) !!}</td></tr>
    </table>
    @endif

    {{-- Declaration + signature --}}
    <table class="layout" style="margin-top: 16px; page-break-inside: avoid;">
        <tr>
            <td style="width: 58%; vertical-align: bottom;">
                <div class="declaration">
                    <div class="declaration-title">Declaration</div>
                    Please advise us of any discrepancies within 2 working days from the date of receipt, otherwise the invoice/receipt is considered as accepted. After that, no queries will be entertained under any circumstances.
                </div>
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 38%; text-align: center; vertical-align: bottom;">
                @php $stampDataUri = $company?->stamp_data_uri; @endphp
                @if($stampDataUri)
                    <img src="{{ $stampDataUri }}" style="height: 115px; width: auto;" alt="">
                @else
                    <div style="height: 60px;"></div>
                @endif
                <div class="signature-rule"></div>
                <div class="signature-line">Authorised Signatory</div>
                <div class="signature-company">For, {{ $company->name ?? '' }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
