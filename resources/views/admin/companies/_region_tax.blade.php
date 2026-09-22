{{-- Region + Tax Information card. Expects an optional $company (edit) --}}
@php
    $selectedCountry = old('country', $company->country ?? 'india');
    $currencies = currencyMap();
@endphp
<div class="main-card mb-3 card">
    <div class="card-header">
        <i class="bi bi-receipt me-2"></i> Tax Information
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Region <span class="text-danger">*</span></label>
                    <select class="form-select" name="country" id="company_country" required>
                        @foreach(\App\Models\Company::COUNTRIES as $key => $label)
                            <option value="{{ $key }}" data-currency="{{ $currencies[$key]['symbol'] }}" data-currency-code="{{ $currencies[$key]['code'] }}" {{ $selectedCountry == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Currency: <strong id="company_currency_label">{{ $currencies[$selectedCountry]['code'] }}</strong></small>
                </div>
            </div>
            <div class="col-md-4 tax-india">
                <div class="mb-3">
                    <label class="form-label">GST Number</label>
                    <input type="text" class="form-control" name="gst_number" value="{{ old('gst_number', $company->gst_number ?? '') }}" maxlength="15" placeholder="e.g., 24AABCK1234D1ZH">
                </div>
            </div>
            <div class="col-md-4 tax-india">
                <div class="mb-3">
                    <label class="form-label">PAN Number</label>
                    <input type="text" class="form-control" name="pan_number" value="{{ old('pan_number', $company->pan_number ?? '') }}" maxlength="10" placeholder="e.g., AABCK1234D">
                </div>
            </div>
            <div class="col-md-4 tax-uae">
                <div class="mb-3">
                    <label class="form-label">VAT Number (TRN)</label>
                    <input type="text" class="form-control" name="vat_number" value="{{ old('vat_number', $company->vat_number ?? '') }}" maxlength="30" placeholder="e.g., 100123456700003">
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const countrySelect = document.getElementById('company_country');
    if (!countrySelect) return;

    function applyRegion() {
        const country = countrySelect.value || 'india';
        const opt = countrySelect.options[countrySelect.selectedIndex];

        document.querySelectorAll('.tax-india').forEach(el => el.style.display = country === 'india' ? '' : 'none');
        document.querySelectorAll('.tax-uae').forEach(el => el.style.display = country === 'uae' ? '' : 'none');

        const label = document.getElementById('company_currency_label');
        if (label && opt) label.textContent = opt.dataset.currencyCode;

        // Address: Indian states vs UAE emirates, Pincode vs P.O. Box
        const stateSelect = document.getElementById('company_state');
        if (stateSelect) {
            stateSelect.querySelectorAll('optgroup[data-country]').forEach(group => {
                const match = group.dataset.country === country;
                group.hidden = !match;
                group.disabled = !match;
            });
            const selected = stateSelect.options[stateSelect.selectedIndex];
            if (selected && selected.parentElement.tagName === 'OPTGROUP' && selected.parentElement.dataset.country !== country) {
                stateSelect.value = '';
            }
            document.getElementById('state_label').textContent = country === 'uae' ? 'Emirate' : 'State';
        }
        const pincode = document.getElementById('company_pincode');
        if (pincode) {
            const isUae = country === 'uae';
            document.getElementById('pincode_label').textContent = isUae ? 'P.O. Box' : 'Pincode';
            pincode.placeholder = isUae ? 'P.O. Box' : 'Pincode';
            pincode.maxLength = isUae ? 10 : 6;
        }

        // Only offer banks from the same region; a company's banks always match its region.
        document.querySelectorAll('.bank-option[data-country]').forEach(el => {
            const match = el.dataset.country === country;
            el.style.display = match ? '' : 'none';
            if (!match) {
                const cb = el.querySelector('input[type="checkbox"]');
                if (cb) cb.checked = false;
            }
        });
        const emptyNote = document.getElementById('bank_region_empty');
        if (emptyNote) {
            const visible = Array.from(document.querySelectorAll('.bank-option[data-country]')).some(el => el.dataset.country === country);
            emptyNote.style.display = visible ? 'none' : '';
        }
    }

    countrySelect.addEventListener('change', applyRegion);
    applyRegion();
})();
</script>
@endpush
