{{--
  One shared "Appointment Details" modal for the notifications list. Clicking a
  booked / approved / completed notification loads that appointment's details
  into it (see public/js/notification-details.js). Included by both
  admin-notif-modal and user-notif-modal.
--}}
<div class="modal fade" id="notifDetailModal" tabindex="-1" aria-labelledby="notifDetailTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="notifDetailTitle">Appointment Details</h5>
                    <div class="small text-muted" id="notifDetailSubtitle"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="notifDetailBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="notifDetailCsrf" value="{{ csrf_token() }}">
<script src="{{ asset('js/notification-details.js') }}"></script>
