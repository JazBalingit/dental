<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dental Records — Pus-Pus Britanico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/css/user_appointments.css">
    <link rel="stylesheet" href="/css/odontogram.css">
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top mask-custom shadow-sm">
        <div class="container-fluid px-3 px-lg-5">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('landingPage') }}#home">
                <img class="logo" src="/images/puspus_logo.png" alt="Pus-Pus Britanico logo">
                <span class="navt ms-1" style="color:#0f7a2d;">PUS-PUS</span>
                <span class="navt ms-2" style="color:#144d25;">BRITANICO</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mx-auto text-center">
                    <li class="nav-item"><a class="nav-link navh" href="{{ route('landingPage') }}#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link navh" href="{{ route('landingPage') }}#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link navh" href="{{ route('landingPage') }}#how">How It Works</a></li>
                    <li class="nav-item"><a class="nav-link navh" href="{{ route('landingPage') }}#about">About</a></li>
                    <li class="nav-item"><a class="nav-link navh" href="{{ route('landingPage') }}#appointment">Appointment</a></li>
                    <li class="nav-item"><a class="nav-link navh" href="{{ route('landingPage') }}#contact">Contact</a></li>
                </ul>
                <ul class="navbar-nav ms-lg-3">
                    <li class="nav-item">
                        <div class="d-flex justify-content-between">
                            @include('partials.user-notif-dropdown')
                            @if (session('user_email'))
                                <div class="dropdown d-flex align-items-center">
                                    <a href="{{ route('settings', ['tab' => 'profile']) }}"
                                        class="nav-link navh d-flex align-items-center gap-2" style="padding-right:8px;">
                                        <i class="bi bi-person-circle"></i>
                                        <span>{{ session('user_email') }}</span>
                                    </a>
                                    <button class="nav-link navh border-0 bg-transparent dropdown-toggle"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                        aria-label="Account menu" style="padding-left:6px;padding-right:10px;margin-left:-4px;"></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><a class="dropdown-item small" href="{{ route('userAppointment') }}"><i class="bi bi-calendar-check me-2"></i>User Appointments</a></li>
                                        <li><a class="dropdown-item small" href="{{ route('myRecords') }}"><i class="bi bi-folder2-open me-2"></i>My Dental Records</a></li>
                                        <li><a class="dropdown-item small" href="{{ route('settings') }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                                @csrf
                                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-1"></i> Log Out</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            @else
                                <a href="{{ route('login') }}" class="nav-link navh signin-btn">Sign In</a>
                                <a href="{{ route('signup') }}" class="nav-link navh signup-btn">Sign Up</a>
                            @endif
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- PAGE HERO -->
    <div class="page-hero">
        <div class="container px-4">
            <h1>USER PROFILE</h1>
            <p class="page-hero-sub">My Dental Records</p>
        </div>
    </div>

    <!-- SUB-NAV -->
    <div class="subnav">
        <div class="container px-4">
            <div class="subnav-inner">
                <a href="{{ route('userAppointment') }}" class="subnav-link"><i class="fas fa-calendar-check"></i>Appointments</a>
                <a href="{{ route('myRecords') }}" class="subnav-link active"><i class="fas fa-folder-open"></i>My Records</a>
                <a href="{{ route('settings') }}" class="subnav-link"><i class="fas fa-gear"></i>Settings</a>
            </div>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content-wrap">

        @include('partials.flash-toasts', ['topOffset' => '100px'])

        @php
            $allRecords = $records->getCollection();
            $lastVisit = $allRecords->sortByDesc('VisitDate')->first();
        @endphp

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-ico" style="background:rgba(34,197,94,0.1);color:#22c55e"><i class="fas fa-notes-medical"></i></div>
                <div>
                    <div class="stat-val">{{ $records->total() }}</div>
                    <div class="stat-lbl">Recorded Visits</div>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-ico" style="background:rgba(15,76,122,0.08);color:#0f4c7a"><i class="fas fa-calendar-day"></i></div>
                <div>
                    <div class="stat-val">{{ $lastVisit ? $lastVisit->VisitDate->format('M j, Y') : '—' }}</div>
                    <div class="stat-lbl">Most Recent Visit</div>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-ico" style="background:rgba(59,217,101,0.1);color:#0f7a33"><i class="fas fa-tooth"></i></div>
                <div>
                    <div class="stat-val">{{ $allRecords->flatMap->odontogramTeeth->count() }}</div>
                    <div class="stat-lbl">Teeth Charted (this page)</div>
                </div>
            </div>
        </div>

        <!-- Records -->
        <div class="section-card">
            <div class="card-hd">
                <div class="card-hd-left">
                    <div class="card-hd-icon"><i class="fas fa-folder-open"></i></div>
                    <div>
                        <h4>Treatment History</h4>
                        <p>Every completed visit on your file, with the dentist's chart and notes</p>
                    </div>
                </div>
            </div>

            <div style="overflow-x:auto">
                <table class="appt-table">
                    <thead>
                        <tr>
                            <th>Visit Date</th>
                            <th>Time</th>
                            <th>Treatment</th>
                            <th>Dentist</th>
                            <th>Status</th>
                            <th class="text-end">Chart &amp; Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            @php
                                $dentistInfo = $record->appointment?->dentist?->staffInfo;
                                $dentistName = $dentistInfo
                                    ? 'Dr. ' . trim($dentistInfo->FirstName . ' ' . $dentistInfo->LastName)
                                    : '—';
                            @endphp
                            <tr>
                                <td style="font-weight:600;color:#0f4c7a">{{ $record->VisitDate->format('M j, Y') }}</td>
                                <td>{{ $record->VisitTime ? \Carbon\Carbon::createFromFormat('H:i', $record->VisitTime)->format('g:i A') : '—' }}</td>
                                <td><span class="service-tag">{{ $record->Service ?: ($record->service->ServiceName ?? '—') }}</span></td>
                                <td>{{ $dentistName }}</td>
                                <td><span class="badge-pill badge-completed">{{ $record->Status ?: 'Completed' }}</span></td>
                                <td class="text-end">
                                    <button type="button" class="btn-view-appt" data-bs-toggle="modal" data-bs-target="#recModal{{ $record->RecordID }}">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No dental records yet. They appear here after a completed visit.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="history-footer">
                <small>Showing <strong>{{ $records->count() }}</strong> of <strong>{{ $records->total() }}</strong> visits</small>
                @if ($records->lastPage() > 1)
                    <div class="pages">
                        <a href="{{ $records->previousPageUrl() ?? '#' }}"><i class="bi bi-chevron-left"></i></a>
                        @for ($i = 1; $i <= $records->lastPage(); $i++)
                            <a href="{{ $records->url($i) }}" class="{{ $records->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                        @endfor
                        <a href="{{ $records->nextPageUrl() ?? '#' }}"><i class="bi bi-chevron-right"></i></a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ===================== RECORD DETAIL MODALS ===================== --}}
    @foreach ($records as $record)
        <div class="modal fade" id="recModal{{ $record->RecordID }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-notes-medical me-2" style="color:#0f7a33"></i>Visit of {{ $record->VisitDate->format('F j, Y') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="appt-info-grid">
                            <div class="appt-info-cell"><span class="appt-info-lbl">Treatment</span><span class="appt-info-val">{{ $record->Service ?: ($record->service->ServiceName ?? '—') }}</span></div>
                            <div class="appt-info-cell"><span class="appt-info-lbl">Date</span><span class="appt-info-val">{{ $record->VisitDate->format('F j, Y') }}</span></div>
                            <div class="appt-info-cell"><span class="appt-info-lbl">Time</span><span class="appt-info-val">{{ $record->VisitTime ? \Carbon\Carbon::createFromFormat('H:i', $record->VisitTime)->format('g:i A') : '—' }}</span></div>
                            <div class="appt-info-cell"><span class="appt-info-lbl">Status</span><span class="appt-info-val"><span class="badge-pill badge-completed">{{ $record->Status ?: 'Completed' }}</span></span></div>
                        </div>

                        <div class="appt-info-divider"></div>

                        @include('partials.odontogram', ['record' => $record, 'readonly' => true])

                        <div class="odontogram-heading"><i class="bi bi-journal-text"></i> Dentist's Notes</div>
                        <div class="odontogram-detail-readvalue">{{ $record->Notes ?: 'No notes were recorded for this visit.' }}</div>
                    </div>
                    <div class="modal-footer gap-2">
                        <button class="btn-sec" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    @include('partials.user-notif-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/odontogram.js"></script>
</body>

</html>
