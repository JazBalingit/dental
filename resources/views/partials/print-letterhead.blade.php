{{--
    Clinic letterhead for printable documents (reports, dental records).
    Styled by /css/print-doc.css. Address and contacts come from
    Configuration (SystemSetting::aboutInfo); pass $clinic to reuse an
    already-loaded copy.
--}}
@php
  $clinic = $clinic ?? \App\Models\SystemSetting::aboutInfo();
  // Phones share a line; the email gets its own so nothing wraps mid-line.
  $phoneParts = array_filter([
      filled($clinic['phone'] ?? null) ? 'Tel. ' . $clinic['phone'] : null,
      filled($clinic['mobile'] ?? null) ? 'Mobile ' . $clinic['mobile'] : null,
  ]);
@endphp
<header class="letterhead">
  <img class="logo" src="{{ $clinic['logo'] ?? '/images/puspus_logo.png' }}" alt="Puspus-Britanico Dental Clinic logo">
  <div class="letterhead-text">
    <div class="letterhead-name">Puspus-Britanico Dental Clinic</div>
    @if (filled($clinic['heroSubtitle'] ?? null))
      <div class="letterhead-tag">{{ $clinic['heroSubtitle'] }}</div>
    @endif
    @if (filled($clinic['address'] ?? null))
      <div class="letterhead-line">{{ $clinic['address'] }}</div>
    @endif
    @if ($phoneParts)
      <div class="letterhead-line">
        @foreach ($phoneParts as $part)
          @if (!$loop->first)<span class="sep">|</span>@endif{{ $part }}
        @endforeach
      </div>
    @endif
    @if (filled($clinic['email'] ?? null))
      <div class="letterhead-line">{{ $clinic['email'] }}</div>
    @endif
  </div>
  <div class="letterhead-spacer" aria-hidden="true"></div>
</header>
