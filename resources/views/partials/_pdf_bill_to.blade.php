{{-- "Bill To" customer block shared by the quotation and invoice PDFs. Expects $customer (nullable). --}}
<div class="customer-name">{{ $customer->name ?? '-' }}</div>
@if($customer)
<div class="customer-info">
    @if($customer->company_name)<strong>{{ $customer->company_name }}</strong><br>@endif
    @if($customer->address){{ $customer->address }}<br>@endif
    @if($customer->mobile)Phone: {{ $customer->mobile }}<br>@endif
    @if($customer->gst_number)GST: {{ $customer->gst_number }}<br>@endif
    @if($customer->company_trn)TRN: {{ $customer->company_trn }}@endif
</div>
@endif
