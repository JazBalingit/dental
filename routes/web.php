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

Route::get('/', function () {
    return view('welcome');
});
// show log in front end
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
// show sign up front end
Route::get('signup', [AuthController::class, 'showSignup'])->name('signup');

// show all super admin front end
Route::get('dashboard', [AdminController::class, 'showDashboard'])->name('dashboard');
Route::get('staff-accounts', [AdminController::class, 'showStaffAcc'])->name('staffAcc');
Route::get('dentist-schedule', [AdminController::class, 'showDentistSchedule'])->name('dentistSchedule');
Route::get('walk-in', [AdminController::class, 'showWalkIn'])->name('walkIn');
Route::get('appointments', [AdminController::class, 'showAppointments'])->name('appointments');
Route::get('patient-records', [AdminController::class, 'showPatientRecords'])->name('patientRecords');
Route::get('configuration', [AdminController::class, 'showConfiguration'])->name('configuration');

// show all user side front end
Route::get('landing-page', [UserController::class, 'showLandingPage'])->name('landingPage');
Route::get('settings', [UserController::class, 'showSettings'])->name('settings');
Route::get('user-appointment', [UserController::class, 'showUserAppointment'])->name('userAppointment');
Route::post('/user-appointment/{appointment}/remove', [UserController::class, 'removeAppointment'])->name('userAppointment.remove');

// dentist schedule backend route
Route::get('/dentist-schedule', [DentistScheduleController::class, 'index'])->name('dentistSchedule');
Route::post('/dentist-schedule/toggle', [DentistScheduleController::class, 'toggle'])->name('dentistSchedule.toggle');

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

// ---- Forgot / Reset Password (modal lives on the login page, no separate GET page) ----
Route::post('/forgot-password', [LoginController::class, 'sendResetCode'])->name('password.email');
Route::post('/forgot-password/resend', [LoginController::class, 'resendResetCode'])->name('password.resend');
Route::post('/reset-password', [LoginController::class, 'resetPassword'])->name('password.update');
Route::post('/forgot-password/cancel', [LoginController::class, 'cancelReset'])->name('password.cancel');

// ---- Profile (patient edits their own info; UserID/DateCreated/Email are never editable) ----
Route::get('/profile', [ProfileController::class, 'edit'])->name('userProfile');
Route::post('/profile', [ProfileController::class, 'update'])->name('userProfile.update');

// ---- Settings / Change Password ----
Route::get('/settings', [SettingsController::class, 'edit'])->name('settings');
Route::post('/settings/update-password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
Route::post('/settings/send-reset-code', [SettingsController::class, 'sendResetCode'])->name('settings.reset.send');
Route::post('/settings/resend-reset-code', [SettingsController::class, 'resendResetCode'])->name('settings.reset.resend');
Route::post('/settings/verify-reset', [SettingsController::class, 'verifyAndReset'])->name('settings.reset.verify');
Route::post('/settings/cancel-reset', [SettingsController::class, 'cancelReset'])->name('settings.reset.cancel');

//---- Appointment Booking / Approval ----
Route::get('/appointment-approval', [AppointmentApprovalController::class, 'index'])->name('appointmentApproval');
Route::post('/appointment-approval/{id}/approve', [AppointmentApprovalController::class, 'approve'])->name('appointmentApproval.approve');
Route::post('/appointment-approval/{id}/decline', [AppointmentApprovalController::class, 'decline'])->name('appointmentApproval.decline');

Route::post('/book-appointment', [AppointmentBookingController::class, 'store'])->name('booking.store');

// staff account backend route
Route::get('/staff-accounts', [StaffAccountController::class, 'index'])->name('staffAcc');
Route::post('/staff-accounts', [StaffAccountController::class, 'store'])->name('staffAcc.store');
Route::post('/staff-accounts/{id}/update', [StaffAccountController::class, 'update'])->name('staffAcc.update');
Route::post('/staff-accounts/{id}/archive', [StaffAccountController::class, 'archive'])->name('staffAcc.archive');
Route::post('/staff-accounts/{id}/unarchive', [StaffAccountController::class, 'unarchive'])->name('staffAcc.unarchive');

// user accounts backend route (patients only — no create here, they sign up themselves)
Route::get('/user-accounts', [UserAccountController::class, 'index'])->name('userAcc');
Route::post('/user-accounts/{id}/update', [UserAccountController::class, 'update'])->name('userAcc.update');
Route::post('/user-accounts/{id}/archive', [UserAccountController::class, 'archive'])->name('userAcc.archive');
Route::post('/user-accounts/{id}/unarchive', [UserAccountController::class, 'unarchive'])->name('userAcc.unarchive');

// staff user profile (staff self-service: view info, verify email, change password)
Route::get('/staff-profile', [StaffProfileController::class, 'edit'])->name('staffProfile');
Route::post('/staff-profile/send-verification', [StaffProfileController::class, 'sendVerification'])->name('staffProfile.sendVerification');
Route::post('/staff-profile/verify-email', [StaffProfileController::class, 'verifyEmail'])->name('staffProfile.verifyEmail');
Route::post('/staff-profile/update-password', [StaffProfileController::class, 'updatePassword'])->name('staffProfile.password.update');