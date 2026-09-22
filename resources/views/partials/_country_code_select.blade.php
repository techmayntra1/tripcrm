{{-- Dialling-code select for a mobile input-group. Pass $selected (defaults to India). --}}
<select class="form-select js-country-code @error('country_code') is-invalid @enderror" id="country_code" name="country_code" title="Country code">
    @foreach(\App\Support\Countries::dialOptions() as $code => $label)
        <option value="{{ $code }}" {{ ($selected ?? '+91') == $code ? 'selected' : '' }}>{{ $label }}</option>
    @endforeach
</select>

@once
@push('styles')
<style>
    /* Select2 inside a Bootstrap input-group: keep it narrow and square off the joined edge. */
    .input-group > .select2-container {
        flex: 0 0 auto;
        width: 150px !important;
    }
    .input-group > .select2-container .select2-selection--single {
        height: 100%;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
    .select2-container--bootstrap-5 .select2-dropdown {
        min-width: 260px;
    }
</style>
@endpush

@push('scripts')
<script>
$(function () {
    $('.js-country-code').select2({
        width: '150px',
        theme: 'bootstrap-5',
        placeholder: 'Code',
        dropdownAutoWidth: true
    });
});
</script>
@endpush
@endonce
