{{--
    "Why are you cancelling?" fields for a patient's own cancel modal
    (patient portal + guest manage link). Goes inside the modal's <form>.
    Read back server-side with Appointment::cancelReasonFrom($request).

    Optional: ['id' => '…'] suffix to keep ids unique when a page renders
    several cancel modals.

    Picking "Other" makes the details box required; any other reason
    leaves it optional.
--}}
@php $rid = 'cancelReason' . ($id ?? ''); @endphp
<div class="cancel-reason-fields mt-3">
  <label class="form-label small fw-semibold" for="{{ $rid }}">Reason for cancelling</label>
  <select class="form-select js-cancel-reason" id="{{ $rid }}" name="reason" required>
    <option value="">Choose a reason…</option>
    @foreach (\App\Models\Appointment::CANCEL_REASONS as $r)
      <option value="{{ $r }}">{{ $r === 'Other' ? 'Other (please specify)' : $r }}</option>
    @endforeach
  </select>

  <label class="form-label small fw-semibold mt-3" for="{{ $rid }}Details">
    Tell us more <span class="text-muted-2 fw-normal js-cancel-optional">(optional)</span>
  </label>
  <textarea class="form-control js-cancel-details" id="{{ $rid }}Details" name="reason_details" rows="3" maxlength="500"
    placeholder="e.g. I have an exam that day"></textarea>
</div>

@once
<script>
  (function () {
    // "Other" → the details box becomes required (and loses "(optional)").
    function sync(select) {
      var wrap = select.closest('.cancel-reason-fields');
      if (!wrap) return;
      var isOther = select.value === 'Other';
      var details = wrap.querySelector('.js-cancel-details');
      details.required = isOther;
      details.placeholder = isOther ? 'Please type your reason' : 'e.g. I have an exam that day';
      wrap.querySelector('.js-cancel-optional').hidden = isOther;
    }
    document.addEventListener('change', function (e) {
      if (e.target.classList && e.target.classList.contains('js-cancel-reason')) sync(e.target);
    });
    // Start fresh each time a cancel modal opens.
    document.addEventListener('show.bs.modal', function (e) {
      e.target.querySelectorAll('.js-cancel-reason').forEach(function (select) {
        select.value = '';
        var details = select.closest('.cancel-reason-fields').querySelector('.js-cancel-details');
        if (details) details.value = '';
        sync(select);
      });
    });
  })();
</script>
@endonce
