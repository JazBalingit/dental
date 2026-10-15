{{--
    Configuration → Backup & Restore (super admin only). Expects $backups
    (BackupService::list()) and $backupTypeLabels. Every action form is a
    .js-backup-form: confirmed via appConfirm(), then a "please wait"
    overlay covers the page while the server works.
--}}
@php
  $bkSize = function (int $bytes) {
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 1) . ' MB';
    return number_format($bytes / 1024, 1) . ' KB';
  };
  $latest = $backups[0] ?? null;
@endphp

<div class="card-soft backup-card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
      <div>
        <h5 class="backup-title mb-1"><i class="bi bi-database-check me-2"></i>Backup &amp; Restore</h5>
        <div class="small text-muted-2">Save your system records and uploaded files. Only the Super Admin can use these controls.</div>
      </div>
      <form method="POST" action="{{ route('configuration.backups.store') }}" class="js-backup-form"
        data-busy="Creating backup…">
        @csrf
        <button type="submit" class="btn btn-brand px-3"><i class="bi bi-plus-circle me-2"></i>Create backup now</button>
      </form>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="backup-stat">
          <div class="backup-stat-label">Last saved copy</div>
          <div class="backup-stat-value">{{ $latest ? $latest['created_at']->format('M j, Y g:i A') : 'None yet' }}</div>
          <div class="backup-stat-sub">{{ $latest ? 'Philippine time' : 'Create your first backup.' }}</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="backup-stat">
          <div class="backup-stat-label">Backup method</div>
          <div class="backup-stat-value">Manual backup</div>
          <div class="backup-stat-sub">Create a copy whenever you need one.</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="backup-stat">
          <div class="backup-stat-label">Copies saved on the website</div>
          <div class="backup-stat-value">{{ count($backups) }} {{ count($backups) === 1 ? 'copy' : 'copies' }}</div>
          <div class="backup-stat-sub">Copies stay until you delete them.</div>
        </div>
      </div>
    </div>

    <h6 class="backup-section-title">Saved copies</h6>
    @forelse ($backups as $copy)
      <div class="backup-copy">
        <div class="backup-copy-info">
          <div class="backup-copy-date">{{ $copy['created_at']->format('M j, Y g:i A') }}</div>
          <div class="backup-copy-meta">
            {{ $bkSize($copy['size']) }} · {{ $copy['table_count'] }} tables · {{ $copy['file_count'] }} uploaded {{ $copy['file_count'] === 1 ? 'file' : 'files' }}
          </div>
          <div class="backup-copy-meta mt-1">
            <span class="backup-tag {{ $copy['type'] === 'pre_restore' ? 'is-auto' : '' }}">{{ $backupTypeLabels[$copy['type']] ?? 'Backup' }}</span>
            @if ($copy['created_by']) <span class="ms-2">{{ $copy['created_by'] }}</span> @endif
          </div>
        </div>
        <div class="backup-copy-actions">
          <form method="POST" action="{{ route('configuration.backups.restore', $copy['name']) }}" class="js-backup-form"
            data-busy="Restoring… please don't close this page."
            data-title="Restore this copy?"
            data-message="Every current record will be replaced with the data from {{ $copy['created_at']->format('M j, Y g:i A') }}. Anything added after that will be lost. A safety copy of the current data is saved first, and everyone (including you) will need to sign in again."
            data-label="Restore" data-class="btn-pill-archive">
            @csrf
            <button type="submit" class="btn btn-outline-success btn-sm backup-btn"><i class="bi bi-clock-history me-1"></i>Restore this copy</button>
          </form>
          <a href="{{ route('configuration.backups.download', $copy['name']) }}" class="btn btn-outline-secondary btn-sm backup-btn">
            <i class="bi bi-download me-1"></i>Save to computer
          </a>
          <form method="POST" action="{{ route('configuration.backups.destroy', $copy['name']) }}" class="js-backup-form"
            data-title="Delete this backup copy?"
            data-message="The copy from {{ $copy['created_at']->format('M j, Y g:i A') }} will be permanently removed from the website. Copies saved to your computer are not affected."
            data-label="Delete" data-class="btn-pill-archive">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm backup-btn" title="Delete this copy" aria-label="Delete this copy"><i class="bi bi-trash"></i></button>
          </form>
        </div>
      </div>
    @empty
      <div class="backup-copy text-muted-2 justify-content-center">No backups yet. Click <strong class="mx-1">Create backup now</strong> to make the first one.</div>
    @endforelse

    <div class="row g-3 mt-1">
      <div class="col-md-6">
        <div class="backup-panel">
          <div class="backup-panel-title"><i class="bi bi-download me-2"></i>Keep a copy away from the website</div>
          <p class="mb-0">Use <strong>Save to computer</strong> after creating a backup. Store it somewhere private: saved copies
            contain account and patient information. Website copies share the server's storage, and may be cleared when the
            system is redeployed.</p>
        </div>
      </div>
      <div class="col-md-6">
        <div class="backup-panel">
          <div class="backup-panel-title"><i class="bi bi-upload me-2"></i>Bring back a saved file</div>
          <p class="mb-3">Choose a ZIP backup previously downloaded from this system.</p>
          <form method="POST" action="{{ route('configuration.backups.restoreUpload') }}" enctype="multipart/form-data"
            class="js-backup-form" id="backupUploadForm"
            data-busy="Uploading and restoring… please don't close this page."
            data-title="Restore from this file?"
            data-message="Every current record will be replaced with the data in the chosen file. A safety copy of the current data is saved first, and everyone (including you) will need to sign in again."
            data-label="Restore" data-class="btn-pill-archive">
            @csrf
            <input type="file" name="backup_file" id="backupFileInput" accept=".zip,application/zip" hidden>
            <button type="button" class="btn btn-outline-success btn-sm backup-btn" id="backupChooseBtn">
              <i class="bi bi-upload me-1"></i>Choose a saved file
            </button>
            @error('backup_file')
              <div class="field-error mt-2"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
            @enderror
          </form>
        </div>
      </div>
    </div>

    <p class="small text-muted-2 mt-3 mb-0">
      Includes database records, appointment photos, and uploaded logos, landing-page images and profile pictures.
      Application code, server settings and login sessions are excluded. Restores require a matching system version.
      A restore replaces all records in one step — if anything goes wrong, nothing is changed.
    </p>
  </div>
</div>

<div class="backup-busy" id="backupBusy" hidden>
  <div class="backup-busy-box">
    <div class="spinner-border text-success mb-3" role="status"></div>
    <div id="backupBusyText">Working…</div>
  </div>
</div>

<script>
  (function () {
    var busy = document.getElementById('backupBusy');
    var busyText = document.getElementById('backupBusyText');

    function go(form) {
      busyText.textContent = form.dataset.busy || 'Working…';
      // Downloads/deletes are quick; only long jobs get the overlay.
      if (form.dataset.busy) busy.hidden = false;
      form.submit();
    }

    document.querySelectorAll('.js-backup-form').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!form.dataset.title) return go(form);
        window.appConfirm({
          title: form.dataset.title,
          message: form.dataset.message,
          confirmLabel: form.dataset.label,
          confirmClass: form.dataset.class,
        }).then(function (ok) {
          if (ok) go(form);
          else if (form.id === 'backupUploadForm') document.getElementById('backupFileInput').value = '';
        });
      });
    });

    var input = document.getElementById('backupFileInput');
    document.getElementById('backupChooseBtn').addEventListener('click', function () { input.click(); });
    input.addEventListener('change', function () {
      if (!input.files.length) return;
      var form = document.getElementById('backupUploadForm');
      form.dataset.message = 'Restore from "' + input.files[0].name + '"? Every current record will be replaced with the data in this file. A safety copy of the current data is saved first, and everyone (including you) will need to sign in again.';
      form.requestSubmit();
    });
  })();
</script>
