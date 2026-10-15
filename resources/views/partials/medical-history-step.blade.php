{{--
    The medical-history step of a booking. Returning patients get the form
    already filled in with their last answers — they only change what's
    different (unchanged answers don't create a new version). Everyone else
    fills it in for the first time. $mhs:
      name    field-name prefix ("mh", "mh_p12"…)
      id      unique suffix
      latest  the patient's current MedicalHistory, or null
      whose   heading subject — "your" or a name ("Miguel Santos's")
      staff   staff mode (clinic-only fields, no certify box)
--}}
@php
    $sid = $mhs['id'] ?? '';
    $latest = $mhs['latest'] ?? null;
    $whose = $mhs['whose'] ?? 'your';
    $n = $mhs['name'] ?? 'mh';
@endphp
<div class="mh-step" data-mh-step>
    <div class="section-label mt-3"><i class="bi bi-heart-pulse me-1"></i> Medical history</div>
    <input type="hidden" name="{{ $n }}_status" value="{{ $latest ? 'update' : 'new' }}">

    @if ($latest)
        <div class="mh-onfile mb-2">
            <div class="mh-onfile-head">
                <span>Your last answers are filled in{{ $whose === 'your' ? '' : ' for ' . preg_replace("/'s$/", '', $whose) }}</span>
                <span class="mh-onfile-date">Last updated {{ $latest->created_at->format('M j, Y') }}</span>
            </div>
            <div class="small text-muted-2">Check them and change anything that's different — e.g. a condition that has healed or a new medication.</div>
        </div>
    @else
        <p class="small text-muted-2 mb-2">
            So the dentist can treat {{ $whose === 'your' ? 'you' : 'them' }} safely. You only fill this in once — next time your answers will already be filled in.
        </p>
    @endif

    <div data-mh-form>
        @include('partials.medical-history-form', ['mhf' => ['name' => $n, 'id' => $sid, 'values' => $latest?->form_values ?? [], 'staff' => !empty($mhs['staff'])]])
    </div>
</div>
