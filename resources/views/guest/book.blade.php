<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Book an Appointment • Dental Clinic</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
  <link rel="stylesheet" href="{{ asset('css/guest-booking.css') }}">
  <link rel="stylesheet" href="{{ asset('css/medical-history.css') }}">
  <link rel="stylesheet" href="{{ asset('css/modals.css') }}">
  <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
</head>

<body class="auth-page">
  @include('login_signup.partials.auth-navbar')

  <main class="auth-main">
    <div class="auth-card signup-card">
      <div class="text-center mb-3">
        <div class="auth-emblem"><img src="/images/puspus_logo.png" alt="Pus-Pus Britanico Dental Clinic"></div>
        <h2 class="auth-title mb-1">Book an Appointment</h2>
        <p class="auth-subtitle mb-0">No account needed — we'll confirm your email with a quick code.</p>
      </div>

      @include('partials.flash-toasts', ['topOffset' => '20px', 'suppressErrors' => true])

      {{-- Steps --}}
      @php
        $stepIndex = ['email' => 1, 'code' => 2, 'details' => 3][$step];
        $stepLabels = ['Your email', 'Verify code', 'Patient details'];
      @endphp
      <ol class="gb-steps" aria-label="Step {{ $stepIndex }} of 3: {{ $stepLabels[$stepIndex - 1] }}">
        @foreach ($stepLabels as $i => $stepLabel)
          <li class="{{ $i + 1 < $stepIndex ? 'is-done' : ($i + 1 === $stepIndex ? 'is-current' : '') }}">
            <span class="gb-step-num">@if ($i + 1 < $stepIndex)<i class="bi bi-check-lg"></i>@else{{ $i + 1 }}@endif</span>
            <span class="gb-step-label">{{ $stepLabel }}</span>
          </li>
        @endforeach
      </ol>
      {{-- Phones: the pills show numbers only; the current step is spelled out here. --}}
      <div class="gb-step-caption">Step {{ $stepIndex }} of 3 · <strong>{{ $stepLabels[$stepIndex - 1] }}</strong></div>

      {{-- The slot they picked on the calendar --}}
      <div class="gb-summary">
        <div class="gb-summary-head">
          <span><i class="bi bi-calendar2-check me-1"></i> Your appointment</span>
          <form method="POST" action="{{ route('guestBooking.cancel') }}" class="m-0">@csrf
            <button type="submit" class="btn btn-link btn-sm p-0 gb-change">Change</button>
          </form>
        </div>
        <div class="gb-summary-grid">
          <div><div class="gb-k">Date</div><div class="gb-v">{{ $summary['date'] }}</div></div>
          <div><div class="gb-k">Time</div><div class="gb-v">{{ $summary['time'] }} · {{ $summary['duration'] }}</div></div>
          <div><div class="gb-k">Dentist</div><div class="gb-v">{{ $summary['dentist'] }}</div></div>
          <div><div class="gb-k">Service(s)</div><div class="gb-v">{{ $summary['services'] }}</div></div>
        </div>
        @if ($summary['notes'] !== '' || $summary['photos'])
          <div class="gb-summary-note small text-muted-2">
            @if ($summary['notes'] !== '')<i class="bi bi-journal-text me-1"></i>{{ \Illuminate\Support\Str::limit($summary['notes'], 140) }}@endif
            @if ($summary['photos'])<span class="ms-2"><i class="bi bi-images me-1"></i>{{ $summary['photos'] }} photo{{ $summary['photos'] === 1 ? '' : 's' }}</span>@endif
          </div>
        @endif
      </div>

      {{-- ============ STEP 1: EMAIL ============ --}}
      @if ($step === 'email')
        <form method="POST" action="{{ route('guestBooking.email') }}" class="gb-panel">
          @csrf
          <div class="section-label">Your email</div>
          <p class="small text-muted-2 mb-2">We'll send a 6-digit code to confirm it's yours. Every update about this appointment — approval, reminders, changes — goes to this email.</p>
          <div class="row g-3 align-items-end">
            @include('guest.partials.field', ['f' => ['name' => 'email', 'label' => 'Email address', 'icon' => 'bi-envelope', 'type' => 'email', 'placeholder' => 'you@example.com', 'required' => true, 'col' => 'col-md-8', 'attrs' => 'autocomplete="email"', 'value' => $verifiedEmail ?? '']])
            <div class="col-md-4">
              @if ($verifiedEmail)
                <button type="submit" class="btn btn-brand w-100"><i class="bi bi-arrow-right me-1"></i> Continue</button>
              @else
                <button type="submit" class="btn btn-brand w-100"><i class="bi bi-send me-1"></i> Send code</button>
              @endif
            </div>
          </div>
          @if ($verifiedEmail)
            <p class="small text-muted-2 mt-3 mb-0">This email is already verified — keep it to continue without a new code, or enter a different one.</p>
          @else
            <p class="small text-muted-2 mt-3 mb-0">If you're booking for your child or someone else, use <strong>your</strong> email — you'll add their details next.</p>
          @endif
          <div class="mt-3">
            <button type="submit" form="gbBack" class="btn btn-ghost"><i class="bi bi-arrow-left me-1"></i> Back to calendar</button>
          </div>
        </form>

      {{-- ============ STEP 2: CODE ============ --}}
      @elseif ($step === 'code')
        <div class="gb-panel">
          <div class="section-label">Enter the code</div>
          <p class="small text-muted-2 mb-2">We sent a 6-digit code to <strong>{{ $pendingEmail }}</strong>. It expires in 5 minutes.</p>
          <form method="POST" action="{{ route('guestBooking.verify') }}">
            @csrf
            <div class="row g-3 align-items-end">
              <div class="col-md-8">
                <label class="form-label" for="gbCode">Verification code</label>
                <input type="text" name="code" id="gbCode" class="form-control text-center gb-code" inputmode="numeric" maxlength="6"
                  pattern="[0-9]*" placeholder="••••••" autocomplete="one-time-code" required autofocus>
              </div>
              <div class="col-md-4">
                <button type="submit" class="btn btn-brand w-100"><i class="bi bi-shield-check me-1"></i> Verify</button>
              </div>
            </div>
          </form>
          <div class="d-flex flex-wrap gap-3 mt-3 small">
            <form method="POST" action="{{ route('guestBooking.resend') }}" class="m-0">@csrf
              <button type="submit" class="btn btn-link btn-sm p-0" id="gbResend" data-retry="{{ $retryAfter }}" {{ $retryAfter ? 'disabled' : '' }}>
                Resend code<span class="gb-retry">{{ $retryAfter ? " ({$retryAfter}s)" : '' }}</span>
              </button>
            </form>
            <form method="POST" action="{{ route('guestBooking.changeEmail') }}" class="m-0">@csrf
              <button type="submit" class="btn btn-link btn-sm p-0">Use a different email</button>
            </form>
          </div>
          <div class="mt-3">
            <button type="submit" form="gbBack" class="btn btn-ghost"><i class="bi bi-arrow-left me-1"></i> Back</button>
          </div>
        </div>

      {{-- ============ STEP 3: WHO + DETAILS ============ --}}
      @else
        @php
          $choice = old('patient_choice', $people->isEmpty() ? 'self' : '');
          $needsBookerFields = !$selfRecord;
        @endphp
        <form method="POST" action="{{ route('guestBooking.confirm') }}" class="gb-panel">
          @csrf
          <div class="gb-verified mb-3">
            <i class="bi bi-patch-check-fill"></i> <span>{{ $verifiedEmail }}</span>
            <button type="submit" form="gbChangeEmail" class="btn btn-link btn-sm p-0 ms-auto">Change</button>
          </div>

          @php $peopleFull = $people->count() >= $maxPeople; @endphp
          <div class="section-label">Who is this appointment for?</div>
          <div class="who-options" data-who>
            @foreach ($people as $person)
              @php $isSelf = !$person->isDependentOf($verifiedEmail); @endphp
              <label class="who-option">
                <input type="radio" name="patient_choice" value="{{ $person->PatientID }}" data-kind="{{ $isSelf ? 'existing-self' : 'existing-dep' }} p{{ $person->PatientID }}"
                  data-booker-name="{{ $person->ParentsName }}" data-booker-phone="{{ $person->ParentsContactNumber }}"
                  {{ (string) $choice === (string) $person->PatientID ? 'checked' : '' }}>
                <span class="who-main">{{ $person->full_name }}</span>
                <span class="who-sub">
                  {{ $isSelf ? 'You' : ($person->Relationship ?: 'Booked by you') }}@if ($person->age_years !== null) · {{ $person->age_years }} yrs @endif
                </span>
                @unless ($isSelf)
                  <button type="button" class="who-remove" title="Remove {{ $person->full_name }}" aria-label="Remove {{ $person->full_name }}"
                    data-remove-url="{{ route('guestBooking.removePerson', $person->PatientID) }}" data-remove-name="{{ $person->full_name }}">
                    <i class="bi bi-x-lg"></i>
                  </button>
                @endunless
              </label>
            @endforeach
            @unless ($selfRecord || $peopleFull)
              <label class="who-option">
                <input type="radio" name="patient_choice" value="self" data-kind="self" {{ $choice === 'self' ? 'checked' : '' }}>
                <span class="who-main"><i class="bi bi-person me-1"></i> Myself</span>
                <span class="who-sub">18 years old and above</span>
              </label>
            @endunless
            @unless ($peopleFull)
              <label class="who-option">
                <input type="radio" name="patient_choice" value="other" data-kind="other" {{ $choice === 'other' ? 'checked' : '' }}>
                <span class="who-main"><i class="bi bi-people me-1"></i> Someone else</span>
                <span class="who-sub">My child, parent, spouse…</span>
              </label>
            @endunless
          </div>
          @if ($peopleFull)
            <div class="who-limit"><i class="bi bi-info-circle me-1"></i> One email can keep up to {{ $maxPeople }} people. To book for someone new, remove a person with the <i class="bi bi-x-lg"></i> on their card.</div>
          @endif
          @error('patient_choice') <div class="field-error mb-2"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror

          {{-- Myself — the same details as signing up --}}
          <div data-show-for="self" hidden>
            <div class="section-label mt-3">Your personal information</div>
            <div class="row g-3 mb-3">
              @include('guest.partials.field', ['f' => ['name' => 'last_name', 'label' => 'Last name', 'icon' => 'bi-person', 'placeholder' => 'Last name', 'required' => true, 'col' => 'col-md-4']])
              @include('guest.partials.field', ['f' => ['name' => 'first_name', 'label' => 'First name', 'icon' => 'bi-person', 'placeholder' => 'First name', 'required' => true, 'col' => 'col-md-4']])
              @include('guest.partials.field', ['f' => ['name' => 'middle_name', 'label' => 'Middle name', 'icon' => 'bi-person', 'placeholder' => 'Middle name', 'optional' => true, 'col' => 'col-md-4']])
              @include('guest.partials.field', ['f' => ['name' => 'birthdate', 'label' => 'Birthdate', 'icon' => 'bi-calendar-event', 'type' => 'date', 'required' => true, 'col' => 'col-md-4',
                'attrs' => 'max="' . $adultCutoff . '" data-age-target="#selfAge"']])
              <div class="col-md-2">
                <label class="form-label">Age</label>
                <div class="input-icon"><i class="bi bi-person-vcard"></i><input type="text" id="selfAge" class="form-control" placeholder="—" disabled /></div>
              </div>
              <div class="col-md-3">
                <label class="form-label" for="f_gender">Gender</label>
                <div class="input-icon @error('gender') has-error @enderror">
                  <select name="gender" id="f_gender" class="form-select" required data-req="1">
                    <option value="" disabled {{ old('gender') ? '' : 'selected' }}>Select</option>
                    @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $gv => $gl)
                      <option value="{{ $gv }}" {{ old('gender') === $gv ? 'selected' : '' }}>{{ $gl }}</option>
                    @endforeach
                  </select>
                </div>
                @error('gender') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
              </div>
              @include('guest.partials.field', ['f' => ['name' => 'nationality', 'label' => 'Nationality', 'icon' => 'bi-flag', 'placeholder' => 'Filipino', 'value' => 'Filipino', 'required' => true, 'col' => 'col-md-3']])
              @include('guest.partials.field', ['f' => ['name' => 'religion', 'label' => 'Religion', 'icon' => 'bi-book', 'placeholder' => 'Catholic', 'optional' => true, 'col' => 'col-md-4']])
              @include('guest.partials.field', ['f' => ['name' => 'occupation', 'label' => 'Occupation', 'icon' => 'bi-briefcase', 'placeholder' => 'Occupation', 'optional' => true, 'col' => 'col-md-4']])
              @include('guest.partials.field', ['f' => ['name' => 'phone', 'label' => 'Cell/Mobile number', 'icon' => 'bi-telephone', 'type' => 'tel', 'placeholder' => '09XXXXXXXXX', 'required' => true, 'col' => 'col-md-4',
                'attrs' => 'inputmode="numeric" pattern="[0-9]{11}" maxlength="11" data-digits="1" title="Enter an 11-digit mobile number"']])
            </div>
            <p class="small text-muted-2 mt-n2 mb-3">You must be 18 or older to book for yourself. Under 18? Ask a parent or guardian to book for you with their email.</p>

            <div class="section-label">Home address</div>
            <div class="row g-3 mb-3">
              @include('guest.partials.field', ['f' => ['name' => 'addr_street', 'label' => 'Street / House No.', 'icon' => 'bi-signpost-2', 'placeholder' => '123 Sample St.', 'optional' => true]])
              @include('guest.partials.field', ['f' => ['name' => 'addr_barangay', 'label' => 'Barangay', 'icon' => 'bi-geo', 'placeholder' => 'Barangay', 'optional' => true]])
              @include('guest.partials.field', ['f' => ['name' => 'addr_city', 'label' => 'City / Municipality', 'icon' => 'bi-buildings', 'placeholder' => 'City / Municipality', 'required' => true]])
              @include('guest.partials.field', ['f' => ['name' => 'addr_province', 'label' => 'Province', 'icon' => 'bi-map', 'placeholder' => 'Province', 'required' => true]])
            </div>

            @include('partials.medical-history-step', ['mhs' => ['name' => 'mh_self', 'id' => 'self', 'latest' => null, 'whose' => 'your']])
          </div>

          {{-- Someone already on file under this email: confirm or update their medical history --}}
          @foreach ($people as $person)
            <div data-show-for="p{{ $person->PatientID }}" hidden>
              @include('partials.medical-history-step', ['mhs' => [
                'name' => 'mh_p' . $person->PatientID,
                'id' => 'p' . $person->PatientID,
                'latest' => $person->latestMedicalHistory,
                'whose' => $person->isDependentOf($verifiedEmail) ? $person->full_name . "'s" : 'your',
              ]])
            </div>
          @endforeach

          {{-- Who's booking — only asked when this email has no patient record of its own yet --}}
          @if ($needsBookerFields)
            <div data-show-for="other,existing-dep" hidden>
              <div class="section-label mt-3">Your details <span class="text-muted-2 fw-normal">(the person booking)</span></div>
              <div class="row g-3 mb-3">
                @include('guest.partials.field', ['f' => ['name' => 'booker_name', 'label' => 'Your full name', 'icon' => 'bi-person-badge', 'placeholder' => 'Juan Dela Cruz', 'required' => true]])
                @include('guest.partials.field', ['f' => ['name' => 'booker_phone', 'label' => 'Your mobile number', 'icon' => 'bi-telephone', 'type' => 'tel', 'placeholder' => '09XXXXXXXXX', 'required' => true,
                  'attrs' => 'inputmode="numeric" pattern="[0-9]{11}" maxlength="11" data-digits="1" title="Enter an 11-digit mobile number"']])
              </div>
            </div>
          @endif

          {{-- Someone else --}}
          <div data-show-for="other" hidden>
            <div class="section-label mt-3">Patient's information</div>
            @include('guest.partials.other-fields', ['o' => ['id' => '', 'sameAddress' => (bool) $selfRecord]])
            @include('partials.medical-history-step', ['mhs' => ['name' => 'mh_other', 'id' => 'other', 'latest' => null, 'whose' => "the patient's"]])
          </div>

          <div class="form-check mt-2 mb-3">
            <input type="checkbox" class="form-check-input" name="agree_terms" id="agreeTerms" value="1" {{ old('agree_terms') ? 'checked' : '' }} required>
            <label class="form-check-label small text-muted-2" for="agreeTerms">
              I agree to the
              <a href="#" data-bs-toggle="modal" data-bs-target="#privacyPolicyModal">Privacy Policy</a> and
              <a href="#" data-bs-toggle="modal" data-bs-target="#legalTermsModal">Terms</a>.
              The clinic will review this request and email me once it's approved.
            </label>
            @error('agree_terms') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
          </div>

          <div class="d-flex gap-2">
            <button type="submit" form="gbBack" class="btn btn-ghost" formnovalidate><i class="bi bi-arrow-left me-1"></i> Back</button>
            <button type="submit" class="btn btn-brand flex-grow-1"><i class="bi bi-check2-circle me-1"></i> Request Appointment</button>
          </div>
        </form>
        <form method="POST" action="{{ route('guestBooking.changeEmail') }}" id="gbChangeEmail" class="d-none">@csrf</form>
        <form method="POST" action="" id="gbRemovePerson" class="d-none">@csrf</form>
      @endif
      <form method="POST" action="{{ route('guestBooking.back') }}" id="gbBack" class="d-none">@csrf</form>

      <p class="text-center small text-muted-2 mt-4 mb-0">
        Have an account? <a href="{{ route('login') }}">Log in</a> to book from your Patient Portal.
      </p>
    </div>
  </main>

  <div class="modal fade" id="privacyPolicyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-semibold">Privacy Policy</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="max-height:60vh; overflow-y:auto; white-space:pre-wrap;">{{ $privacyPolicy ?: 'This clinic has not published a privacy policy yet.' }}</div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="legalTermsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-semibold">Legal Terms</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="max-height:60vh; overflow-y:auto; white-space:pre-wrap;">{{ $legalTerms ?: 'This clinic has not published its legal terms yet.' }}</div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/booking-forms.js') }}"></script>
  @include('partials.confirm-action-modal')
  <script>
    // "×" on a saved person: confirm, then remove them from this email.
    document.querySelectorAll('.who-remove').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation(); // don't pick the radio in the card
        window.appConfirm({
          title: 'Remove ' + btn.dataset.removeName + '?',
          message: btn.dataset.removeName + ' will no longer show up under this email. Their records stay with the clinic, and you can add them again later with "Someone else".',
          confirmLabel: 'Yes, remove',
          confirmClass: 'btn-pill-archive',
        }).then(function (ok) {
          if (!ok) return;
          var form = document.getElementById('gbRemovePerson');
          form.action = btn.dataset.removeUrl;
          form.submit();
        });
      });
    });
  </script>
  <script>
    // Resend countdown.
    (function () {
      var btn = document.getElementById('gbResend');
      if (!btn) return;
      var left = parseInt(btn.dataset.retry || '0', 10);
      var label = btn.querySelector('.gb-retry');
      if (!left) return;
      var t = setInterval(function () {
        left--;
        label.textContent = left > 0 ? ' (' + left + 's)' : '';
        if (left <= 0) { btn.disabled = false; clearInterval(t); }
      }, 1000);
    })();
  </script>
</body>

</html>
