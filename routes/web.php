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
use App\Http\Controllers\UserAccountController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AppointmentsController;
use App\Http\Controllers\PatientRecordsController;
use App\Http\Controllers\OdontogramController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\WalkInController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ContactController;

/*
|--------------------------------------------------------------------------
| Access control lives here, on the routes — not in each controller.
|   (no middleware)   public pages + the login / signup / OTP flows
|   auth.session      any signed-in user (patients + admins)
|   admin             session('user_role') === 'admin' (staff, dentist, super admin)
|   admin + superadmin  super admin only (Staff Accounts, Configuration)
| EnsureStaffIsVerified (global) still forces an unverified staff account
| onto the verification screen regardless of the above.
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// ---- Public pages ----
Route::get('landing-page', [UserController::class, 'showLandingPage'])->name('landingPage');

// Landing-page "Contact Us" form — open to guests and signed-in patients alike.
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');

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

    // Patient — my appointments
    Route::get('user-appointment', [UserController::class, 'showUserAppointment'])->name('userAppointment');
    Route::post('/user-appointment/{appointment}/remove', [UserController::class, 'removeAppointment'])->name('userAppointment.remove');

    // Patient — my dental records (read-only view of my own completed visits)
    Route::get('/my-records', [PatientRecordsController::class, 'mine'])->name('myRecords');

    // Patient — book an appointment
    Route::post('/book-appointment', [AppointmentBookingController::class, 'store'])->name('booking.store');

    // Profile (patient edits their own info; UserID / DateCreated / Email are never editable)
    Route::post('/profile', [ProfileController::class, 'update'])->name('userProfile.update');

    // Settings / Change Password (+ the "Configuration" activity-trail tab: /settings?tab=configuration)
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings');
    Route::post('/settings/update-password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::post('/settings/send-reset-code', [SettingsController::class, 'sendResetCode'])->name('settings.reset.send');
    Route::post('/settings/resend-reset-code', [SettingsController::class, 'resendResetCode'])->name('settings.reset.resend');
    Route::post('/settings/verify-reset', [SettingsController::class, 'verifyAndReset'])->name('settings.reset.verify');
    Route::post('/settings/cancel-reset', [SettingsController::class, 'cancelReset'])->name('settings.reset.cancel');

    // Notifications — shared by the admin side and the patient side
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
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

    // User (patient) accounts
    Route::get('/user-accounts', [UserAccountController::class, 'index'])->name('userAcc');
    Route::post('/user-accounts/{id}/update', [UserAccountController::class, 'update'])->name('userAcc.update');
    Route::post('/user-accounts/{id}/archive', [UserAccountController::class, 'archive'])->name('userAcc.archive');
    Route::post('/user-accounts/{id}/unarchive', [UserAccountController::class, 'unarchive'])->name('userAcc.unarchive');

    // Patient records
    Route::get('/patient-records', [PatientRecordsController::class, 'index'])->name('patientRecords');
    Route::post('/patient-records/{id}/update', [PatientRecordsController::class, 'updateNotes'])->name('patientRecords.update');
    Route::post('/patient-records/{id}/archive', [PatientRecordsController::class, 'archive'])->name('patientRecords.archive');
    Route::post('/patient-records/{id}/unarchive', [PatientRecordsController::class, 'unarchive'])->name('patientRecords.unarchive');
    Route::post('/patient-records/{id}/odontogram', [OdontogramController::class, 'save'])->name('patientRecords.odontogram.save');

    // Staff self-service profile (view info, verify email, change password)
    Route::get('/staff-profile', [StaffProfileController::class, 'edit'])->name('staffProfile');
    Route::post('/staff-profile/send-verification', [StaffProfileController::class, 'sendVerification'])->name('staffProfile.sendVerification');
    Route::post('/staff-profile/verify-email', [StaffProfileController::class, 'verifyEmail'])->name('staffProfile.verifyEmail');
    Route::post('/staff-profile/update-password', [StaffProfileController::class, 'updatePassword'])->name('staffProfile.password.update');

    /*
    |----------------------------------------------------------------------
    | Super admin only — Staff Accounts + Configuration
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

        // Configuration: branding, services, categories, legal content, appointment steps, activity logs
        Route::get('/configuration', [ConfigurationController::class, 'index'])->name('configuration');
        Route::post('/configuration/services', [ConfigurationController::class, 'storeService'])->name('configuration.services.store');
        Route::post('/configuration/services/{id}/update', [ConfigurationController::class, 'updateService'])->name('configuration.services.update');
        Route::post('/configuration/services/{id}/archive', [ConfigurationController::class, 'archiveService'])->name('configuration.services.archive');
        Route::post('/configuration/services/{id}/unarchive', [ConfigurationController::class, 'unarchiveService'])->name('configuration.services.unarchive');
        Route::post('/configuration/categories', [ConfigurationController::class, 'storeCategory'])->name('configuration.categories.store');
        Route::post('/configuration/categories/{id}/update', [ConfigurationController::class, 'updateCategory'])->name('configuration.categories.update');
        Route::post('/configuration/categories/{id}/delete', [ConfigurationController::class, 'destroyCategory'])->name('configuration.categories.destroy');
        Route::post('/configuration/activity-logs/{id}/archive', [ConfigurationController::class, 'archiveActivityLog'])->name('configuration.activityLogs.archive');
        Route::post('/configuration/activity-logs/{id}/unarchive', [ConfigurationController::class, 'unarchiveActivityLog'])->name('configuration.activityLogs.unarchive');
        Route::post('/configuration/about', [ConfigurationController::class, 'updateAboutInfo'])->name('configuration.about.update');
        Route::post('/configuration/privacy-legal', [ConfigurationController::class, 'updatePrivacyLegal'])->name('configuration.privacyLegal.update');
        Route::post('/configuration/appointment-steps', [ConfigurationController::class, 'updateAppointmentSteps'])->name('configuration.appointmentSteps.update');
    });
});
