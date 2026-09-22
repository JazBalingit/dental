// Lock-until-Edit-clicked behavior for a profile form. Any <form
// data-edit-toggle> starts with its [data-editable] fields disabled, and any
// [data-edit-only] element (e.g. the "Change Photo" control) starts hidden
// entirely — not just disabled, so it doesn't show at all until Edit is
// clicked. Its data-edit-btn="edit" button enables/reveals both and swaps in
// the data-edit-btn="cancel"/"save" buttons. Cancel reverts to the
// server-rendered values and re-locks/re-hides the form. The actual "are you
// sure" confirmation on save comes from
// partials/confirm-action-modal.blade.php's shared data-confirm-message form
// handling — this script only owns the lock/unlock state, not the
// submit-time confirmation.
(function () {
    function setEditing(form, fields, editOnly, buttons, on) {
        fields.forEach(function (f) { f.disabled = !on; });
        editOnly.forEach(function (el) { el.hidden = !on; });
        buttons.edit.hidden = on;
        if (buttons.cancel) buttons.cancel.hidden = !on;
        buttons.save.hidden = !on;
    }

    function bind(form) {
        if (form.dataset.editToggleBound) return;
        form.dataset.editToggleBound = '1';

        var fields = form.querySelectorAll('[data-editable]');
        var editOnly = form.querySelectorAll('[data-edit-only]');
        var buttons = {
            edit: form.querySelector('[data-edit-btn="edit"]'),
            cancel: form.querySelector('[data-edit-btn="cancel"]'),
            save: form.querySelector('[data-edit-btn="save"]'),
        };
        if (!buttons.edit || !buttons.save) return;

        buttons.edit.addEventListener('click', function () {
            setEditing(form, fields, editOnly, buttons, true);
        });

        if (buttons.cancel) {
            buttons.cancel.addEventListener('click', function () {
                form.reset();
                // Native reset() doesn't fire 'change', so anything derived
                // from a field's value (computed Age, the minor-guardian
                // section) needs a nudge to resync with the reverted value.
                form.querySelectorAll('[data-age-target]').forEach(function (el) {
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                });
                setEditing(form, fields, editOnly, buttons, false);
            });
        }

        // A failed save (e.g. a validation error) re-renders the page with
        // the fields already enabled and Cancel/Save already showing, so the
        // user can see and fix what's wrong — don't stomp on that by
        // re-locking the form back to its default "Edit" state.
        if (!buttons.edit.hidden) {
            setEditing(form, fields, editOnly, buttons, false);
        }
    }

    function apply(root) {
        (root || document).querySelectorAll('form[data-edit-toggle]').forEach(bind);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { apply(document); });
    } else {
        apply(document);
    }
})();
