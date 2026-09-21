{{--
  One notification in the list. A booked / approved / completed appointment
  notification is clickable and opens that appointment's details; anything else
  keeps the old behaviour (an unread one is marked read on click).

  Expects: $n, $side ('admin'|'user'); $notifIcon / $notifTone come from the
  including modal.
--}}
@php
    $opens = $n->opensAppointmentDetails() && $n->appointment; // nothing to show if the appointment is gone
    $plainUnread = !$opens && !$n->IsRead;
@endphp
<li class="notif-card {{ $opens ? 'notif-clickable' : '' }}"
    style="{{ !$n->IsRead ? 'background:var(--brand-50, #eef9f0);' : '' }}"
    @if ($opens)
        data-notif-details-url="{{ route('notifications.details', $n->NotificationID) }}"
        data-notif-read-url="{{ $n->IsRead ? '' : route('notifications.read', $n->NotificationID) }}"
        role="button" tabindex="0" title="View appointment details"
    @endif>
    @if ($plainUnread)
        <form method="POST" action="{{ route('notifications.read', $n->NotificationID) }}" style="display:contents;">
            @csrf
            <button type="submit" style="all:unset; display:contents; cursor:pointer;">
    @endif

    <span class="notif-icon notif-{{ $notifTone($n->Type) }}"><i class="bi {{ $notifIcon($n->Type) }}"></i></span>
    <div class="notif-content">
        <p class="notif-text">
            <strong>{{ $n->Title }}</strong><br>
            {{ $n->Message }}
            @if ($n->Status)
                <br><span class="text-muted small">Status: {{ $n->Status }}</span>
            @endif
            @if ($side === 'admin' && $n->appointment)
                <br><span class="text-muted small">
                    Date: {{ $n->appointment->AppointmentDate->format('F j, Y') }}
                    &bull; Time: {{ \Carbon\Carbon::createFromFormat('H:i', $n->appointment->AppointmentTime)->format('g:i A') }}
                    @if ($n->appointment->TypeOfAppointment || $n->appointment->service)
                        &bull; Service: {{ $n->appointment->TypeOfAppointment ?: $n->appointment->service->ServiceName }}
                    @endif
                </span>
            @endif
            @if ($side === 'user' && in_array($n->Status, ['Declined', 'Cancelled']) && $n->appointment && $n->appointment->DeclineReason)
                <br><span class="text-muted small">Reason: {{ $n->appointment->DeclineReason }}</span>
            @endif
        </p>
        <div class="notif-meta"><span>{{ $n->created_at->format('M j, Y') }}</span><span class="notif-dot">•</span><span>{{ $n->created_at->diffForHumans() }}</span>
        </div>
    </div>
    <span class="notif-badge notif-{{ $notifTone($n->Type) }}">{{ $n->Status ?? ucfirst($n->Type) }}</span>
    @if ($opens)
        <i class="bi bi-chevron-right notif-chevron" aria-hidden="true"></i>
    @endif

    @if ($plainUnread)
            </button>
        </form>
    @endif
</li>
