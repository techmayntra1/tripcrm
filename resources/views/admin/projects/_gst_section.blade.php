@php
    $gstPercentVal = old('gst_percent', $project->gst_percent ?? 0);
    $gstInclusiveVal = old('gst_inclusive', $project->gst_inclusive ?? false);
    $gstSplitVal = old('gst_split', $project->gst_split ?? false);
@endphp
<div id="projectGstSection" class="mb-0" style="display: none;">
    <hr class="my-2">
    <label class="form-label fw-bold"><i class="bi bi-percent me-1"></i> GST</label>
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label for="gst_percent" class="form-label small mb-1">GST Rate</label>
            <select class="form-select" name="gst_percent" id="gst_percent">
                <option value="0">No GST</option>
                @foreach($gstRates as $rate)
                    <option value="{{ $rate->percentage }}" {{ (string) $gstPercentVal === (string) $rate->percentage ? 'selected' : '' }}>{{ $rate->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">GST Amount</label>
            <div class="input-group">
                <span class="input-group-text text-success" id="gstSign">+ ₹</span>
                <input type="text" class="form-control" id="gstAmountDisplay" value="0" readonly style="background-color: #e9ecef;">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Total with GST</label>
            <div class="input-group">
                <span class="input-group-text">₹</span>
                <input type="text" class="form-control fw-bold" id="totalWithGstDisplay" value="0" readonly style="background-color: #e9ecef;">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="gst_inclusive" id="gst_inclusive" value="1" {{ $gstInclusiveVal ? 'checked' : '' }}>
                <label class="form-check-label small" for="gst_inclusive">GST Inclusive</label>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="gst_split" id="gst_split" value="1" {{ $gstSplitVal ? 'checked' : '' }}>
                <label class="form-check-label small" for="gst_split">Split SGST + CGST</label>
            </div>
        </div>
    </div>
    <div class="row g-2 mt-1" id="gstSplitDisplay" style="display: none;">
        <div class="col-md-3 offset-md-3">
            <div class="input-group input-group-sm">
                <span class="input-group-text">CGST</span>
                <input type="text" class="form-control" id="cgstDisplay" readonly style="background-color: #e9ecef;">
            </div>
        </div>
        <div class="col-md-3">
            <div class="input-group input-group-sm">
                <span class="input-group-text">SGST</span>
                <input type="text" class="form-control" id="sgstDisplay" readonly style="background-color: #e9ecef;">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const companySelect = document.getElementById('company_id');
    const budgetInput = document.getElementById('budget');
    const section = document.getElementById('projectGstSection');
    if (!companySelect || !budgetInput || !section) return;

    const gstPercent = document.getElementById('gst_percent');
    const gstInclusive = document.getElementById('gst_inclusive');
    const gstSplit = document.getElementById('gst_split');
    const gstAmountDisplay = document.getElementById('gstAmountDisplay');
    const totalWithGstDisplay = document.getElementById('totalWithGstDisplay');
    const gstSign = document.getElementById('gstSign');
    const splitDisplay = document.getElementById('gstSplitDisplay');
    const cgstDisplay = document.getElementById('cgstDisplay');
    const sgstDisplay = document.getElementById('sgstDisplay');

    function companyHasGst() {
        const opt = companySelect.options[companySelect.selectedIndex];
        return opt && opt.getAttribute('data-has-gst') === '1';
    }

    function computeGst() {
        const visible = section.style.display !== 'none';
        const base = parseFloat(budgetInput.value) || 0;
        const percent = visible ? (parseFloat(gstPercent.value) || 0) : 0;
        const inclusive = gstInclusive.checked;

        let gst = 0, total = base;
        if (percent > 0) {
            if (inclusive) {
                gst = base * percent / (100 + percent);
                total = base;
                gstSign.textContent = '₹';
                gstSign.classList.remove('text-success');
            } else {
                gst = base * percent / 100;
                total = base + gst;
                gstSign.textContent = '+ ₹';
                gstSign.classList.add('text-success');
            }
        }
        gst = Math.round(gst);
        gstAmountDisplay.value = gst.toLocaleString('en-IN');
        totalWithGstDisplay.value = Math.round(total).toLocaleString('en-IN');

        const splitOn = gstSplit.checked && percent > 0;
        splitDisplay.style.display = splitOn ? '' : 'none';
        if (splitOn) {
            const half = Math.round(gst / 2);
            cgstDisplay.value = half.toLocaleString('en-IN');
            sgstDisplay.value = half.toLocaleString('en-IN');
        }
    }

    function updateVisibility() {
        section.style.display = companyHasGst() ? '' : 'none';
        computeGst();
    }

    companySelect.addEventListener('change', updateVisibility);
    budgetInput.addEventListener('input', computeGst);
    gstPercent.addEventListener('change', computeGst);
    gstInclusive.addEventListener('change', computeGst);
    gstSplit.addEventListener('change', computeGst);

    updateVisibility();
});
</script>
@endpush
