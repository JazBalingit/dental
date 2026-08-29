{{--
    A single clickable tooth in the odontogram. Expects $n (FDI number).
    Colour + flags are painted by the odontogram script from saved state.
--}}
<button type="button" class="odontogram-tooth" data-tooth="{{ $n }}" aria-label="Tooth {{ $n }}">
  <svg viewBox="0 0 36 46" class="odontogram-tooth-svg" aria-hidden="true">
    <path class="odontogram-tooth-shape"
      d="M18 3c5 0 9.5 1.6 11.5 5 1.8 3 1.7 7 0.8 11.6-0.9 4.4-1 9.4-2.1 14.4-0.9 4-2 7-4.2 7-2.3 0-3.2-3.2-3.4-7.2-0.15-3-1.6-5.1-2.6-5.1s-2.45 2.1-2.6 5.1c-0.2 4-1.1 7.2-3.4 7.2-2.2 0-3.3-3-4.2-7-1.1-5-1.2-10-2.1-14.4-0.9-4.6-1-8.6 0.8-11.6C8.5 4.6 13 3 18 3Z" />
    <line class="odontogram-tooth-x" x1="8" y1="8" x2="28" y2="38" />
    <line class="odontogram-tooth-x" x1="28" y1="8" x2="8" y2="38" />
  </svg>
  <span class="odontogram-tooth-num">{{ $n }}</span>
  <span class="odontogram-tooth-flag" title="Has notes / surfaces" hidden></span>
</button>
