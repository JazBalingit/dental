{{--
    Generic "Print Preview" — one copy per page. Any button with
    data-print-preview="{{ url }}" (optionally data-print-title="…") opens
    this modal, loads that page in a frame exactly as it will print, and
    prints the frame on "Print". "Download PDF" saves it as an A4 PDF via
    the frame's downloadDocPdf() (public/js/print-doc.js); it stays hidden
    for pages that don't load that script. If the button sits inside
    another open modal, that one closes first and comes back when the
    preview closes.
--}}
<div class="modal fade" id="printPreviewModal" tabindex="-1" aria-labelledby="printPreviewTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-lg-down">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title fw-semibold" id="printPreviewTitle"><i class="bi bi-printer me-1"></i> Print Preview</h5>
          <div class="small text-muted" id="printPreviewSubtitle">This is exactly what will be printed.</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0 position-relative" style="background:#eef2f0;">
        <div id="printPreviewLoading" class="position-absolute top-50 start-50 translate-middle text-muted small">
          <span class="spinner-border spinner-border-sm me-2"></span>Preparing document…
        </div>
        <iframe id="printPreviewFrame" title="Print preview" style="display:block;width:100%;height:72vh;border:0;"></iframe>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sec px-4" id="printPreviewPdfBtn" disabled>
          <i class="bi bi-file-earmark-arrow-down me-1"></i> Download PDF
        </button>
        <button type="button" class="btn btn-success px-4" id="printPreviewPrintBtn" disabled
          style="background:#167d1d;border-color:#167d1d;"><i class="bi bi-printer me-1"></i> Print</button>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    var modalEl = document.getElementById('printPreviewModal');
    if (!modalEl || modalEl.dataset.ready) return;
    modalEl.dataset.ready = '1';

    var frame = document.getElementById('printPreviewFrame');
    var loading = document.getElementById('printPreviewLoading');
    var printBtn = document.getElementById('printPreviewPrintBtn');
    var pdfBtn = document.getElementById('printPreviewPdfBtn');
    var pdfBtnLabel = pdfBtn.innerHTML;
    var subtitle = document.getElementById('printPreviewSubtitle');
    var returnTo = null;

    function open(trigger) {
      printBtn.disabled = true;
      pdfBtn.disabled = true;
      pdfBtn.innerHTML = pdfBtnLabel;
      loading.hidden = false;
      frame.style.visibility = 'hidden';
      subtitle.textContent = trigger.dataset.printTitle
        ? trigger.dataset.printTitle + ' — this is exactly what will be printed.'
        : 'This is exactly what will be printed.';
      frame.src = trigger.dataset.printPreview;
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    frame.addEventListener('load', function () {
      if (!frame.src || frame.src === 'about:blank') return;
      loading.hidden = true;
      frame.style.visibility = 'visible';
      printBtn.disabled = false;
      var canPdf = typeof frame.contentWindow.downloadDocPdf === 'function';
      pdfBtn.hidden = !canPdf;
      pdfBtn.disabled = !canPdf;
    });

    printBtn.addEventListener('click', function () {
      frame.contentWindow.focus();
      frame.contentWindow.print();
    });

    // Download PDF: the previewed page builds and saves the file itself.
    pdfBtn.addEventListener('click', function () {
      var frameWin = frame.contentWindow;
      if (!frameWin || typeof frameWin.downloadDocPdf !== 'function') return;
      pdfBtn.disabled = true;
      pdfBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Preparing PDF…';
      Promise.resolve().then(function () { return frameWin.downloadDocPdf(); }).then(function () {
        pdfBtn.innerHTML = pdfBtnLabel;
      }, function () {
        pdfBtn.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i> Couldn\'t create the PDF, try again';
        setTimeout(function () { pdfBtn.innerHTML = pdfBtnLabel; }, 4000);
      }).then(function () { pdfBtn.disabled = false; });
    });

    document.addEventListener('click', function (e) {
      var trigger = e.target.closest('[data-print-preview]');
      if (!trigger) return;
      e.preventDefault();
      // One Bootstrap modal at a time: close the one the button is in,
      // open the preview once it's gone, and return to it afterwards.
      var parent = trigger.closest('.modal.show');
      returnTo = parent;
      if (parent) {
        parent.addEventListener('hidden.bs.modal', function () { open(trigger); }, { once: true });
        bootstrap.Modal.getOrCreateInstance(parent).hide();
      } else {
        open(trigger);
      }
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
      frame.src = 'about:blank';
      if (returnTo) bootstrap.Modal.getOrCreateInstance(returnTo).show();
      returnTo = null;
    });
  })();
</script>
