{{--
    Patient Portal → Book Appointment (for yourself): the medical-history
    step inside the calendar's confirm form. Expects $bookSelfMedical and $dayKey.
--}}
<div class="book-photos mb-3">
    @include('partials.medical-history-step', ['mhs' => ['name' => 'mh', 'id' => 'd' . $dayKey, 'latest' => $bookSelfMedical ?? null, 'whose' => 'your']])
</div>
