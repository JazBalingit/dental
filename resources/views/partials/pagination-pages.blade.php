{{--
    Windowed page-number list for the `.pages` div inside either the admin
    side's .pagination-soft or the patient portal's .history-footer — first
    page, last page, and a small window around the current page, with a
    single "..." gap marker instead of printing every page number (which
    gets unreadable once a table has 15+ pages).

    Usage: @include('partials.pagination-pages', ['paginator' => $users])
    Optional: 'onEachSide' => 1 (default) — how many pages to show beside
    the current one before collapsing into "...".
--}}
@php
    $onEachSide = $onEachSide ?? 1;
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();
    $pages = collect();
    for ($i = 1; $i <= $last; $i++) {
        if ($i === 1 || $i === $last || ($i >= $current - $onEachSide && $i <= $current + $onEachSide)) {
            $pages->push($i);
        } elseif ($pages->last() !== '...') {
            $pages->push('...');
        }
    }
@endphp
<a href="{{ $paginator->url(1) }}" class="{{ $current === 1 ? 'disabled' : '' }}" aria-label="First page"><i class="bi bi-chevron-bar-left"></i></a>
<a href="{{ $paginator->previousPageUrl() ?? '#' }}" class="{{ $current === 1 ? 'disabled' : '' }}" aria-label="Previous page"><i class="bi bi-chevron-left"></i></a>
@foreach ($pages as $page)
    @if ($page === '...')
        <span class="pagination-ellipsis">&hellip;</span>
    @else
        <a href="{{ $paginator->url($page) }}" class="{{ $current === $page ? 'active' : '' }}">{{ $page }}</a>
    @endif
@endforeach
<a href="{{ $paginator->nextPageUrl() ?? '#' }}" class="{{ $current === $last ? 'disabled' : '' }}" aria-label="Next page"><i class="bi bi-chevron-right"></i></a>
<a href="{{ $paginator->url($last) }}" class="{{ $current === $last ? 'disabled' : '' }}" aria-label="Last page"><i class="bi bi-chevron-bar-right"></i></a>
