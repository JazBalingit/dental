{{--
  Static (read-only) patient profile shown at the top of the Patient Record
  History page. Editing still happens through the Edit modal on the Patient
  Records list.

  Expects: $patient (PatientInfo with userAccount + appointments_max_appointmentdate).
--}}
@php
    $p = $patient;
    $fullName = trim($p->FirstName . ' ' . ($p->MiddleName ? $p->MiddleName . ' ' : '') . $p->LastName);
    $email = $p->userAccount?->Email ?? $p->Email;
    $recordCount = $p->records()->count();
    $lastVisit = $p->records()->max('VisitDate');
    $isMinor = $p->age_years !== null && $p->age_years < 18;

    // [label, value, icon, wide?] — grouped so each row holds 3–4 tiles.
    $sections = [
        'Personal Information' => [
            ['Age', $p->age_years !== null ? $p->age_years . ' years old' : null, 'bi-hourglass-split'],
            ['Gender', $p->Gender ? ucfirst($p->Gender) : null, 'bi-gender-ambiguous'],
            ['Birthdate', $p->DateOfBirth?->format('F j, Y'), 'bi-calendar-heart'],
            ['Nationality', $p->Nationality, 'bi-flag'],
            ['Religion', $p->Religion, 'bi-book'],
            ['Occupation', $p->Occupation, 'bi-briefcase'],
        ],
        'Contact Details' => [
            ['Contact Number', $p->PhoneNumber, 'bi-telephone'],
            ['Email', $email, 'bi-envelope'],
            ['Address', $p->Address, 'bi-geo-alt', true],
        ],
    ];
    if ($isMinor || collect([$p->ParentsName, $p->ParentsContactNumber, $p->ParentsEmail, $p->ParentsOccupation])->filter()->isNotEmpty()) {
        $sections['Parent / Guardian'] = [
            ['Name', $p->ParentsName, 'bi-person-heart'],
            ['Contact Number', $p->ParentsContactNumber, 'bi-telephone'],
            ['Email', $p->ParentsEmail, 'bi-envelope'],
            ['Occupation', $p->ParentsOccupation, 'bi-briefcase'],
        ];
    }
    $sectionIcons = ['Personal Information' => 'bi-person-vcard', 'Contact Details' => 'bi-chat-dots', 'Parent / Guardian' => 'bi-people'];
@endphp

<style>
    .pp-card { background:#fff; border:1px solid var(--border, #e6ecef); border-radius:16px; padding:20px 22px; margin-bottom:18px; }
    .pp-head { display:flex; align-items:center; gap:16px; flex-wrap:wrap; padding-bottom:16px; margin-bottom:6px; border-bottom:1px solid var(--border, #e6ecef); }
    .pp-avatar { width:64px; height:64px; border-radius:50%; object-fit:cover; flex:0 0 auto; box-shadow:0 0 0 3px #eaf8ec; }
    .pp-name { font-size:1.15rem; font-weight:700; margin:0; }
    .pp-sub { color:#64748b; font-size:.85rem; margin-top:2px; }
    .pp-stats { margin-left:auto; display:flex; gap:10px; }
    .pp-stat { text-align:center; background:#f6faf7; border:1px solid #e3efe6; border-radius:12px; padding:8px 16px; min-width:110px; }
    .pp-stat b { display:block; font-size:1.1rem; color:#0f172a; }
    .pp-stat span { color:#64748b; font-size:.68rem; text-transform:uppercase; letter-spacing:.06em; font-weight:700; }

    .pp-title { display:flex; align-items:center; gap:8px; color:var(--brand-900, #14532d); font-size:.74rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin:18px 0 10px; }
    .pp-title::after { content:''; flex:1; height:1px; background:var(--border, #e6ecef); }
    .pp-grid { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:10px; margin:0; }
    .pp-item { display:flex; align-items:flex-start; gap:10px; background:#fafcfb; border:1px solid #edf2ee; border-radius:12px; padding:10px 12px; min-width:0; }
    .pp-item.pp-wide { grid-column:span 2; }
    .pp-icon { flex:none; width:32px; height:32px; border-radius:9px; background:#eaf8ec; color:var(--brand-700, #167d1d); display:flex; align-items:center; justify-content:center; font-size:.95rem; }
    .pp-item dt { color:#64748b; font-size:.68rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; margin:0 0 1px; }
    .pp-item dd { margin:0; font-weight:600; color:#0f172a; overflow-wrap:anywhere; }
    .pp-item dd.is-empty { color:#a0aec0; font-weight:500; font-style:italic; }

    @media (max-width: 991.98px) { .pp-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 575.98px) {
        .pp-grid { grid-template-columns:minmax(0, 1fr); }
        .pp-item.pp-wide { grid-column:auto; }
        .pp-stats { margin-left:0; width:100%; }
        .pp-stat { flex:1; }
    }
</style>

<div class="pp-card">
    <div class="pp-head">
        <img class="pp-avatar" src="{{ $p->photo_url }}" alt="">
        <div>
            <h3 class="pp-name">{{ $fullName }}</h3>
            <div class="pp-sub">
                PT-{{ str_pad($p->PatientID, 4, '0', STR_PAD_LEFT) }}
                &bull; {{ $p->patient_type }} patient
                @if ($p->is_inactive)
                    <span class="pill pill-warning ms-1" title="No appointment in the last 6 months">Inactive</span>
                @else
                    <span class="pill pill-success ms-1">Active</span>
                @endif
            </div>
        </div>
        <div class="pp-stats">
            <div class="pp-stat"><b>{{ $recordCount }}</b><span>Total records</span></div>
            <div class="pp-stat"><b>{{ $lastVisit ? \Carbon\Carbon::parse($lastVisit)->format('M j, Y') : '—' }}</b><span>Last visit</span></div>
        </div>
    </div>

    @foreach ($sections as $title => $items)
        <div class="pp-title"><i class="bi {{ $sectionIcons[$title] ?? 'bi-info-circle' }}"></i> {{ $title }}</div>
        <dl class="pp-grid">
            @foreach ($items as $f)
                <div class="pp-item {{ !empty($f[3] ?? null) ? 'pp-wide' : '' }}">
                    <span class="pp-icon"><i class="bi {{ $f[2] }}"></i></span>
                    <div style="min-width:0;">
                        <dt>{{ $f[0] }}</dt>
                        <dd class="{{ $f[1] ? '' : 'is-empty' }}">{{ $f[1] ?: 'Not provided' }}</dd>
                    </div>
                </div>
            @endforeach
        </dl>
    @endforeach
</div>
