<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsBookingCalendar;
use App\Models\Appointment;
use App\Models\RescheduleRequest;
use App\Models\Service;
use App\Models\UserAccount;
use App\Services\RescheduleService;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Reschedule pages (not in the menu — reached from the Reschedule buttons)
 * and the patient's answers to a staff proposal. All slot handling lives in
 * App\Services\RescheduleService.
 */
class RescheduleController extends Controller
{
    use BuildsBookingCalendar;

    public function __construct(protected RescheduleService $reschedules)
    {
    }

    // ---------------- Staff ----------------

    /** /appointments/{id}/reschedule — staff pick the new time to propose. */
    public function staffPage(Request $request, $id)
    {
        $appointment = $this->staffAppointment($id);
        if (!$appointment->can_reschedule) {
            return redirect()->back()->with('error', 'This appointment can no longer be rescheduled — it has already started, or its status changed.');
        }

        if (!$request->has('dentist')) {
            $request->query->set('dentist', $appointment->DentistID);
        }
        if (!$request->has('bookMonth')) {
            $request->query->set('bookMonth', $appointment->AppointmentDate->format('Y-m'));
        }

        return view('appointments.reschedule-staff', array_merge($this->calendarData($request), [
            'appointment' => $appointment,
            'returnTo' => $this->returnTo($request),
            // Show the appointment being moved as "current" on the calendar.
            'bookCurrentPatientId' => $appointment->PatientID,
        ]));
    }

    public function staffPropose(Request $request, $id)
    {
        $appointment = $this->staffAppointment($id);
        $data = $this->validateSlot($request, true);

        try {
            $proposal = $this->reschedules->propose($appointment, (int) $data['dentist_id'], $data['date'], $data['time'], $data['service_ids'], $data['reason'] ?? null);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('booking_error', $e->getMessage());
        }

        return redirect($this->returnTo($request))
            ->with('success', "New time proposed: {$proposal->when_label}. The patient has been notified and has until {$proposal->ExpiresAt->format('M j, g:i A')} to respond.");
    }

    public function staffWithdraw(Request $request, $rescheduleId)
    {
        $proposal = RescheduleRequest::findOrFail($rescheduleId);

        try {
            $this->reschedules->withdraw($proposal);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Proposed time withdrawn — the appointment keeps its original time.');
    }

    // ---------------- Patient ----------------

    public function patientAccept($rescheduleId)
    {
        $proposal = $this->patientProposal($rescheduleId);

        try {
            $appointment = $this->reschedules->accept($proposal);
        } catch (RuntimeException $e) {
            return redirect()->route('userAppointment')->with('error', $e->getMessage());
        }

        return redirect()->route('userAppointment')
            ->with('success', "You're all set — your appointment is now on {$appointment->AppointmentDate->format('F j, Y')}, {$appointment->time_range_label}.");
    }

    public function patientDecline($rescheduleId)
    {
        $proposal = $this->patientProposal($rescheduleId);

        try {
            $this->reschedules->decline($proposal);
        } catch (RuntimeException $e) {
            return redirect()->route('userAppointment')->with('error', $e->getMessage());
        }

        return redirect()->route('userAppointment')->with('success', 'Got it — you kept your original appointment time. The clinic has been told.');
    }

    /** /user-appointment/{appointment}/reschedule — the patient picks their own new time. */
    public function patientPage(Request $request, $appointmentId)
    {
        $appointment = $this->patientAppointment($appointmentId);
        if (!$appointment->can_reschedule) {
            return redirect()->route('userAppointment')->with('error', 'This appointment can no longer be rescheduled.');
        }

        if (!$request->has('dentist')) {
            $request->query->set('dentist', $appointment->DentistID);
        }
        if (!$request->has('bookMonth')) {
            $request->query->set('bookMonth', $appointment->AppointmentDate->format('Y-m'));
        }

        return view('users.user-appointment-reschedule', array_merge($this->calendarData($request), [
            'appointment' => $appointment,
        ]));
    }

    public function patientMove(Request $request, $appointmentId)
    {
        $appointment = $this->patientAppointment($appointmentId);
        $data = $this->validateSlot($request, false);

        try {
            $appointment = $this->reschedules->patientMove($appointment, (int) $data['dentist_id'], $data['date'], $data['time'], $data['service_ids']);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('booking_error', $e->getMessage());
        }

        return redirect()->route('userAppointment')
            ->with('success', "Your appointment moved to {$appointment->AppointmentDate->format('F j, Y')}, {$appointment->time_range_label}. The clinic will review and approve it.");
    }

    // ---------------- Helpers ----------------

    protected function validateSlot(Request $request, bool $withReason): array
    {
        return $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
            'dentist_id' => 'required|integer',
            'service_ids' => 'required|array|min:1|max:' . Service::MAX_PER_APPOINTMENT,
            'service_ids.*' => 'distinct|integer|exists:tbl_services,ServiceID',
            'reason' => $withReason ? 'nullable|string|max:500' : 'prohibited',
        ], [
            'service_ids.max' => 'You can choose up to ' . Service::MAX_PER_APPOINTMENT . ' services per appointment.',
        ]);
    }

    protected function staffAppointment($id): Appointment
    {
        return Appointment::with(['patientInfo.userAccount', 'service', 'services', 'dentist.staffInfo', 'pendingReschedule'])->findOrFail($id);
    }

    /** The signed-in patient's own appointment — or one they booked for someone else — or 404. */
    protected function patientAppointment($id): Appointment
    {
        $user = UserAccount::with('patientInfo')->findOrFail(session('user_id'));
        $appointment = Appointment::with(['patientInfo.userAccount', 'service', 'services', 'dentist.staffInfo', 'pendingReschedule'])
            ->findOrFail($id);
        abort_unless($appointment->isManagedBy($user), 404);

        return $appointment;
    }

    protected function patientProposal($id): RescheduleRequest
    {
        $user = UserAccount::with('patientInfo')->findOrFail(session('user_id'));
        $proposal = RescheduleRequest::with('appointment.patientInfo')->findOrFail($id);
        abort_unless($proposal->appointment?->isManagedBy($user), 404);

        return $proposal;
    }

    /** Back to Appointments or Appointment Requests — wherever staff came from. */
    protected function returnTo(Request $request): string
    {
        return $request->input('from') === 'requests' || $request->query('from') === 'requests'
            ? route('appointmentApproval')
            : route('appointments');
    }
}
