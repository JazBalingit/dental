{{--
    Under an appointment's status pill: shows a staff proposal that's still
    waiting for the patient, with a Withdraw option. Expects $appt (with
    pendingReschedule loaded).
--}}
@if ($appt->pendingReschedule)
    @php $rs = $appt->pendingReschedule; @endphp
    <div class="mt-1" style="font-size:.72rem;line-height:1.3;">
        <span class="pill pill-warning" title="Waiting for the patient until {{ $rs->ExpiresAt->format('M j, g:i A') }}">
            <i class="bi bi-hourglass-split"></i> New time proposed
        </span>
        <div class="text-muted-2 mt-1">{{ $rs->when_label }}</div>
        <form method="POST" action="{{ route('reschedules.withdraw', $rs->RescheduleID) }}" class="d-inline"
            data-confirm-title="Withdraw the proposed time?"
            data-confirm-message="The proposed time ({{ $rs->when_label }}) will be released and the appointment keeps its original time. The patient will be notified."
            data-confirm-label="Withdraw" data-confirm-class="btn-pill-cancel">
            @csrf
            <button type="submit" class="btn btn-link p-0 small" style="font-size:.72rem;">Withdraw</button>
        </form>
    </div>
@endif
