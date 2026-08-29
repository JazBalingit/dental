{{--
    Odontogram / dental chart for one patient record (one completed visit).

    Params:
      $record   — PatientRecord with ->odontogramTeeth eager-loaded
      $readonly — true renders a non-editable chart + detail list (patient side)

    Needs: /css/odontogram.css and /js/odontogram.js on the page.
--}}
@php
    use App\Models\OdontogramTooth;

    $readonly = $readonly ?? false;

    $seed = $record->odontogramTeeth->mapWithKeys(fn ($t) => [
        $t->ToothNumber => [
            'condition'   => $t->Condition,
            'surfaces'    => $t->surface_list,
            'description' => $t->Description,
        ],
    ]);

    $upperRight = ['18', '17', '16', '15', '14', '13', '12', '11'];
    $upperLeft  = ['21', '22', '23', '24', '25', '26', '27', '28'];
    $lowerRight = ['48', '47', '46', '45', '44', '43', '42', '41'];
    $lowerLeft  = ['31', '32', '33', '34', '35', '36', '37', '38'];
@endphp

<div class="odontogram-heading">
  <i class="bi bi-clipboard2-pulse"></i> Odontogram / Dental Chart
</div>

<div class="odontogram {{ $readonly ? 'is-readonly' : '' }}" data-record="{{ $record->RecordID }}" data-readonly="{{ $readonly ? '1' : '0' }}">

  <div class="odontogram-toolbar">
    <span class="odontogram-visit">
      <i class="bi bi-calendar-event"></i> Visit of {{ $record->VisitDate->format('F j, Y') }}
    </span>
    <span class="odontogram-summary"></span>
  </div>

  {{-- Legend --}}
  <div class="odontogram-legend">
    @foreach (OdontogramTooth::CONDITIONS as $key => $label)
      <span class="odontogram-legend-item">
        <span class="odontogram-dot" style="background: {{ OdontogramTooth::CONDITION_COLORS[$key] }};"></span>
        {{ $label }}
      </span>
    @endforeach
  </div>

  {{-- Arches --}}
  <div class="odontogram-chart">
    <div class="odontogram-quadrant-row">
      <span>Upper Right</span><span>Upper Left</span>
    </div>

    <div class="odontogram-row">
      @foreach ($upperRight as $n) @include('partials.odontogram-tooth', ['n' => $n]) @endforeach
      <span class="odontogram-midline"></span>
      @foreach ($upperLeft as $n) @include('partials.odontogram-tooth', ['n' => $n]) @endforeach
    </div>

    <div class="odontogram-row">
      @foreach ($lowerRight as $n) @include('partials.odontogram-tooth', ['n' => $n]) @endforeach
      <span class="odontogram-midline"></span>
      @foreach ($lowerLeft as $n) @include('partials.odontogram-tooth', ['n' => $n]) @endforeach
    </div>

    <div class="odontogram-quadrant-row">
      <span>Lower Right</span><span>Lower Left</span>
    </div>
  </div>

  @if ($readonly)
    {{-- Read-only: list every charted tooth --}}
    <div class="odontogram-readonly-detail">
      <label class="odonto-label"><i class="bi bi-list-check"></i> Charted teeth</label>
      <div class="odontogram-detail-list"></div>
      <div class="odontogram-detail-empty">No teeth were charted on this visit.</div>
    </div>
  @else
    {{-- Per-tooth editor --}}
    <div class="odontogram-editor" hidden>
      <div class="odontogram-editor-head">
        <div class="odontogram-editor-title">
          Tooth <span class="odontogram-editor-tooth">—</span>
        </div>
        <button type="button" class="odontogram-editor-close" aria-label="Close">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <label class="field-label"><i class="bi bi-palette"></i> Condition</label>
      <div class="odontogram-condition-grid">
        @foreach (OdontogramTooth::CONDITIONS as $key => $label)
          <button type="button" class="odontogram-condition" data-condition="{{ $key }}">
            <span class="odontogram-dot" style="background: {{ OdontogramTooth::CONDITION_COLORS[$key] }};"></span>
            {{ $label }}
          </button>
        @endforeach
      </div>

      <label class="field-label"><i class="bi bi-bounding-box"></i> Affected surface(s)</label>
      <div class="odontogram-surface-grid">
        @foreach (OdontogramTooth::SURFACES as $surface)
          <label class="odontogram-surface">
            <input type="checkbox" value="{{ $surface }}"> {{ ucfirst($surface) }}
          </label>
        @endforeach
      </div>

      <label class="field-label"><i class="bi bi-journal-text"></i> Description</label>
      <textarea class="odontogram-desc" rows="2" placeholder="Clinical notes for this tooth..."></textarea>

      <div class="odontogram-editor-actions">
        <button type="button" class="btn-ghost odontogram-clear">
          <i class="bi bi-eraser"></i> Clear tooth
        </button>
        <button type="button" class="btn-brand odontogram-apply">
          <i class="bi bi-check2"></i> Apply
        </button>
      </div>
    </div>

    {{-- Save --}}
    <form method="POST" action="{{ route('patientRecords.odontogram.save', $record->RecordID) }}" class="odontogram-form">
      @csrf
      @include('partials.record-return-fields')
      <div class="odontogram-inputs"></div>
      <div class="odontogram-save-row">
        <span class="odontogram-hint">
          <i class="bi bi-info-circle"></i> Click a tooth to chart its condition. Each visit keeps its own chart.
        </span>
        <button type="submit" class="btn-brand">
          <i class="bi bi-floppy"></i> Save Odontogram
        </button>
      </div>
    </form>
  @endif

  <script type="application/json" class="odontogram-seed">{!! $seed->toJson() !!}</script>
  <script type="application/json" class="odontogram-colors">{!! json_encode(OdontogramTooth::CONDITION_COLORS) !!}</script>
  <script type="application/json" class="odontogram-condlabels">{!! json_encode(OdontogramTooth::CONDITIONS) !!}</script>
</div>
