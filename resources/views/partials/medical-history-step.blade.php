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

    The questions fold away behind a Show/Hide toggle: collapsed for a
    returning patient (answers already on file), open for a first-timer or
    after a validation error. A collapsed section with a missing required
    answer opens itself when the form is submitted (script below).
--}}
@php
    $sid = $mhs['id'] ?? '';
    $latest = $mhs['latest'] ?? null;
    $whose = $mhs['whose'] ?? 'your';
    $n = $mhs['name'] ?? 'mh';
    $mhOpen = !$latest || (isset($errors) && $errors->any());
    $mhFormId = 'mhForm' . preg_replace('/[^A-Za-z0-9_-]/', '', $n . $sid);
@endphp
<div class="mh-step" data-mh-step>
    <div class="mh-step-head mt-3">
        <div class="section-label mb-0"><i class="bi bi-heart-pulse me-1"></i> Medical history</div>
        <button type="button" class="mh-toggle" data-mh-collapse aria-controls="{{ $mhFormId }}" aria-expanded="{{ $mhOpen ? 'true' : 'false' }}">
            <span data-mh-collapse-text>{{ $mhOpen ? 'Hide questions' : 'Show questions' }}</span>
            <i class="bi bi-chevron-down"></i>
        </button>
    </div>
    <input type="hidden" name="{{ $n }}_status" value="{{ $latest ? 'update' : 'new' }}">

    @if ($latest)
        <div class="mh-onfile mb-2">
            <div class="mh-onfile-head">
                <span>Your last answers are filled in{{ $whose === 'your' ? '' : ' for ' . preg_replace("/'s$/", '', $whose) }}</span>
                <span class="mh-onfile-date">Last updated {{ $latest->created_at->format('M j, Y') }}</span>
            </div>
            <div class="small text-muted-2">Nothing changed? You can leave them as they are. Otherwise tap <strong>Show questions</strong> and update anything that's different — e.g. a condition that has healed or a new medication.</div>
        </div>
    @else
        <p class="small text-muted-2 mb-2">
            So the dentist can treat {{ $whose === 'your' ? 'you' : 'them' }} safely. You only fill this in once — next time your answers will already be filled in.
        </p>
    @endif

    {{-- Collapsing wraps [data-mh-form] rather than hiding it: booking-forms.js
         treats a hidden [data-mh-form] as "not answering" and disables it. --}}
    <div data-mh-body id="{{ $mhFormId }}" @unless ($mhOpen) hidden @endunless>
        <div data-mh-form>
            @include('partials.medical-history-form', ['mhf' => ['name' => $n, 'id' => $sid, 'values' => $latest?->form_values ?? [], 'staff' => !empty($mhs['staff'])]])
        </div>
    </div>
</div>

@once
<script>
  (function () {
    function setOpen(step, open) {
      var form = step.querySelector('[data-mh-body]');
      var btn = step.querySelector('[data-mh-collapse]');
      if (!form || !btn) return;
      form.hidden = !open;
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      btn.querySelector('[data-mh-collapse-text]').textContent = open ? 'Hide questions' : 'Show questions';
    }

    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-mh-collapse]');
      if (btn) {
        var step = btn.closest('[data-mh-step]');
        setOpen(step, btn.getAttribute('aria-expanded') !== 'true');
        return;
      }

      // Submitting with a required answer missing inside a collapsed
      // section: open it first so the browser can point at the question.
      var submit = e.target.closest('button[type="submit"], input[type="submit"], button:not([type])');
      var form = submit && (submit.form || submit.closest('form'));
      if (!form) return;
      form.querySelectorAll('[data-mh-body][hidden]').forEach(function (f) {
        if (f.querySelector(':invalid')) setOpen(f.closest('[data-mh-step]'), true);
      });
    }, true);
  })();
</script>
@endonce
