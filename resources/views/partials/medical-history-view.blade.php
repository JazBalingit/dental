{{--
    Read-only medical history — warnings first, then every answer.
    Expects $mhv (MedicalHistory or null). Optional $mhvCompact (bool):
    warnings only, with a "full answers" toggle.
--}}
@php
    $h = $mhv ?? null;
    $compact = $mhvCompact ?? false;
    $ynLabel = fn ($v) => $v === 'yes' ? 'Yes' : ($v === 'no' ? 'No' : '—');
@endphp
@if (!$h)
    <div class="mh-alert-none"><i class="bi bi-info-circle me-1"></i> No medical history on file yet.</div>
@else
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
        @if ($h->alerts)
            <div class="mh-alerts">
                @foreach ($h->alerts as $alert)
                    <span class="mh-alert is-{{ $alert['level'] }}">{{ $alert['text'] }}</span>
                @endforeach
            </div>
        @else
            <span class="mh-alert-none"><i class="bi bi-check-circle me-1" style="color:#16a34a;"></i> No allergies or medical conditions declared.</span>
        @endif
        <span class="small text-muted-2">
            Updated {{ $h->created_at->format('M j, Y g:i A') }}{{ $h->UpdatedByName ? ' by ' . $h->UpdatedByName : '' }}{{ $h->Source === 'staff' ? ' (clinic)' : '' }}
        </span>
    </div>

    <details class="mh-full" {{ $compact ? '' : 'open' }}>
        <summary class="small fw-semibold" style="cursor:pointer;color:var(--brand-800,#167d1d);">Full answers</summary>
        <div class="mh-view mt-2">
            @foreach (\App\Models\MedicalHistory::QUESTIONS as $key => [$question, $followUp])
                @php $v = $h->answer($key); $detail = $h->answer($key . '_detail'); @endphp
                <div class="mh-view-row">
                    <div class="mh-view-k">{{ $question }}</div>
                    <div class="mh-view-v {{ $v === 'yes' && $key !== 'good_health' ? 'is-yes' : '' }}">{{ $ynLabel($v) }}{{ $detail ? ' — ' . $detail : '' }}</div>
                </div>
            @endforeach
            <div class="mh-view-row">
                <div class="mh-view-k">Allergies</div>
                <div class="mh-view-v {{ $h->allergy_list ? 'is-yes' : '' }}">{{ $h->allergy_list ? implode(', ', $h->allergy_list) : 'None' }}</div>
            </div>
            @foreach (\App\Models\MedicalHistory::WOMEN as $key => $question)
                @if ($h->answer($key))
                    <div class="mh-view-row">
                        <div class="mh-view-k">{{ $question }}</div>
                        <div class="mh-view-v {{ $h->answer($key) === 'yes' ? 'is-yes' : '' }}">{{ $ynLabel($h->answer($key)) }}</div>
                    </div>
                @endif
            @endforeach
            <div class="mh-view-row">
                <div class="mh-view-k">Medical conditions</div>
                <div class="mh-view-v {{ $h->condition_list ? 'is-yes' : '' }}">{{ $h->condition_list ? implode(', ', $h->condition_list) : 'None' }}</div>
            </div>
            @foreach (['physician_name' => 'Physician', 'physician_specialty' => 'Specialty', 'physician_office' => "Physician's office", 'physician_phone' => "Physician's number",
                'previous_dentist' => 'Previous dentist', 'last_dental_visit' => 'Last dental visit',
                'blood_type' => 'Blood type'] as $key => $label)
                @if ($h->answer($key))
                    <div class="mh-view-row">
                        <div class="mh-view-k">{{ $label }}</div>
                        <div class="mh-view-v">{{ $h->answer($key) }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    </details>
@endif
