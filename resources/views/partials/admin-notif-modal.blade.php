@php
    $notifIcon = fn ($type) => match ($type) {
        'success' => 'bi-check-lg',
        'danger' => 'bi-x-lg',
        'warning' => 'bi-exclamation-lg',
        default => 'bi-bell-fill',
    };
    $notifTone = fn ($type) => in_array($type, ['success', 'danger', 'warning']) ? $type : 'primary';
    $showAllTab = (bool) $adminNotifDate;
@endphp
<div class="modal fade {{ $adminNotifDate ? 'show' : '' }}" id="allNotificationsModal" tabindex="-1"
    aria-labelledby="allNotificationsLabel" aria-hidden="{{ $adminNotifDate ? 'false' : 'true' }}"
    style="{{ $adminNotifDate ? 'display:block;' : '' }}">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content notif-modal">

            <div class="modal-header notif-modal-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="notif-modal-icon"><i class="bi bi-bell-fill"></i></span>
                    <h5 class="modal-title mb-0" id="allNotificationsLabel">Notifications</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body notif-modal-body">

                <ul class="nav nav-pills notif-tabs mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ !$showAllTab ? 'active' : '' }}" data-bs-toggle="pill"
                            data-bs-target="#adminLatestNotifPane" type="button" role="tab">Latest</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $showAllTab ? 'active' : '' }}" data-bs-toggle="pill"
                            data-bs-target="#adminAllNotifPane" type="button" role="tab">All Notifications</button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade {{ !$showAllTab ? 'show active' : '' }}" id="adminLatestNotifPane" role="tabpanel">
                        <ul class="notif-list">
                            @forelse ($adminLatestNotifications as $n)
                                @include('partials.notification-card', ['n' => $n, 'side' => 'admin'])
                            @empty
                                <li class="text-center text-muted-2 py-4">No notifications yet.</li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="tab-pane fade {{ $showAllTab ? 'show active' : '' }}" id="adminAllNotifPane" role="tabpanel">
                        <form method="GET" action="{{ url()->current() }}" class="notif-filter-bar mb-3 d-flex align-items-center flex-wrap gap-2">
                            <span class="small text-muted-2">Select Date:</span>
                            <input type="date" name="notif_date" class="form-control" style="width:auto;" value="{{ $adminNotifDate }}">
                            <button type="submit" class="notif-pill" style="cursor:pointer;">Filter</button>
                            @if ($adminNotifDate)
                                <a href="{{ url()->current() }}" class="notif-pill">Clear</a>
                            @endif
                        </form>

                        <ul class="notif-list">
                            @forelse ($adminAllNotifications as $n)
                                @include('partials.notification-card', ['n' => $n, 'side' => 'admin'])
                            @empty
                                <li class="text-center text-muted-2 py-4">No notifications found.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('partials.notification-detail-modal')
