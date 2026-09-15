// Shared input restrictions applied by field name across the whole app:
// phone/mobile fields accept digits only (capped at 11), name-type fields
// (first/last/middle name, occupation, nationality, religion, guardian
// info) accept letters + spaces/apostrophes/hyphens/periods only — no
// digits. Works by scanning for known `name` attributes so it applies
// uniformly without touching every form individually.
(function () {
    var PHONE_FIELD_NAMES = ['phone'];
    var LETTER_FIELD_NAMES = [
        'first_name', 'last_name', 'middle_name',
        'occupation', 'nationality', 'religion',
        'guardian_name', 'guardian_occupation'
    ];

    var LETTER_ALLOWED = /[^\p{L}\s'.-]/gu;
    var DIGITS_ONLY = /\D/g;

    function restrictPhone(el) {
        if (el.dataset.restricted) return;
        el.dataset.restricted = '1';
        if (!el.getAttribute('inputmode')) el.setAttribute('inputmode', 'numeric');
        el.addEventListener('input', function () {
            var caret = el.selectionEnd;
            var before = el.value.length;
            el.value = el.value.replace(DIGITS_ONLY, '').slice(0, 11);
            if (caret !== null) {
                var diff = before - el.value.length;
                try { el.setSelectionRange(caret - diff, caret - diff); } catch (e) {}
            }
        });
    }

    function restrictLetters(el) {
        if (el.dataset.restricted) return;
        el.dataset.restricted = '1';
        el.addEventListener('input', function () {
            var caret = el.selectionEnd;
            var before = el.value.length;
            el.value = el.value.replace(LETTER_ALLOWED, '');
            if (caret !== null) {
                var diff = before - el.value.length;
                try { el.setSelectionRange(caret - diff, caret - diff); } catch (e) {}
            }
        });
    }

    function apply(root) {
        root = root || document;
        PHONE_FIELD_NAMES.forEach(function (name) {
            root.querySelectorAll('input[name="' + name + '"]').forEach(restrictPhone);
        });
        LETTER_FIELD_NAMES.forEach(function (name) {
            root.querySelectorAll('input[name="' + name + '"]').forEach(restrictLetters);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { apply(document); });
    } else {
        apply(document);
    }

    // Modals/rows rendered per-record are already in the DOM at load time,
    // but re-scan on modal show too in case anything gets injected later
    // (e.g. a cloned row) — cheap no-op otherwise thanks to the guard above.
    document.addEventListener('shown.bs.modal', function (e) { apply(e.target); });
})();
