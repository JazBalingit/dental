{{--
    One labelled input in the signup style (icon + field + inline error).
    Everything comes in one $f array so nothing leaks in from the including
    view's own variables: name, label, icon, type (text), placeholder,
    required (false), optional (false — adds "(optional)"), value (old()
    fallback), attrs (raw extra attributes), col (column classes), id (suffix).
--}}
@php
    $fName = $f['name'];
    $fId = 'f_' . $fName . ($f['id'] ?? '');
    $fValue = old($fName, $f['value'] ?? '');
@endphp
<div class="{{ $f['col'] ?? 'col-md-6' }}">
    <label class="form-label" for="{{ $fId }}">{{ $f['label'] }} @if (!empty($f['optional']))<span class="text-muted">(optional)</span>@endif</label>
    <div class="input-icon @error($fName) has-error @enderror"><i class="bi {{ $f['icon'] }}"></i>
        <input type="{{ $f['type'] ?? 'text' }}" name="{{ $fName }}" id="{{ $fId }}" class="form-control" value="{{ $fValue }}"
            placeholder="{{ $f['placeholder'] ?? '' }}" @if (!empty($f['required'])) required data-req="1" @endif {!! $f['attrs'] ?? '' !!} />
    </div>
    @error($fName) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
</div>
