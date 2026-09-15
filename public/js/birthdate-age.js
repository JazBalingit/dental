// Shared birthdate helpers applied across the app: any <input type="date">
// carrying data-age-target shows a live-computed age in that target field,
// and if it also carries data-minor-target, that section is hidden once the
// computed age is 18 or older (shown again for a minor). Works uniformly via
// data attributes so it applies without touching each form's own JS.
(function () {
    function computeAge(dobValue) {
        if (!dobValue) return null;
        var dob = new Date(dobValue);
        if (isNaN(dob.getTime())) return null;
        var today = new Date();
        var age = today.getFullYear() - dob.getFullYear();
        var m = today.getMonth() - dob.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
        return age >= 0 ? age : null;
    }

    function sync(input) {
        var age = computeAge(input.value);

        var ageTargetSel = input.dataset.ageTarget;
        if (ageTargetSel) {
            var ageTarget = document.querySelector(ageTargetSel);
            if (ageTarget) ageTarget.value = age === null ? '' : age;
        }

        var minorTargetSel = input.dataset.minorTarget;
        if (minorTargetSel) {
            var minorSection = document.querySelector(minorTargetSel);
            if (minorSection) minorSection.hidden = age === null ? false : age >= 18;
        }
    }

    function bind(input) {
        if (input.dataset.ageBound) return;
        input.dataset.ageBound = '1';
        input.addEventListener('input', function () { sync(input); });
        input.addEventListener('change', function () { sync(input); });
        sync(input);
    }

    function apply(root) {
        root = root || document;
        root.querySelectorAll('input[type="date"][data-age-target]').forEach(bind);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { apply(document); });
    } else {
        apply(document);
    }

    // Re-scan when a Bootstrap modal is shown, since its birthdate/age fields
    // are already in the DOM but need their initial sync run.
    document.addEventListener('shown.bs.modal', function (e) { apply(e.target); });
})();
