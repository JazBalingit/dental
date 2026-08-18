@php
    $notifIcon = fn ($type) => match ($type) {
        'success' => 'bi-check-lg',
        'danger' => 'bi-x-lg',
        'warning' => 'bi-exclamation-lg',
        default => 'bi-bell-fill',
    };
    $notifTone = fn ($type) => in_array($type, ['success', 'danger', 'warning']) ? $type : 'primary';
@endphp
<div class="modal fade {{ $userNotifDate ? 'show' : '' }}" id="allNotificationsModal" tabindex="-1"
    aria-labelledby="allNotificationsLabel" aria-hidden="{{ $userNotifDate ? 'false' : 'true' }}"
    style="{{ $userNotifDate ? 'display:block;' : '' }}">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content notif-modal">

            <div class="modal-header notif-modal-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="notif-modal-icon"><i class="bi bi-bell-fill"></i></span>
                    <h5 class="modal-title mb-0 text-light" id="allNotificationsLabel">All Notifications</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body p-3 notif-modal-body">

                <form method="GET" action="{{ url()->current() }}" class="notif-filter-bar mb-3 d-flex align-items-center flex-wrap gap-2">
                    <span class="small text-muted">Select Date:</span>
                    <input type="date" name="notif_date" class="form-control" style="width:auto;" value="{{ $userNotifDate }}">
                    <button type="submit" class="notif-pill" style="cursor:pointer;">Filter</button>
                    @if ($userNotifDate)
                        <a href="{{ url()->current() }}" class="notif-pill">All Notifications</a>
                    @endif
                </form>

                <ul class="notif-list">
                    @forelse ($userAllNotifications as $n)
                        <li class="notif-card" style="{{ !$n->IsRead ? 'background:var(--brand-50, #eef9f0);' : '' }}">
                            @unless ($n->IsRead)
                                <form method="POST" action="{{ route('notifications.read', $n->NotificationID) }}" style="display:contents;">
                                    @csrf
                                    <button type="submit" style="all:unset; display:contents; cursor:pointer;">
                            @endunless

                            <span class="notif-icon notif-{{ $notifTone($n->Type) }}"><i class="bi {{ $notifIcon($n->Type) }}"></i></span>
                            <div class="notif-content">
                                <p class="notif-text">
                                    <strong>{{ $n->Title }}</strong><br>
                                    {{ $n->Message }}
                                    @if ($n->Status)
                                        <br><span class="text-muted small">Status: {{ $n->Status }}</span>
                                    @endif
                                    @if (in_array($n->Status, ['Declined', 'Cancelled']) && $n->appointment && $n->appointment->DeclineReason)
                                        <br><span class="text-muted small">Reason: {{ $n->appointment->DeclineReason }}</span>
                                    @endif
                                </p>
                                <div class="notif-meta"><span>{{ $n->created_at->format('M j, Y') }}</span><span class="notif-dot">•</span><span>{{ $n->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <span class="notif-badge notif-{{ $notifTone($n->Type) }}">{{ $n->Status ?? ucfirst($n->Type) }}</span>

                            @unless ($n->IsRead)
                                    </button>
                                </form>
                            @endunless
                        </li>
                    @empty
                        <li class="text-center text-muted py-4">No notifications found.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
