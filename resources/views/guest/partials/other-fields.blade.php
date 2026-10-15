{{--
    The patient's details when booking for someone else — shared by the
    guest booking page and the Patient Portal's "Book for Someone Else".
    Params: $o = ['id' => unique id suffix, 'sameAddress' => show the
    "Same address as mine" checkbox].
--}}
@php
    $oid = $o['id'] ?? '';
    $nameRx = "[A-Za-zÀ-ÿÑñ\\s'.\\-]+";
@endphp
<div class="row g-3 mb-3">
    @include('guest.partials.field', ['f' => ['name' => 'other_last_name', 'label' => 'Last name', 'icon' => 'bi-person', 'placeholder' => 'Last name', 'required' => true, 'col' => 'col-md-4', 'id' => $oid]])
    @include('guest.partials.field', ['f' => ['name' => 'other_first_name', 'label' => 'First name', 'icon' => 'bi-person', 'placeholder' => 'First name', 'required' => true, 'col' => 'col-md-4', 'id' => $oid]])
    @include('guest.partials.field', ['f' => ['name' => 'other_middle_name', 'label' => 'Middle name', 'icon' => 'bi-person', 'placeholder' => 'Middle name', 'optional' => true, 'col' => 'col-md-4', 'id' => $oid]])

    @include('guest.partials.field', ['f' => ['name' => 'other_birthdate', 'label' => 'Birthdate', 'icon' => 'bi-calendar-event', 'type' => 'date', 'required' => true, 'col' => 'col-md-4', 'id' => $oid,
        'attrs' => 'max="' . now()->format('Y-m-d') . '" data-age-target="#otherAge' . $oid . '"']])
    <div class="col-md-2">
        <label class="form-label">Age</label>
        <div class="input-icon"><i class="bi bi-person-vcard"></i><input type="text" id="otherAge{{ $oid }}" class="form-control" placeholder="—" disabled /></div>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="f_other_gender{{ $oid }}">Gender</label>
        <div class="input-icon @error('other_gender') has-error @enderror">
            <select name="other_gender" id="f_other_gender{{ $oid }}" class="form-select" required data-req="1">
                <option value="" disabled {{ old('other_gender') ? '' : 'selected' }}>Select</option>
                @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $gv => $gl)
                    <option value="{{ $gv }}" {{ old('other_gender') === $gv ? 'selected' : '' }}>{{ $gl }}</option>
                @endforeach
            </select>
        </div>
        @error('other_gender') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="f_other_relationship{{ $oid }}">They are my…</label>
        <div class="input-icon @error('other_relationship') has-error @enderror">
            <select name="other_relationship" id="f_other_relationship{{ $oid }}" class="form-select" required data-req="1">
                <option value="" disabled {{ old('other_relationship') ? '' : 'selected' }}>Select</option>
                @foreach (\App\Models\PatientInfo::RELATIONSHIPS as $rel)
                    <option value="{{ $rel }}" {{ old('other_relationship') === $rel ? 'selected' : '' }}>{{ $rel }}</option>
                @endforeach
            </select>
        </div>
        @error('other_relationship') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
    </div>

    @include('guest.partials.field', ['f' => ['name' => 'other_phone', 'label' => "Patient's mobile number", 'icon' => 'bi-telephone', 'type' => 'tel', 'placeholder' => '09XXXXXXXXX', 'optional' => true, 'col' => 'col-md-6', 'id' => $oid,
        'attrs' => 'inputmode="numeric" pattern="[0-9]{11}" maxlength="11" data-digits="1" title="Enter an 11-digit mobile number"']])
    <div class="col-md-6 d-flex align-items-end">
        <div class="small text-muted-2 pb-2"><i class="bi bi-shield-check me-1" style="color:var(--brand-700);"></i>
            If they're under 18, you'll be saved as their parent/guardian on file.</div>
    </div>
</div>

<div class="section-label">Patient's address</div>
@if (!empty($o['sameAddress']))
    <div class="form-check mb-2">
        <input class="form-check-input other-same-address" type="checkbox" name="other_same_address" value="1" id="otherSame{{ $oid }}" {{ old('other_same_address') ? 'checked' : '' }}>
        <label class="form-check-label small" for="otherSame{{ $oid }}">Same address as mine</label>
    </div>
@endif
<div class="row g-3 mb-3 other-address-fields">
    @include('guest.partials.field', ['f' => ['name' => 'other_addr_street', 'label' => 'Street / House No.', 'icon' => 'bi-signpost-2', 'placeholder' => '123 Sample St.', 'optional' => true, 'id' => $oid]])
    @include('guest.partials.field', ['f' => ['name' => 'other_addr_barangay', 'label' => 'Barangay', 'icon' => 'bi-geo', 'placeholder' => 'Barangay', 'optional' => true, 'id' => $oid]])
    @include('guest.partials.field', ['f' => ['name' => 'other_addr_city', 'label' => 'City / Municipality', 'icon' => 'bi-buildings', 'placeholder' => 'City / Municipality', 'required' => true, 'id' => $oid]])
    @include('guest.partials.field', ['f' => ['name' => 'other_addr_province', 'label' => 'Province', 'icon' => 'bi-map', 'placeholder' => 'Province', 'required' => true, 'id' => $oid]])
</div>
