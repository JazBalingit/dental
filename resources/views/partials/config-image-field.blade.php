{{--
    Image tile for the Configuration → System Information card.

    Params:
      $name    — input name ('logo', 'hero_image', 'about_image')
      $label   — tile title
      $current — URL of the currently saved image
      $desc    — one-line description shown under the title
      $accept  — optional accept attr (default ".jpg,.jpeg,.png")
      $fit     — 'cover' (default) or 'contain' (for the logo)
--}}
@php
    $fieldId = 'cfgimg_' . $name;
    $fit = $fit ?? 'cover';
@endphp
<div class="cfg-img-card">
  <label class="cfg-img-preview {{ $fit === 'contain' ? 'is-contain' : '' }}" for="{{ $fieldId }}" title="Click to upload a new image">
    <img src="{{ $current }}" alt="{{ $label }}" onerror="this.classList.add('is-broken')">
    <span class="cfg-img-badge"><i class="bi bi-camera-fill"></i></span>
  </label>
  <div class="cfg-img-title">{{ $label }}</div>
  <div class="cfg-img-desc">{{ $desc ?? '' }}</div>
  <label class="cfg-img-btn" for="{{ $fieldId }}"><i class="bi bi-arrow-up-circle"></i> Change image</label>
  <div class="cfg-img-picked" data-cfg-filename hidden>
    <i class="bi bi-check-circle-fill"></i> <span></span>
  </div>
  <input type="file" id="{{ $fieldId }}" name="{{ $name }}"
         accept="{{ $accept ?? '.jpg,.jpeg,.png' }}" class="d-none" data-cfg-img>
</div>
