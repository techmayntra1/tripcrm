@php $currentLogo = isset($company) ? $company->logo_url : null; @endphp
<div class="main-card mb-3 card">
    <div class="card-header">
        <i class="bi bi-image me-2"></i> Company Logo
    </div>
    <div class="card-body">
        <div class="text-center mb-3">
            <div class="border rounded d-flex align-items-center justify-content-center mx-auto bg-light" style="width: 160px; height: 160px; overflow: hidden;">
                <img id="logo_preview" src="{{ $currentLogo ?? '' }}" alt="Logo preview" style="max-width: 100%; max-height: 100%; object-fit: contain; {{ $currentLogo ? '' : 'display: none;' }}">
                <span id="logo_placeholder" class="text-muted small" style="{{ $currentLogo ? 'display: none;' : '' }}">
                    <i class="bi bi-image fs-1 d-block"></i> No logo
                </span>
            </div>
        </div>
        <input type="file" class="form-control" name="logo" id="logo_input" accept="image/png,image/jpeg">
        <small class="text-muted d-block mt-1">JPG or PNG, max 2 MB. Shown on quotations and invoices.</small>
        @if($currentLogo)
        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="remove_logo" {{ old('remove_logo') ? 'checked' : '' }}>
            <label class="form-check-label" for="remove_logo">Remove current logo</label>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('logo_input');
        const preview = document.getElementById('logo_preview');
        const placeholder = document.getElementById('logo_placeholder');
        const removeBox = document.getElementById('remove_logo');
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
    })();
</script>
@endpush
