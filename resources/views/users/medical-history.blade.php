<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/images/puspus_logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical History — Patient Portal — Pus-Pus Britanico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="/css/user_appointments.css">
    <link rel="stylesheet" href="{{ asset('css/guest-booking.css') }}">
    <link rel="stylesheet" href="{{ asset('css/medical-history.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modals.css') }}">
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
            @include('partials.patient-sidebar-nav', ['active' => 'medical'])
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
                        <h2>Medical History</h2>
                        <div class="crumbs">So the dentist can treat you safely. Keep it up to date — the clinic sees the latest version on every visit.</div>
                    </div>
                </div>

                @if ($people->count() > 1)
                    <div class="who-options mb-3" style="max-width:760px;">
                        @foreach ($people as $person)
                            <a href="{{ route('myMedicalHistory', ['patient' => $person->PatientID]) }}" class="who-option text-decoration-none {{ $person->PatientID === $patient->PatientID ? 'is-picked' : '' }}" style="padding-left:.9rem;">
                                <span class="who-main">{{ $loop->first ? 'Me' : $person->full_name }}</span>
                                <span class="who-sub">{{ $loop->first ? $person->full_name : ($person->Relationship ?: 'Booked by you') }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="section-card">
                    <div class="card-hd">
                        <div class="card-hd-left">
                            <div class="card-hd-icon"><i class="fas fa-heart-pulse"></i></div>
                            <div>
                                <h4>{{ $people->first()->PatientID === $patient->PatientID ? 'Your medical history' : $patient->full_name . "'s medical history" }}</h4>
                                <p>{{ $current ? 'Last updated ' . $current->created_at->format('F j, Y') : 'Not filled in yet' }}</p>
                            </div>
                        </div>
                        @if ($current && !$editing)
                            <a href="{{ route('myMedicalHistory', ['patient' => $patient->PatientID, 'edit' => 1]) }}" class="btn-outline" style="text-decoration:none;">
                                <i class="fas fa-pen"></i> Update
                            </a>
                        @endif
                    </div>
                    <div class="appt-body" style="display:block;">
                        @if ($editing)
                            <form method="POST" action="{{ route('myMedicalHistory.update') }}">
                                @csrf
                                <input type="hidden" name="patient_id" value="{{ $patient->PatientID }}">
                                <div data-mh-step>
                                    @if ($current)
                                        <p class="small text-muted-2 mb-2">Your previous answers are filled in — change only what's different. The old version stays on file for the clinic.</p>
                                    @endif
                                    <div data-mh-form>
                                        @include('partials.medical-history-form', ['mhf' => ['name' => 'mh', 'id' => 'portal', 'values' => $current?->form_values ?? [], 'staff' => false]])
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mt-3">
                                    @if ($current)
                                        <a href="{{ route('myMedicalHistory', ['patient' => $patient->PatientID]) }}" class="btn-sec" style="text-decoration:none;">Cancel</a>
                                    @endif
                                    <button type="submit" class="btn-prim"><i class="fas fa-check"></i> Save Medical History</button>
                                </div>
                            </form>
                        @else
                            @include('partials.medical-history-view', ['mhv' => $current, 'mhvCompact' => false])
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>

    @include('partials.user-notif-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/booking-forms.js') }}"></script>
</body>

</html>
