<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dental Records — Patient Portal — Pus-Pus Britanico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="/css/user_appointments.css">
    <link rel="stylesheet" href="/css/odontogram.css">
    <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
</head>

<body>
    <div class="app">
        <aside class="sidebar offcanvas position-sticky" tabindex="-1" id="sidebarOffcanvas">
            <div class="brand">
                <div><img class="logo" src="/images/puspus_logo.png" alt=""></div>
                <div>
                    <div class="name">PUS-PUS BRITANICO</div>
                    <div class="sub">PATIENT PORTAL</div>
                </div>
            </div>
            @include('partials.patient-sidebar-nav', ['active' => 'records'])
            @include('partials.patient-profile-badge')
        </aside>

        <main>
            <div class="topbar">
                <div class="left">
                    <button class="toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
                <div class="right">
                    @include('partials.user-notif-dropdown')
                </div>
            </div>

            <div class="content">
                @include('partials.flash-toasts')
                <div class="page-head">
                    <div>
                        <h2>My Dental Records</h2>
                        <div class="crumbs">Every completed visit on your file, with the dentist's chart and notes.</div>
                    </div>
                </div>

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
        </main>
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
