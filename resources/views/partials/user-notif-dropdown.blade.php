@php
    $notifIcon = fn ($type) => match ($type) {
        'success' => 'bi-check-circle-fill text-success',
        'danger' => 'bi-x-circle-fill text-danger',
        'warning' => 'bi-exclamation-circle-fill text-warning',
        default => 'bi-bell-fill text-primary',
    };
@endphp
<div class="dropdown">
    <button class="icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell"></i>@if($userUnreadCount > 0)<span class="dot">{{ $userUnreadCount }}</span>@endif
    </button>
    <ul class="dropdown-menu dropdown-menu-end notif-dropdown shadow-sm p-2"
        style="width: 500px; max-width: 90vw; max-height: 400px; overflow-y: auto;">
        <li>
            <h6 class="dropdown-header">Notifications</h6>
        </li>

        @forelse ($userNotifications as $n)
            <li>
                <form method="POST" action="{{ route('notifications.read', $n->NotificationID) }}" class="m-0">
                    @csrf
                    <button type="submit"
                        class="dropdown-item rounded d-flex gap-2 align-items-start w-100 text-start border-0 bg-transparent"
                        style="{{ !$n->IsRead ? 'background:var(--brand-50, #eef9f0);font-weight:600;' : '' }}">
                        <i class="bi {{ $notifIcon($n->Type) }} mt-1"></i>
                        <div>
                            <p class="mb-0 small">
                                <strong>{{ $n->Title }}</strong> — {{ $n->Message }}
                            </p>
                            <span class="text-muted" style="font-size: 0.75rem;">{{ $n->created_at->diffForHumans() }}</span>
                        </div>
                    </button>
                </form>
            </li>
            <li>
                <hr class="dropdown-divider my-1">
            </li>
        @empty
            <li>
                <p class="text-center text-muted small mb-0 py-3">No notifications yet.</p>
            </li>
        @endforelse

        <li><a class="dropdown-item text-center small" href="#" data-bs-toggle="modal"
                data-bs-target="#allNotificationsModal">View all notifications</a></li>
    </ul>
</div>
