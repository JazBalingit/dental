{{--
  "Archive Reason" table cell for the Archived tabs: why the row was archived
  (typed in the archive dialog) and when. Expects $row — anything with
  ArchiveReason / ArchivedAt — or $reason / $at for a computed value.
--}}
@php
    $reason = $reason ?? ($row->ArchiveReason ?? null);
    $at = $at ?? ($row->ArchivedAt ?? null);
@endphp
<td style="min-width:180px;">
    @if (!empty($reason))
        <span class="d-inline-block text-truncate align-bottom" style="max-width: 240px;"
            data-bs-toggle="tooltip" title="{{ $reason }}">{{ $reason }}</span>
        @if ($at)
            <div class="small text-muted-2">{{ \Carbon\Carbon::parse($at)->format('M j, Y g:i A') }}</div>
        @endif
    @else
        <span class="text-muted-2">—</span>
    @endif
</td>
