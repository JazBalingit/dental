<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesArchiveReason;
use App\Models\Appointment;
use App\Models\ClosedDate;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClosedDateController extends Controller
{
    use HandlesArchiveReason;

    public function __construct(
        protected ActivityLogService $activityLog,
        protected NotificationService $notifications
    ) {
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $date = Carbon::parse($data['date']);

        if (ClosedDate::isClosed($date)) {
            return back()->withInput()->with('error', 'That date is already closed.');
        }

        DB::beginTransaction();
        try {
            $cancelledCount = $this->cancelAppointmentsOn($data['date'], $data['reason'] ?? null);

            $closedDate = ClosedDate::create([
                'Date' => $data['date'],
                'Reason' => $data['reason'] ?? null,
                'ClosedBy' => session('user_id'),
                'IsArchived' => false,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Could not close this date. Please try again.');
        }

        ClosedDate::flushCache();
        $this->notifyIfCancelled($cancelledCount, $closedDate->Date);

        $this->activityLog->log(
            'Create',
            "Closed the clinic on {$closedDate->Date->format('F j, Y')}."
                . ($data['reason'] ?? null ? " Reason: {$data['reason']}." : '')
                . ($cancelledCount > 0 ? " Cancelled {$cancelledCount} appointment(s)." : '')
        );

        $message = 'Date closed.' . ($cancelledCount > 0 ? " {$cancelledCount} appointment(s) were cancelled and the patient(s) notified." : '');

        return back()->with('success', $message);
    }

    public function update(Request $request, $id)
    {
        $closedDate = ClosedDate::where('IsArchived', false)->findOrFail($id);

        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $dateChanged = $data['date'] !== $closedDate->Date->format('Y-m-d');

        if ($dateChanged && ClosedDate::isClosed(Carbon::parse($data['date']))) {
            return back()->withInput()->with('error', 'That date is already closed.');
        }

        $cancelledCount = 0;

        DB::beginTransaction();
        try {
            // Moved to a new date — that date needs the same "clear it out"
            // guarantee a brand-new closure gets, or existing bookings on
            // the newly-closed date would silently survive.
            if ($dateChanged) {
                $cancelledCount = $this->cancelAppointmentsOn($data['date'], $data['reason'] ?? null);
            }

            $closedDate->update([
                'Date' => $data['date'],
                'Reason' => $data['reason'] ?? null,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Could not update this closed date. Please try again.');
        }

        ClosedDate::flushCache();
        $this->notifyIfCancelled($cancelledCount, $closedDate->Date);

        $this->activityLog->log('Edit', "Edited closed date: now {$closedDate->Date->format('F j, Y')}."
            . ($cancelledCount > 0 ? " Cancelled {$cancelledCount} appointment(s)." : ''));

        $message = 'Closed date updated.' . ($cancelledCount > 0 ? " {$cancelledCount} appointment(s) on the new date were cancelled and the patient(s) notified." : '');

        return back()->with('success', $message);
    }

    /** "Open This Date" — reopens it for booking. */
    public function archive(Request $request, $id)
    {
        $reason = $this->archiveReason($request);
        $closedDate = ClosedDate::where('IsArchived', false)->findOrFail($id);
        $closedDate->update($this->archivedState($reason));

        ClosedDate::flushCache();

        $this->activityLog->log('Archive', "Reopened {$closedDate->Date->format('F j, Y')} for booking. Reason: {$reason}.");

        return back()->with('success', 'Date reopened — it can be booked again.');
    }

    /** "Close Again" — re-closes a previously-reopened date, from the Archived tab. */
    public function unarchive($id)
    {
        $closedDate = ClosedDate::where('IsArchived', true)->findOrFail($id);

        if (ClosedDate::isClosed($closedDate->Date)) {
            return back()->with('error', 'That date is already closed by another entry.');
        }

        $cancelledCount = 0;

        DB::beginTransaction();
        try {
            $cancelledCount = $this->cancelAppointmentsOn($closedDate->Date->format('Y-m-d'), $closedDate->Reason);

            $closedDate->update($this->restoredState());

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Could not close this date. Please try again.');
        }

        ClosedDate::flushCache();
        $this->notifyIfCancelled($cancelledCount, $closedDate->Date);

        $this->activityLog->log('Unarchive', "Closed {$closedDate->Date->format('F j, Y')} again."
            . ($cancelledCount > 0 ? " Cancelled {$cancelledCount} appointment(s)." : ''));

        return back()->with('success', 'Date closed again.' . ($cancelledCount > 0 ? " {$cancelledCount} appointment(s) were cancelled and the patient(s) notified." : ''));
    }

    /**
     * Cancels every Pending/Approved appointment (any dentist) on $date and
     * notifies each patient — the same guarantee DentistScheduleController::
     * toggleDay() gives a single dentist's closed day, applied clinic-wide.
     * MUST be called inside a DB transaction (every caller already opens one).
     */
    private function cancelAppointmentsOn(string $date, ?string $reason): int
    {
        $affected = Appointment::with('patientInfo.userAccount')
            ->whereDate('AppointmentDate', $date)
            ->whereIn('Status', ['Pending', 'Approved'])
            ->get();

        $dateLabel = Carbon::parse($date)->format('F j, Y');
        $cancelledCount = 0;

        foreach ($affected as $appointment) {
            $appointment->Status = 'Cancelled';
            $appointment->DeclineReason = 'The clinic is closed on this date' . ($reason ? " ({$reason})" : '') . '.';
            $appointment->save();
            $cancelledCount++;

            $timeLabel = Carbon::createFromFormat('H:i', $appointment->AppointmentTime)->format('g:i A');
            $patientUser = $appointment->patientInfo->userAccount ?? null;

            if ($patientUser) {
                $this->notifications->notifyUser(
                    $patientUser,
                    'Appointment Cancelled — Clinic Closed',
                    "We regret to inform you that your appointment on {$dateLabel} at {$timeLabel} has been cancelled, as the clinic will be closed that day. Please book another date at your convenience.",
                    'danger',
                    $appointment->AppointmentID,
                    'Cancelled'
                );
            }
        }

        return $cancelledCount;
    }

    private function notifyIfCancelled(int $cancelledCount, Carbon $date): void
    {
        if ($cancelledCount === 0) {
            return;
        }

        $this->notifications->notifyAdmins(
            'Appointments Cancelled — Clinic Closed',
            "{$cancelledCount} appointment(s) on {$date->format('F j, Y')} were cancelled because the clinic was closed for that date.",
            'warning'
        );
    }
}
