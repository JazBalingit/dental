<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DentistScheduleController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AppointmentBookingController;
use App\Http\Controllers\StaffAccountController;
use App\Http\Controllers\StaffProfileController;
use App\Http\Controllers\AppointmentApprovalController;
use App\Http\Controllers\AppointmentAttachmentController;
use App\Http\Controllers\UserAccountController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AppointmentsController;
use App\Http\Controllers\PatientRecordsController;
use App\Http\Controllers\OdontogramController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\WalkInController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EmailChangeController;
use App\Http\Controllers\RescheduleController;
use App\Http\Controllers\SuperAdminSetupController;
use App\Http\Controllers\ClosedDateController;
use App\Http\Controllers\GuestBookingController;
use App\Http\Controllers\ManageAppointmentController;
use App\Http\Controllers\MedicalHistoryController;

/*
|--------------------------------------------------------------------------
| Access control lives here, on the routes — not in each controller.
|   (no middleware)   public pages + the login / signup / OTP flows
|   auth.session      any signed-in user (patients + admins)
|   admin             session('user_role') in UserAccount::ADMIN_ROLES (staff, dentist, super admin)
|   admin + superadmin  super admin only (Staff Accounts, Configuration)
| EnsureStaffIsVerified (global) still forces an unverified staff account
| onto the verification screen regardless of the above.
|--------------------------------------------------------------------------
*/

// Root shows the real landing page (controller action, not a closure, so
// `php artisan route:cache` can still serialize it). /landing-page keeps
// working too — route('landingPage') is used all over the views/nav.
Route::get('/', [UserController::class, 'showLandingPage'])->name('home');

// ---- Public pages ----
Route::get('landing-page', [UserController::class, 'showLandingPage'])->name('landingPage');

// Landing-page "Contact Us" form — open to guests and signed-in patients alike.
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');

// ---- Booking without an account (landing-page calendar → email code → details) ----
Route::post('/book/start', [GuestBookingController::class, 'start'])->name('guestBooking.start');
Route::get('/book', [GuestBookingController::class, 'show'])->name('guestBooking.show');
Route::post('/book/email', [GuestBookingController::class, 'sendCode'])->name('guestBooking.email');
Route::post('/book/resend', [GuestBookingController::class, 'resend'])->name('guestBooking.resend');
Route::post('/book/verify', [GuestBookingController::class, 'verify'])->name('guestBooking.verify');
Route::post('/book/change-email', [GuestBookingController::class, 'changeEmail'])->name('guestBooking.changeEmail');
Route::post('/book/confirm', [GuestBookingController::class, 'confirm'])->name('guestBooking.confirm');
Route::post('/book/cancel', [GuestBookingController::class, 'cancel'])->name('guestBooking.cancel');
Route::post('/book/back', [GuestBookingController::class, 'back'])->name('guestBooking.back');
Route::post('/book/people/{id}/remove', [GuestBookingController::class, 'removePerson'])->whereNumber('id')->name('guestBooking.removePerson');

// ---- Manage an appointment without an account (the emailed link, or look it up by email code) ----
Route::get('/appointment/manage/{token}', [ManageAppointmentController::class, 'show'])->name('manage.show');
Route::post('/appointment/manage/{token}/cancel', [ManageAppointmentController::class, 'cancel'])->name('manage.cancel');
Route::get('/appointment/manage/{token}/reschedule', [ManageAppointmentController::class, 'reschedulePage'])->name('manage.reschedule');
Route::post('/appointment/manage/{token}/reschedule', [ManageAppointmentController::class, 'reschedule'])->name('manage.reschedule.store');
Route::post('/appointment/manage/{token}/proposal/accept', [ManageAppointmentController::class, 'acceptProposal'])->name('manage.proposal.accept');
Route::post('/appointment/manage/{token}/proposal/decline', [ManageAppointmentController::class, 'declineProposal'])->name('manage.proposal.decline');
Route::get('/manage-booking', [ManageAppointmentController::class, 'lookup'])->name('manage.lookup');
Route::post('/manage-booking/send', [ManageAppointmentController::class, 'lookupSend'])->name('manage.lookup.send');
Route::post('/manage-booking/verify', [ManageAppointmentController::class, 'lookupVerify'])->name('manage.lookup.verify');
Route::post('/manage-booking/reset', [ManageAppointmentController::class, 'lookupReset'])->name('manage.lookup.reset');

// ---- Signup + OTP verification ----
Route::get('/signup', [RegisterController::class, 'create'])->name('signup');
Route::post('/signup', [RegisterController::class, 'store'])->name('register.store');
Route::post('/signup/verify-otp', [RegisterController::class, 'verify'])->name('register.verify');
Route::post('/signup/resend-otp', [RegisterController::class, 'resend'])->name('register.resend');
Route::post('/signup/cancel-otp', [RegisterController::class, 'cancelOtp'])->name('register.cancel');

// ---- Login / Logout ----
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.store');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ---- Forgot / Reset Password (modal on the login page, no separate GET page) ----
Route::post('/forgot-password', [LoginController::class, 'sendResetCode'])->name('password.email');
Route::post('/forgot-password/resend', [LoginController::class, 'resendResetCode'])->name('password.resend');
Route::post('/reset-password', [LoginController::class, 'resetPassword'])->name('password.update');
Route::post('/forgot-password/cancel', [LoginController::class, 'cancelReset'])->name('password.cancel');


/*
|--------------------------------------------------------------------------
| Signed-in users (patients and admins both)
|--------------------------------------------------------------------------
*/
Route::middleware('auth.session')->group(function () {

    // Patient — my appointments (3 pages under one sidebar dropdown)
    Route::get('user-appointment', [UserController::class, 'showUserAppointment'])->name('userAppointment');
    Route::get('user-appointment/book', [UserController::class, 'showBookAppointment'])->name('userAppointment.book');
    Route::get('user-appointment/history', [UserController::class, 'showAppointmentHistory'])->name('userAppointment.history');
    Route::post('/user-appointment/{appointment}/remove', [UserController::class, 'removeAppointment'])->name('userAppointment.remove');

    // Patient — reschedule: pick your own new time, or answer a clinic proposal
    Route::get('/user-appointment/{appointment}/reschedule', [RescheduleController::class, 'patientPage'])->whereNumber('appointment')->name('userAppointment.reschedule');
    Route::post('/user-appointment/{appointment}/reschedule', [RescheduleController::class, 'patientMove'])->whereNumber('appointment')->name('userAppointment.reschedule.store');
    Route::post('/my-reschedules/{id}/accept', [RescheduleController::class, 'patientAccept'])->whereNumber('id')->name('myReschedules.accept');
    Route::post('/my-reschedules/{id}/decline', [RescheduleController::class, 'patientDecline'])->whereNumber('id')->name('myReschedules.decline');

    // Patient — my dental records (read-only view of my own completed visits)
    Route::get('/my-records', [PatientRecordsController::class, 'mine'])->name('myRecords');
    Route::get('/my-records/{id}/print', [PatientRecordsController::class, 'printMine'])->whereNumber('id')->name('myRecords.print');

    // Patient — medical history (yours, or someone you book for)
    Route::get('/my-medical-history', [MedicalHistoryController::class, 'show'])->name('myMedicalHistory');
    Route::post('/my-medical-history', [MedicalHistoryController::class, 'update'])->name('myMedicalHistory.update');

    // Patient — book an appointment
    Route::post('/book-appointment', [AppointmentBookingController::class, 'store'])->name('booking.store');

    // Booking photos (patient's own, or any clinic staff) — access-checked in-controller
    Route::get('/appointment-attachments/{id}', [AppointmentAttachmentController::class, 'show'])->whereNumber('id')->name('appointmentAttachments.show');

    // Profile (patient edits their own info; UserID / DateCreated are never editable)
    Route::post('/profile', [ProfileController::class, 'update'])->name('userProfile.update');

    // Change an account's email — a code is sent to the NEW address and must
    // be entered first. Your own account (patients + admins), plus patient
    // accounts (any admin) and staff accounts (super admin), checked in-controller.
    Route::post('/email-change/send', [EmailChangeController::class, 'send'])->name('emailChange.send');
    Route::post('/email-change/verify', [EmailChangeController::class, 'verify'])->name('emailChange.verify');

    // Settings / Change Password (+ the "Configuration" activity-trail tab: /settings?tab=configuration)
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings');
    Route::post('/settings/update-password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::post('/settings/send-reset-code', [SettingsController::class, 'sendResetCode'])->name('settings.reset.send');
    Route::post('/settings/resend-reset-code', [SettingsController::class, 'resendResetCode'])->name('settings.reset.resend');
    Route::post('/settings/verify-reset', [SettingsController::class, 'verifyAndReset'])->name('settings.reset.verify');
    Route::post('/settings/cancel-reset', [SettingsController::class, 'cancelReset'])->name('settings.reset.cancel');

    // Notifications — shared by the admin side and the patient side
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('/notifications/{id}/details', [NotificationController::class, 'details'])->whereNumber('id')->name('notifications.details');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
});


/*
|--------------------------------------------------------------------------
| Clinic staff / dentist / super admin
|--------------------------------------------------------------------------
*/
Route::middleware('admin')->group(function () {

    // Dashboard + reports
    Route::get('dashboard', [AdminController::class, 'showDashboard'])->name('dashboard');
    Route::get('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');

    // Walk-in appointments
    Route::get('walk-in', [WalkInController::class, 'index'])->name('walkIn');
    Route::get('follow-up', [WalkInController::class, 'followUp'])->name('followUp');
    Route::get('/walk-in/search-patient', [WalkInController::class, 'searchPatient'])->name('walkIn.search');
    Route::post('/walk-in', [WalkInController::class, 'store'])->name('walkIn.store');

    // Dentist schedule
    Route::get('/dentist-schedule', [DentistScheduleController::class, 'index'])->name('dentistSchedule');
    Route::post('/dentist-schedule/toggle', [DentistScheduleController::class, 'toggle'])->name('dentistSchedule.toggle');
    Route::post('/dentist-schedule/toggle-day', [DentistScheduleController::class, 'toggleDay'])->name('dentistSchedule.toggleDay');

    // Appointment approval
    Route::get('/appointment-approval', [AppointmentApprovalController::class, 'index'])->name('appointmentApproval');
    Route::post('/appointment-approval/{id}/approve', [AppointmentApprovalController::class, 'approve'])->name('appointmentApproval.approve');
    Route::post('/appointment-approval/{id}/decline', [AppointmentApprovalController::class, 'decline'])->name('appointmentApproval.decline');

    // Appointments management
    Route::get('/appointments', [AppointmentsController::class, 'index'])->name('appointments');
    Route::post('/appointments/{id}/complete', [AppointmentsController::class, 'complete'])->name('appointments.complete');
    Route::post('/appointments/{id}/cancel', [AppointmentsController::class, 'cancel'])->name('appointments.cancel');

    // Reschedule (page reached from the Reschedule buttons; not in the menu)
    Route::get('/appointments/{id}/reschedule', [RescheduleController::class, 'staffPage'])->whereNumber('id')->name('appointments.reschedule');
    Route::post('/appointments/{id}/reschedule', [RescheduleController::class, 'staffPropose'])->whereNumber('id')->name('appointments.reschedule.propose');
    Route::post('/reschedules/{id}/withdraw', [RescheduleController::class, 'staffWithdraw'])->whereNumber('id')->name('reschedules.withdraw');

    // User (patient) accounts
    Route::get('/user-accounts', [UserAccountController::class, 'index'])->name('userAcc');
    Route::post('/user-accounts/{id}/update', [UserAccountController::class, 'update'])->name('userAcc.update');
    Route::post('/user-accounts/{id}/archive', [UserAccountController::class, 'archive'])->name('userAcc.archive');
    Route::post('/user-accounts/{id}/unarchive', [UserAccountController::class, 'unarchive'])->name('userAcc.unarchive');

    // Patient records
    Route::get('/patient-records', [PatientRecordsController::class, 'index'])->name('patientRecords');
    Route::get('/patient-records/patient/{patientId}', [PatientRecordsController::class, 'history'])->whereNumber('patientId')->name('patientRecords.history');
    Route::post('/patient-records/patient/{patientId}/update', [PatientRecordsController::class, 'updatePatient'])->whereNumber('patientId')->name('patientRecords.patient.update');
    Route::post('/patient-records/patient/{patientId}/medical-history', [PatientRecordsController::class, 'updateMedical'])->whereNumber('patientId')->name('patientRecords.medical.update');
    Route::post('/patient-records/patient/{patientId}/archive', [PatientRecordsController::class, 'archivePatient'])->whereNumber('patientId')->name('patientRecords.patient.archive');
    Route::post('/patient-records/patient/{patientId}/unarchive', [PatientRecordsController::class, 'unarchivePatient'])->whereNumber('patientId')->name('patientRecords.patient.unarchive');
    Route::get('/patient-records/{id}/print', [PatientRecordsController::class, 'printRecord'])->whereNumber('id')->name('patientRecords.print');
    Route::post('/patient-records/{id}/update', [PatientRecordsController::class, 'updateNotes'])->name('patientRecords.update');
    Route::post('/patient-records/{id}/odontogram', [OdontogramController::class, 'save'])->name('patientRecords.odontogram.save');

    // Staff self-service profile (view info, verify email, change password)
    Route::get('/staff-profile', [StaffProfileController::class, 'edit'])->name('staffProfile');
    Route::post('/staff-profile/update', [StaffProfileController::class, 'updateProfile'])->name('staffProfile.updateProfile');
    Route::post('/staff-profile/send-verification', [StaffProfileController::class, 'sendVerification'])->name('staffProfile.sendVerification');
    Route::post('/staff-profile/verify-email', [StaffProfileController::class, 'verifyEmail'])->name('staffProfile.verifyEmail');
    Route::post('/staff-profile/update-password', [StaffProfileController::class, 'updatePassword'])->name('staffProfile.password.update');

    /*
    |----------------------------------------------------------------------
    | One-time super admin setup — a session logged in with the .env
    | bootstrap credentials is locked to these routes by
    | EnsureSuperAdminClaimed until it turns itself into a real account
    | (pick an email + password, then confirm the email with a code).
    | Sits in the 'admin' group (the bootstrap session has user_role=admin)
    | but NOT the nested 'superadmin' group (it has no is_super_admin flag).
    |----------------------------------------------------------------------
    */
    Route::get('/super-admin/setup', [SuperAdminSetupController::class, 'show'])->name('superAdminSetup');
    Route::post('/super-admin/setup/send-code', [SuperAdminSetupController::class, 'sendCode'])->name('superAdminSetup.sendCode');
    Route::post('/super-admin/setup/resend-code', [SuperAdminSetupController::class, 'resendCode'])->name('superAdminSetup.resendCode');
    Route::post('/super-admin/setup/verify', [SuperAdminSetupController::class, 'verify'])->name('superAdminSetup.verify');

    // Release a claimed super admin account back to the .env bootstrap
    // (Admin Profile → Security) — email-code confirmed, same as setup.
    // Guarded in-controller to session('is_super_admin').
    Route::post('/super-admin/release/send-code', [SuperAdminSetupController::class, 'sendReleaseCode'])->name('superAdminRelease.sendCode');
    Route::post('/super-admin/release/verify', [SuperAdminSetupController::class, 'verifyRelease'])->name('superAdminRelease.verify');

    /*
    |----------------------------------------------------------------------
    | Super admin only — Staff Accounts + Configuration → Activity Logs
    |----------------------------------------------------------------------
    */
    Route::middleware('superadmin')->group(function () {

        // Staff accounts
        Route::get('/staff-accounts', [StaffAccountController::class, 'index'])->name('staffAcc');
        Route::post('/staff-accounts', [StaffAccountController::class, 'store'])->name('staffAcc.store');
        Route::post('/staff-accounts/{id}/update', [StaffAccountController::class, 'update'])->name('staffAcc.update');
        Route::post('/staff-accounts/{id}/archive', [StaffAccountController::class, 'archive'])->name('staffAcc.archive');
        Route::post('/staff-accounts/{id}/unarchive', [StaffAccountController::class, 'unarchive'])->name('staffAcc.unarchive');
        Route::post('/staff-accounts/{id}/password/update', [StaffAccountController::class, 'updatePassword'])->name('staffAcc.password.update');

        // Configuration → Activity Logs
        Route::post('/configuration/activity-logs/{id}/archive', [ConfigurationController::class, 'archiveActivityLog'])->name('configuration.activityLogs.archive');
        Route::post('/configuration/activity-logs/{id}/unarchive', [ConfigurationController::class, 'unarchiveActivityLog'])->name('configuration.activityLogs.unarchive');

        // Configuration → Backup & Restore
        Route::post('/configuration/backups', [BackupController::class, 'store'])->name('configuration.backups.store');
        Route::post('/configuration/backups/restore-upload', [BackupController::class, 'restoreUpload'])->name('configuration.backups.restoreUpload');
        Route::get('/configuration/backups/{name}/download', [BackupController::class, 'download'])->name('configuration.backups.download');
        Route::post('/configuration/backups/{name}/restore', [BackupController::class, 'restore'])->name('configuration.backups.restore');
        Route::post('/configuration/backups/{name}/delete', [BackupController::class, 'destroy'])->name('configuration.backups.destroy');
    });

    /*
    |----------------------------------------------------------------------
    | Configuration — the super admin and Staff-role accounts (never a
    | Dentist): branding, closed dates/weekdays, services, categories,
    | legal content, appointment steps
    |----------------------------------------------------------------------
    */
    Route::middleware('config.access')->group(function () {
        Route::get('/configuration', [ConfigurationController::class, 'index'])->name('configuration');
        Route::post('/configuration/services', [ConfigurationController::class, 'storeService'])->name('configuration.services.store');
        Route::post('/configuration/services/{id}/update', [ConfigurationController::class, 'updateService'])->name('configuration.services.update');
        Route::post('/configuration/services/{id}/archive', [ConfigurationController::class, 'archiveService'])->name('configuration.services.archive');
        Route::post('/configuration/services/{id}/unarchive', [ConfigurationController::class, 'unarchiveService'])->name('configuration.services.unarchive');
        Route::post('/configuration/categories', [ConfigurationController::class, 'storeCategory'])->name('configuration.categories.store');
        Route::post('/configuration/categories/{id}/update', [ConfigurationController::class, 'updateCategory'])->name('configuration.categories.update');
        Route::post('/configuration/categories/{id}/archive', [ConfigurationController::class, 'archiveCategory'])->name('configuration.categories.archive');
        Route::post('/configuration/categories/{id}/unarchive', [ConfigurationController::class, 'unarchiveCategory'])->name('configuration.categories.unarchive');
        Route::post('/configuration/about', [ConfigurationController::class, 'updateAboutInfo'])->name('configuration.about.update');
        Route::post('/configuration/privacy-legal', [ConfigurationController::class, 'updatePrivacyLegal'])->name('configuration.privacyLegal.update');
        Route::post('/configuration/appointment-steps', [ConfigurationController::class, 'storeAppointmentStep'])->name('configuration.appointmentSteps.store');
        Route::post('/configuration/appointment-steps/{id}/update', [ConfigurationController::class, 'updateAppointmentStep'])->name('configuration.appointmentSteps.update');
        Route::post('/configuration/appointment-steps/{id}/archive', [ConfigurationController::class, 'archiveAppointmentStep'])->name('configuration.appointmentSteps.archive');
        Route::post('/configuration/appointment-steps/{id}/unarchive', [ConfigurationController::class, 'unarchiveAppointmentStep'])->name('configuration.appointmentSteps.unarchive');
        Route::post('/configuration/closed-dates', [ClosedDateController::class, 'store'])->name('configuration.closedDates.store');
        Route::post('/configuration/closed-dates/{id}/update', [ClosedDateController::class, 'update'])->name('configuration.closedDates.update');
        Route::post('/configuration/closed-dates/{id}/archive', [ClosedDateController::class, 'archive'])->name('configuration.closedDates.archive');
        Route::post('/configuration/closed-dates/{id}/unarchive', [ClosedDateController::class, 'unarchive'])->name('configuration.closedDates.unarchive');
        Route::post('/configuration/closed-weekdays', [ClosedDateController::class, 'updateWeekdays'])->name('configuration.closedWeekdays.update');
    });
});
