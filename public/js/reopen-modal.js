// Several pages server-render a modal as "already open" (class="modal fade
// show" + a manually-added .modal-backdrop) after a redirect — e.g. the OTP
// modals and the dentist-schedule day modal. That raw HTML/CSS state isn't
// backed by a real Bootstrap Modal instance, so click-outside-to-close, the
// Escape key, and even the X button's data-bs-dismiss all silently do
// nothing: the backdrop just sits there, inert, blocking the whole page.
//
// Fix: hand it off to Bootstrap's own JS immediately on load — strip the
// raw "open" state and call .show() so Bootstrap creates and manages its
// own backdrop and instance, same as if the user had clicked the trigger.
document.addEventListener('DOMContentLoaded', function () {
    var openModal = document.querySelector('.modal.show');
    if (!openModal || typeof bootstrap === 'undefined') {
        return;
    }

    var staticBackdrop = document.querySelector('.modal-backdrop');
    if (staticBackdrop) {
        staticBackdrop.remove();
    }

    openModal.classList.remove('show');
    openModal.style.display = '';
    openModal.removeAttribute('aria-modal');
    openModal.setAttribute('aria-hidden', 'true');

    bootstrap.Modal.getOrCreateInstance(openModal).show();
});
