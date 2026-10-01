@php
    $companyImages = [
        'logo' => ['title' => 'Company Logo', 'icon' => 'bi-image', 'current' => isset($company) ? $company->logo_url : null, 'help' => 'Shown at the top of quotations and invoices.'],
        'stamp' => ['title' => 'Company Stamp / Seal', 'icon' => 'bi-patch-check', 'current' => isset($company) ? $company->stamp_url : null, 'help' => 'Shown above "Authorized Signatory" on quotations and invoices. A PNG with a transparent background looks best.'],
    ];
@endphp
@foreach($companyImages as $field => $img)
<div class="main-card mb-3 card">
    <div class="card-header">
        <i class="bi {{ $img['icon'] }} me-2"></i> {{ $img['title'] }}
    </div>
    <div class="card-body js-image-upload" data-field="{{ $field }}">
        <div class="text-center mb-3">
            <div class="border rounded d-flex align-items-center justify-content-center mx-auto bg-light" style="width: 160px; height: 160px; overflow: hidden;">
                <img class="js-image-preview" src="{{ $img['current'] ?? '' }}" alt="{{ $img['title'] }} preview" style="max-width: 100%; max-height: 100%; object-fit: contain; {{ $img['current'] ? '' : 'display: none;' }}">
                <span class="js-image-placeholder text-muted small" style="{{ $img['current'] ? 'display: none;' : '' }}">
                    <i class="bi {{ $img['icon'] }} fs-1 d-block"></i> No {{ $field }}
                </span>
            </div>
        </div>
        <input type="file" class="form-control js-image-input" name="{{ $field }}" accept="image/png,image/jpeg">
        @error($field)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        <small class="text-muted d-block mt-1">JPG or PNG, max 2 MB. {{ $img['help'] }}</small>
        @if($img['current'])
        <div class="form-check mt-2">
            <input class="form-check-input js-image-remove" type="checkbox" name="remove_{{ $field }}" value="1" id="remove_{{ $field }}" {{ old('remove_' . $field) ? 'checked' : '' }}>
            <label class="form-check-label" for="remove_{{ $field }}">Remove current {{ $field }}</label>
        </div>
        @endif
    </div>
</div>
@endforeach

@push('scripts')
<script>
    document.querySelectorAll('.js-image-upload').forEach(function (box) {
        const input = box.querySelector('.js-image-input');
        const preview = box.querySelector('.js-image-preview');
        const placeholder = box.querySelector('.js-image-placeholder');
        const removeBox = box.querySelector('.js-image-remove');
        const originalSrc = preview.getAttribute('src');

        function showPreview(src) {
            preview.src = src || '';
            preview.style.display = src ? '' : 'none';
            placeholder.style.display = src ? 'none' : '';
        }

        input.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) {
                showPreview(removeBox && removeBox.checked ? '' : originalSrc);
                return;
            }
            if (removeBox) removeBox.checked = false;
            const reader = new FileReader();
            reader.onload = e => showPreview(e.target.result);
            reader.readAsDataURL(file);
        });

        if (removeBox) {
            removeBox.addEventListener('change', function () {
                if (this.checked) {
                    input.value = '';
                    showPreview('');
                } else {
                    showPreview(originalSrc);
                }
            });
        }
    });
</script>
@endpush
