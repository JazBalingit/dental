{{--
    Table filters apply themselves — no Apply button, no pressing Enter.
    Works on every filter bar (forms with .data-toolbar / .history-filters /
    .cfg-toolbar): picking a dropdown or date applies it at once, and typing in
    a search box applies it after a short pause. The search box gets focus
    back (cursor at the end) after the page reloads, so you can keep typing.
    Safe to include more than once.
--}}
<script>
(function () {
    if (window.__autoFilter) return;
    window.__autoFilter = true;

    var FORMS = 'form.data-toolbar, form.history-filters, form.cfg-toolbar';
    var KEY = 'autoFilterFocus';
    var timer = null;

    function isFilterForm(form) {
        return form && form.matches(FORMS) && (form.getAttribute('method') || 'get').toLowerCase() === 'get';
    }

    function submit(form, field) {
        clearTimeout(timer);
        try {
            if (field && field.name && field.matches('input[type=text], input[type=search], input:not([type])')) {
                sessionStorage.setItem(KEY, JSON.stringify({ path: location.pathname, name: field.name }));
            }
        } catch (e) {}
        // Typing a new search should start from page 1.
        form.querySelectorAll('input[name$="page"]').forEach(function (i) { i.disabled = true; });
        form.requestSubmit ? form.requestSubmit() : form.submit();
    }

    // Dropdowns / dates: apply immediately (skip ones that already submit themselves).
    document.addEventListener('change', function (e) {
        var el = e.target;
        var form = el.form;
        if (!isFilterForm(form) || el.hasAttribute('onchange')) return;
        if (el.matches('select, input[type=date], input[type=month], input[type=checkbox], input[type=radio]')) submit(form, el);
    });

    // Search boxes: apply after the user pauses typing.
    document.addEventListener('input', function (e) {
        var el = e.target;
        var form = el.form;
        if (!isFilterForm(form) || !el.matches('input[type=text], input[type=search], input:not([type])')) return;
        clearTimeout(timer);
        var before = el.dataset.lastValue !== undefined ? el.dataset.lastValue : el.defaultValue;
        timer = setTimeout(function () {
            if (el.value.trim() === (before || '').trim()) return;
            submit(form, el);
        }, 500);
    });

    // After the reload, put the cursor back in the search box the user was typing in.
    function restoreFocus() {
        var saved;
        try { saved = JSON.parse(sessionStorage.getItem(KEY) || 'null'); sessionStorage.removeItem(KEY); } catch (e) { saved = null; }
        if (!saved || saved.path !== location.pathname) return;
        var field = document.querySelector(FORMS.split(',').map(function (s) { return s.trim() + ' [name="' + saved.name + '"]'; }).join(','));
        if (!field) return;
        field.focus();
        var end = field.value.length;
        try { field.setSelectionRange(end, end); } catch (e) {}
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', restoreFocus);
    else restoreFocus();
})();
</script>
