{{-- Address card. State/Pincode switch to Emirate/P.O. Box when the region is UAE. Expects optional $company --}}
@php
    $currentState = old('state', $company->state ?? '');
    $indianStates = \App\Models\Company::INDIAN_STATES;
    $indianUTs = \App\Models\Company::INDIAN_UNION_TERRITORIES;
    $emirates = \App\Models\Company::UAE_EMIRATES;
    // A value saved before these lists existed still needs to show up.
    $knownStates = array_merge($indianStates, $indianUTs, $emirates);
    $unlisted = $currentState && !in_array($currentState, $knownStates) ? $currentState : null;
@endphp
<div class="main-card mb-3 card">
    <div class="card-header">
        <i class="bi bi-geo-alt me-2"></i> Address Information
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea class="form-control" name="address" rows="2" placeholder="Enter full address">{{ old('address', $company->address ?? '') }}</textarea>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">City</label>
                    <input type="text" class="form-control" name="city" value="{{ old('city', $company->city ?? '') }}" placeholder="City">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label" id="state_label">State</label>
                    <select class="form-select" name="state" id="company_state">
                        <option value="">Select</option>
                        @if($unlisted)
                            <option value="{{ $unlisted }}" selected>{{ $unlisted }}</option>
                        @endif
                        <optgroup label="States" data-country="india">
                            @foreach($indianStates as $s)
                                <option value="{{ $s }}" {{ $currentState == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Union Territories" data-country="india">
                            @foreach($indianUTs as $s)
                                <option value="{{ $s }}" {{ $currentState == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Emirates" data-country="uae">
                            @foreach($emirates as $s)
                                <option value="{{ $s }}" {{ $currentState == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label" id="pincode_label">Pincode</label>
                    <input type="text" class="form-control" name="pincode" id="company_pincode" value="{{ old('pincode', $company->pincode ?? '') }}" maxlength="10" placeholder="Pincode">
                </div>
            </div>
        </div>
    </div>
</div>
