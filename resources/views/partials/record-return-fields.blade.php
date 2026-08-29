{{--
    Carries the current Patient Records list state (tab / search / page)
    through a save so the controller can send the user back to exactly
    where they were, with the View modal re-opened.
--}}
<input type="hidden" name="ret[search]" value="{{ request('search') }}">
<input type="hidden" name="ret[tab]" value="{{ request('tab') }}">
<input type="hidden" name="ret[page]" value="{{ request('page') }}">
<input type="hidden" name="ret[archived_page]" value="{{ request('archived_page') }}">
