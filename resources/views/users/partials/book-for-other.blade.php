{{--
    Patient Portal → Book for Someone Else: rendered inside the calendar's
    confirm form (one per day). Pick someone you've booked for before, or
    add someone new. Expects $bookDependents and $dayKey.
--}}
@php $deps = $bookDependents ?? collect(); @endphp
<div class="book-photos mb-3">
    <div class="fw-semibold small mb-2"><i class="bi bi-people me-1" style="color:var(--brand-700);"></i> Who is this appointment for?</div>
    <div class="who-options" data-who>
        @foreach ($deps as $dep)
            <label class="who-option">
                <input type="radio" name="dependent_id" value="{{ $dep->PatientID }}" data-kind="existing p{{ $dep->PatientID }}" required
                    {{ (string) old('dependent_id') === (string) $dep->PatientID ? 'checked' : '' }}>
                <span class="who-main">{{ $dep->full_name }}</span>
                <span class="who-sub">{{ $dep->Relationship ?: 'Booked by you' }}@if ($dep->age_years !== null) · {{ $dep->age_years }} yrs @endif</span>
            </label>
        @endforeach
        <label class="who-option">
            <input type="radio" name="dependent_id" value="new" data-kind="other" required
                {{ old('dependent_id', $deps->isEmpty() ? 'new' : '') === 'new' ? 'checked' : '' }}>
            <span class="who-main"><i class="bi bi-person-plus me-1"></i> Someone new</span>
            <span class="who-sub">Add their details</span>
        </label>
    </div>

    {{-- Someone booked for before: confirm or update their medical history --}}
    @foreach ($deps as $dep)
        <div data-show-for="p{{ $dep->PatientID }}" hidden>
            @include('partials.medical-history-step', ['mhs' => [
                'name' => 'mh_p' . $dep->PatientID,
                'id' => 'p' . $dep->PatientID . 'd' . $dayKey,
                'latest' => $dep->latestMedicalHistory,
                'whose' => $dep->full_name . "'s",
            ]])
        </div>
    @endforeach

    <div data-show-for="other" hidden class="mt-3">
        @include('guest.partials.other-fields', ['o' => ['id' => $dayKey, 'sameAddress' => true]])
        @include('partials.medical-history-step', ['mhs' => ['name' => 'mh_new', 'id' => 'new' . $dayKey, 'latest' => null, 'whose' => "the patient's"]])
    </div>
</div>
