{{--
    Privacy Policy + Legal Terms modals (signup, guest booking). The admin's
    plain text from Configuration is formatted by App\Support\LegalText —
    headings, bullet lists, the "Last updated" badge. Needs $privacyPolicy
    and $legalTerms; opened by data-bs-target="#privacyPolicyModal" /
    "#legalTermsModal".
--}}
@foreach ([
    ['id' => 'privacyPolicyModal', 'title' => 'Privacy Policy', 'icon' => 'bi-shield-lock', 'text' => $privacyPolicy ?? '', 'empty' => 'This clinic has not published a privacy policy yet.'],
    ['id' => 'legalTermsModal', 'title' => 'Terms of Use', 'icon' => 'bi-file-earmark-text', 'text' => $legalTerms ?? '', 'empty' => 'This clinic has not published its legal terms yet.'],
] as $doc)
  <div class="modal fade legal-modal" id="{{ $doc['id'] }}" tabindex="-1" aria-hidden="true" aria-labelledby="{{ $doc['id'] }}Title">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-semibold" id="{{ $doc['id'] }}Title"><i class="bi {{ $doc['icon'] }} me-2"></i>{{ $doc['title'] }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="legal-doc">
            @if (trim($doc['text']) !== '')
              {!! \App\Support\LegalText::toHtml($doc['text']) !!}
            @else
              <p class="text-muted-2 mb-0">{{ $doc['empty'] }}</p>
            @endif
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-brand px-4" data-bs-dismiss="modal">Got it</button>
        </div>
      </div>
    </div>
  </div>
@endforeach
