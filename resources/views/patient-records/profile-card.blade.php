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
    $fields = [
        ['Age', $p->age_years !== null ? $p->age_years . ' years old' : null],
        ['Gender', $p->Gender ? ucfirst($p->Gender) : null],
        ['Birthdate', $p->DateOfBirth?->format('F j, Y')],
        ['Contact Number', $p->PhoneNumber],
        ['Email', $email],
        ['Nationality', $p->Nationality],
        ['Religion', $p->Religion],
        ['Occupation', $p->Occupation],
        ['Address', $p->Address, true],
    ];
    $guardian = [
        ['Parent / Guardian', $p->ParentsName],
        ['Guardian Contact', $p->ParentsContactNumber],
        ['Guardian Email', $p->ParentsEmail],
        ['Guardian Occupation', $p->ParentsOccupation],
    ];
@endphp

<style>
    .pp-card { background:#fff; border:1px solid var(--border, #e6ecef); border-radius:16px; padding:20px 22px; margin-bottom:18px; }
    .pp-head { display:flex; align-items:center; gap:16px; flex-wrap:wrap; padding-bottom:16px; margin-bottom:16px; border-bottom:1px solid var(--border, #e6ecef); }
    .pp-avatar { width:64px; height:64px; border-radius:50%; object-fit:cover; flex:0 0 auto; }
    .pp-name { font-size:1.15rem; font-weight:700; margin:0; }
    .pp-sub { color:#64748b; font-size:.85rem; margin-top:2px; }
    .pp-stats { margin-left:auto; display:flex; gap:22px; }
    .pp-stat { text-align:center; }
    .pp-stat b { display:block; font-size:1.15rem; }
    .pp-stat span { color:#64748b; font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; font-weight:700; }
    .pp-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(190px, 1fr)); gap:14px 20px; margin:0; }
    .pp-item dt { color:#64748b; font-size:.7rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase; margin:0 0 2px; }
    .pp-item dd { margin:0; font-weight:600; word-break:break-word; }
    .pp-item.pp-wide { grid-column:1 / -1; }
    .pp-title { color:var(--brand-900, #167d1d); font-size:.72rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin:18px 0 12px; }
    @media (max-width: 575.98px) { .pp-stats { margin-left:0; width:100%; justify-content:space-around; } }
</style>

<div class="pp-card">
    <div class="pp-head">
        <img class="pp-avatar" src="{{ $p->photo_url }}" alt="">
        <div>
            <h3 class="pp-name">{{ $fullName }}</h3>
            <div class="pp-sub">
                PT-{{ str_pad($p->PatientID, 4, '0', STR_PAD_LEFT) }}
                &bull; {{ $p->IsWalkIn ? 'Walk-in patient' : 'Registered patient' }}
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

    <dl class="pp-grid">
        @foreach ($fields as $f)
            <div class="pp-item {{ !empty($f[2] ?? null) ? 'pp-wide' : '' }}"><dt>{{ $f[0] }}</dt><dd>{{ $f[1] ?: '—' }}</dd></div>
        @endforeach
    </dl>

    @if ($isMinor || collect($guardian)->contains(fn ($g) => !empty($g[1])))
        <div class="pp-title">Parent / Guardian</div>
        <dl class="pp-grid">
            @foreach ($guardian as [$label, $value])
                <div class="pp-item"><dt>{{ $label }}</dt><dd>{{ $value ?: '—' }}</dd></div>
            @endforeach
        </dl>
    @endif
</div>
