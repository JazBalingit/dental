{{--
    What the patient sent with their booking: their note to the dentist and
    any photos (thumbnails open full size in a new tab, served by the
    access-checked attachment route).
    Usage: @include('partials.appointment-photos', ['appointment' => $appt, 'audience' => 'staff'|'patient'])
--}}
@php
    $photos = $appointment->attachments ?? collect();
    $note = trim((string) $appointment->PatientNotes);
    $audience = $audience ?? 'staff';
    $isPatient = $audience === 'patient';
@endphp
<div class="appt-photos mb-3">
    <div class="d-flex align-items-center gap-2 mb-1" style="font-weight:700;font-size:.82rem;color:#14532d;">
        <i class="bi bi-clipboard2-heart"></i> {{ $isPatient ? 'What you sent your dentist' : 'From the patient' }}
    </div>
    <div style="font-size:.78rem;color:#64748b;" class="mb-2">
        {{ $isPatient
            ? 'The note and photos you added when booking, so your dentist can prepare.'
            : 'The patient\'s note and photos describing what they need, so you can prepare for this visit.' }}
    </div>

    {{-- Note --}}
    <div style="font-size:.74rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.03em;" class="mb-1">
        <i class="bi bi-journal-text me-1"></i> Notes
    </div>
    @if ($note !== '')
        <div class="mb-2" style="white-space:pre-line;font-size:.86rem;color:#1e293b;background:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid #167d1d;border-radius:.55rem;padding:.55rem .75rem;">{{ $note }}</div>
    @else
        <div class="mb-2" style="font-size:.82rem;color:#64748b;font-style:italic;">No note was written.</div>
    @endif

    {{-- Photos --}}
    <div style="font-size:.74rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.03em;" class="mb-1">
        <i class="bi bi-images me-1"></i> Photos ({{ $photos->count() }})
    </div>
    @if ($photos->isEmpty())
        <div style="font-size:.82rem;color:#64748b;font-style:italic;">No photos were attached.</div>
    @else
        <div style="font-size:.76rem;color:#64748b;" class="mb-2">Click a photo to view it full size.</div>
        <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
            @foreach ($photos as $photo)
                <a href="{{ $photo->url }}" target="_blank" rel="noopener" title="{{ $photo->FileName }}"
                    style="display:block;width:96px;height:96px;border-radius:.6rem;overflow:hidden;border:1px solid #e5e9e6;background:#f1f5f9;">
                    <img src="{{ $photo->url }}" alt="{{ $photo->FileName }}" loading="lazy" style="width:100%;height:100%;object-fit:cover;display:block;">
                </a>
            @endforeach
        </div>
    @endif
</div>
