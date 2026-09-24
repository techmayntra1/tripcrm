{{--
    Terms textarea pre-fillable from a TermTemplate master.
    Params: $name (field name), $label, $icon, $templates (collection), $value (current text or null),
            $useDefault (bool: fill the default template when $value is empty — true on create forms)
--}}
@php
    $useDefault = $useDefault ?? false;
    $default = $templates->firstWhere('is_default', true);
    if (($value === null || $value === '') && $useDefault && $default) {
        $value = $default->content;
    }
    $selected = $templates->first(fn ($t) => $value !== null && trim($t->content) === trim($value));
@endphp
<div class="main-card mb-3 card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div><i class="bi {{ $icon }} me-2"></i> {{ $label }}</div>
        @if($templates->isNotEmpty())
        <select class="form-select form-select-sm js-term-template" data-target="{{ $name }}_input" style="max-width: 220px;">
            <option value="">— Select template —</option>
            @foreach($templates as $template)
            <option value="{{ $template->id }}" data-content="{{ $template->content }}" {{ $selected && $selected->id === $template->id ? 'selected' : '' }}>
                {{ $template->name }}{{ $template->is_default ? ' (Default)' : '' }}
            </option>
            @endforeach
        </select>
        @endif
    </div>
    <div class="card-body">
        <textarea class="form-control @error($name) is-invalid @enderror" id="{{ $name }}_input" name="{{ $name }}" rows="4" maxlength="5000" placeholder="Enter {{ strtolower($label) }}...">{{ $value }}</textarea>
        @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
@once
@push('scripts')
<script>
document.addEventListener('change', function (e) {
    if (!e.target.classList.contains('js-term-template')) return;
    const opt = e.target.options[e.target.selectedIndex];
    const target = document.getElementById(e.target.dataset.target);
    if (target && opt && opt.value) {
        target.value = opt.getAttribute('data-content') || '';
    }
});
</script>
@endpush
@endonce
