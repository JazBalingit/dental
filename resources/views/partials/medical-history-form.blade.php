{{--
    The Medical History questions from the clinic's paper Patient Information
    Record. Fields post as mh[...]. Shared by guest booking, the Patient
    Portal, walk-ins and staff editing — everything comes in via $mhf so
    nothing leaks in from the including view:
      id      unique suffix for element ids (several forms can share a page)
      values  answers to prefill (a previous version), or []
      staff   true → also shows blood type / blood pressure / bleeding time,
              and no "I certify" box
--}}
@php
    $mid = $mhf['id'] ?? '';
    // Field-name prefix: "mh", or e.g. "mh_p12" when several people's forms share one <form>
    // (radio buttons with the same name would otherwise form a single group).
    $n = $mhf['name'] ?? 'mh';
    $staffMode = !empty($mhf['staff']);
    $pre = $mhf['values'] ?? [];
    $val = fn ($key, $default = null) => old("{$n}.{$key}", $pre[$key] ?? $default);
    $listVal = fn ($key) => (array) old("{$n}.{$key}", $pre[$key] ?? []);
@endphp
<div class="mh-form">
    {{-- 1–7: yes / no --}}
    <div class="mh-group-title">General health</div>
    <div class="mh-questions">
        @foreach (\App\Models\MedicalHistory::QUESTIONS as $key => [$question, $followUp])
            @php $current = $val($key); @endphp
            <div class="mh-q" data-mh-q>
                <div class="mh-q-row">
                    <span class="mh-q-label">{{ $loop->iteration }}. {{ $question }}</span>
                    <span class="mh-yn">
                        <label><input type="radio" name="{{ $n }}[{{ $key }}]" value="yes" {{ $staffMode ? '' : 'required' }} {{ $current === 'yes' ? 'checked' : '' }}> Yes</label>
                        <label><input type="radio" name="{{ $n }}[{{ $key }}]" value="no" {{ $staffMode ? '' : 'required' }} {{ $current === 'no' ? 'checked' : '' }}> No</label>
                    </span>
                </div>
                @if ($followUp)
                    <div class="mh-followup" data-mh-followup="{{ $key }}" {{ $current === 'yes' ? '' : 'hidden' }}>
                        <input type="text" class="form-control form-control-sm" name="{{ $n }}[{{ $key }}_detail]" maxlength="300"
                            placeholder="{{ $followUp }}" value="{{ $val($key . '_detail', '') }}">
                    </div>
                @endif
                @error("mh.{$key}") <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                @error("mh.{$key}_detail") <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
            </div>
        @endforeach
    </div>

    {{-- 8: allergies --}}
    <div class="mh-group-title">Are you allergic to any of the following? <span class="mh-hint">Tick all that apply — leave blank if none.</span></div>
    <div class="mh-checks">
        @foreach (\App\Models\MedicalHistory::ALLERGIES as $key => $label)
            <label class="mh-check"><input type="checkbox" name="{{ $n }}[allergies][]" value="{{ $key }}" {{ in_array($key, $listVal('allergies'), true) ? 'checked' : '' }}> {{ $label }}</label>
        @endforeach
    </div>
    <input type="text" class="form-control form-control-sm mt-2" name="{{ $n }}[allergies_other]" maxlength="200" placeholder="Other allergies (optional)" value="{{ $val('allergies_other', '') }}">

    {{-- 10: women --}}
    <div class="mh-group-title">For women only <span class="mh-hint">Skip if not applicable.</span></div>
    <div class="mh-questions">
        @foreach (\App\Models\MedicalHistory::WOMEN as $key => $question)
            @php $current = $val($key); @endphp
            <div class="mh-q">
                <div class="mh-q-row">
                    <span class="mh-q-label">{{ $question }}</span>
                    <span class="mh-yn">
                        <label><input type="radio" name="{{ $n }}[{{ $key }}]" value="yes" {{ $current === 'yes' ? 'checked' : '' }}> Yes</label>
                        <label><input type="radio" name="{{ $n }}[{{ $key }}]" value="no" {{ $current === 'no' ? 'checked' : '' }}> No</label>
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- 13: conditions --}}
    <div class="mh-group-title">Do you have or have you had any of the following? <span class="mh-hint">Tick all that apply.</span></div>
    <div class="mh-checks mh-checks-grid">
        @foreach (\App\Models\MedicalHistory::CONDITIONS as $key => $label)
            <label class="mh-check"><input type="checkbox" name="{{ $n }}[conditions][]" value="{{ $key }}" {{ in_array($key, $listVal('conditions'), true) ? 'checked' : '' }}> {{ $label }}</label>
        @endforeach
    </div>
    <input type="text" class="form-control form-control-sm mt-2" name="{{ $n }}[conditions_other]" maxlength="200" placeholder="Other condition (optional)" value="{{ $val('conditions_other', '') }}">

    {{-- Optional details --}}
    <details class="mh-more" {{ ($val('physician_name') || $val('previous_dentist')) ? 'open' : '' }}>
        <summary>Physician &amp; dental history <span class="mh-hint">(optional)</span></summary>
        <div class="row g-2 mt-1">
            <div class="col-md-6"><input type="text" class="form-control form-control-sm" name="{{ $n }}[physician_name]" maxlength="150" placeholder="Name of physician (Dr.)" value="{{ $val('physician_name', '') }}"></div>
            <div class="col-md-6"><input type="text" class="form-control form-control-sm" name="{{ $n }}[physician_specialty]" maxlength="150" placeholder="Specialty, if applicable" value="{{ $val('physician_specialty', '') }}"></div>
            <div class="col-md-6"><input type="text" class="form-control form-control-sm" name="{{ $n }}[physician_office]" maxlength="255" placeholder="Physician's office address" value="{{ $val('physician_office', '') }}"></div>
            <div class="col-md-6"><input type="text" class="form-control form-control-sm" name="{{ $n }}[physician_phone]" maxlength="30" placeholder="Physician's office number" value="{{ $val('physician_phone', '') }}"></div>
            <div class="col-md-6"><input type="text" class="form-control form-control-sm" name="{{ $n }}[previous_dentist]" maxlength="150" placeholder="Previous dentist (Dr.)" value="{{ $val('previous_dentist', '') }}"></div>
            <div class="col-md-6"><input type="text" class="form-control form-control-sm" name="{{ $n }}[last_dental_visit]" maxlength="100" placeholder="Last dental visit (e.g. March 2025)" value="{{ $val('last_dental_visit', '') }}"></div>
        </div>
    </details>

    {{-- 11: blood type — the patient may not know it --}}
    <div class="mh-group-title">Blood type <span class="mh-hint">Leave as "I don't know" if you're not sure.</span></div>
    <select class="form-select form-select-sm" name="{{ $n }}[blood_type]" style="max-width:220px;">
        <option value="">I don't know</option>
        @foreach (\App\Models\MedicalHistory::BLOOD_TYPES as $bt)
            <option value="{{ $bt }}" {{ $val('blood_type') === $bt ? 'selected' : '' }}>{{ $bt }}</option>
        @endforeach
    </select>

    @if (!$staffMode)
        <label class="mh-certify">
            <input type="checkbox" name="{{ $n }}[certify]" value="1" required {{ old($n . '.certify') ? 'checked' : '' }}>
            I certify that the medical information above is true and complete to the best of my knowledge.
        </label>
        @error('mh.certify') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
    @endif
</div>
