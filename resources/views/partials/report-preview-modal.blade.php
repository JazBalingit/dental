{{--
    "Report Preview" — opened by Generate Report on the dashboard. Shows the
    exactly-as-printed report (reports.generate?preview=1) in a frame so the
    admin can check it before printing. Print prints the frame; Back returns
    to the report options.
--}}
<div class="modal fade" id="reportPreviewModal" tabindex="-1" aria-labelledby="reportPreviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-lg-down">
    <div class="modal-content">
      <div class="modal-header report-modal-header">
        <div class="icon-badge"><i class="bi bi-eye"></i></div>
        <div class="flex-grow-1">
          <div class="modal-title" id="reportPreviewModalLabel">Report Preview</div>
          <div class="subtitle">This is exactly what will be printed. Check it, then print or go back to change the options.</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0 position-relative" style="background: var(--ink-100);">
        <div id="reportPreviewLoading" class="position-absolute top-50 start-50 translate-middle text-center text-muted-2">
          <span class="spinner-border spinner-border-sm me-2"></span>Preparing report…
        </div>
        <iframe id="reportPreviewFrame" title="Report preview"
          style="display:block; width:100%; height:72vh; border:0; background:transparent;"></iframe>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-ghost" id="reportPreviewBackBtn"><i class="bi bi-arrow-left me-1"></i> Back</button>
        <button type="button" class="btn btn-brand px-4" id="reportPreviewPrintBtn" disabled>
          <i class="bi bi-printer me-1"></i> Print
        </button>
      </div>
    </div>
  </div>
</div>
