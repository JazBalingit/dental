<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Service;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class ConfigurationController extends Controller
{
    public function __construct(protected AuditLogService $auditLog)
    {
    }

    protected array $actionTypes = ['Create', 'Edit', 'Archive', 'Unarchive', 'Approve', 'Decline', 'Complete', 'Cancel'];

    protected function guard()
    {
        if (!session('user_id') || session('user_role') !== 'admin') {
            return redirect()->route('login')->with('login_error', 'Please log in as an administrator to continue.');
        }

        return null;
    }

    public function index(Request $request)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $serviceSearch = $request->query('serviceSearch');
        $activitySearch = $request->query('activitySearch');
        $auditSearch = $request->query('auditSearch');
        $auditType = $request->query('auditType');

        $activeServices = Service::where('IsArchived', false);
        $archivedServices = Service::where('IsArchived', true);
        if ($serviceSearch) {
            $filter = function ($q) use ($serviceSearch) {
                $q->where('ServiceName', 'like', "%{$serviceSearch}%")
                    ->orWhere('Description', 'like', "%{$serviceSearch}%");
            };
            $activeServices->where($filter);
            $archivedServices->where($filter);
        }

        $activeActivity = ActivityLog::with(['userAccount.patientInfo', 'userAccount.staffInfo'])->where('IsArchived', false);
        $archivedActivity = ActivityLog::with(['userAccount.patientInfo', 'userAccount.staffInfo'])->where('IsArchived', true);
        if ($activitySearch) {
            $filter = function ($q) use ($activitySearch) {
                $q->whereHas('userAccount', function ($u) use ($activitySearch) {
                    $u->where('Email', 'like', "%{$activitySearch}%")
                        ->orWhereHas('patientInfo', function ($p) use ($activitySearch) {
                            $p->where('FirstName', 'like', "%{$activitySearch}%")->orWhere('LastName', 'like', "%{$activitySearch}%");
                        })
                        ->orWhereHas('staffInfo', function ($s) use ($activitySearch) {
                            $s->where('FirstName', 'like', "%{$activitySearch}%")->orWhere('LastName', 'like', "%{$activitySearch}%");
                        });
                });
            };
            $activeActivity->where($filter);
            $archivedActivity->where($filter);
        }

        $activeAudit = AuditLog::with('staffAccount.staffInfo')->where('IsArchived', false);
        $archivedAudit = AuditLog::with('staffAccount.staffInfo')->where('IsArchived', true);
        if ($auditType) {
            $activeAudit->where('ActionType', $auditType);
            $archivedAudit->where('ActionType', $auditType);
        }
        if ($auditSearch) {
            $filter = function ($q) use ($auditSearch) {
                $q->where('Description', 'like', "%{$auditSearch}%")
                    ->orWhereHas('staffAccount.staffInfo', function ($s) use ($auditSearch) {
                        $s->where('FirstName', 'like', "%{$auditSearch}%")->orWhere('LastName', 'like', "%{$auditSearch}%");
                    });
            };
            $activeAudit->where($filter);
            $archivedAudit->where($filter);
        }

        $logoPath = public_path('images/puspus_logo.png');

        return view('superAdmin.configuration', [
            'services' => $activeServices->orderBy('ServiceName')->paginate(10, ['*'], 'services_page')->withQueryString(),
            'archivedServices' => $archivedServices->orderBy('ServiceName')->paginate(10, ['*'], 'services_archived_page')->withQueryString(),
            'activityLogs' => $activeActivity->orderByDesc('LoggedInTime')->paginate(10, ['*'], 'activity_page')->withQueryString(),
            'archivedActivityLogs' => $archivedActivity->orderByDesc('LoggedInTime')->paginate(10, ['*'], 'activity_archived_page')->withQueryString(),
            'auditLogs' => $activeAudit->orderByDesc('created_at')->paginate(10, ['*'], 'audit_page')->withQueryString(),
            'archivedAuditLogs' => $archivedAudit->orderByDesc('created_at')->paginate(10, ['*'], 'audit_archived_page')->withQueryString(),
            'servicesTab' => $request->query('servicesTab') === 'archived' ? 'archived' : 'active',
            'activityTab' => $request->query('activityTab') === 'archived' ? 'archived' : 'active',
            'auditTab' => $request->query('auditTab') === 'archived' ? 'archived' : 'active',
            'serviceSearch' => $serviceSearch,
            'activitySearch' => $activitySearch,
            'auditSearch' => $auditSearch,
            'auditType' => $auditType,
            'actionTypes' => $this->actionTypes,
            'logoVersion' => file_exists($logoPath) ? filemtime($logoPath) : time(),
        ]);
    }

    public function updateLogo(Request $request)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $request->validate([
            'logo' => 'required|file|mimes:jpg,jpeg,png,svg|max:2048',
        ]);

        $request->file('logo')->move(public_path('images'), 'puspus_logo.png');

        $this->auditLog->log('Edit', 'Changed the system logo.');

        return redirect()->route('configuration')->with('success', 'Logo updated successfully.');
    }

    public function storeService(Request $request)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $data = $request->validate([
            'service_name' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
        ]);

        Service::create([
            'ServiceName' => $data['service_name'],
            'Description' => $data['description'] ?? null,
            'IsArchived' => false,
        ]);

        $this->auditLog->log('Create', "Added a new service: {$data['service_name']}.");

        return redirect()->route('configuration')->with('success', 'Service added.');
    }

    public function updateService(Request $request, $id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $data = $request->validate([
            'service_name' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
        ]);

        $service = Service::findOrFail($id);
        $service->update([
            'ServiceName' => $data['service_name'],
            'Description' => $data['description'] ?? null,
        ]);

        $this->auditLog->log('Edit', "Edited service: {$data['service_name']}.");

        return redirect()->route('configuration')->with('success', 'Service updated.');
    }

    public function archiveService($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $service = Service::findOrFail($id);
        $service->update(['IsArchived' => true]);

        $this->auditLog->log('Archive', "Archived service: {$service->ServiceName}.");

        return redirect()->route('configuration')->with('success', 'Service archived.');
    }

    public function unarchiveService($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $service = Service::findOrFail($id);
        $service->update(['IsArchived' => false]);

        $this->auditLog->log('Unarchive', "Unarchived service: {$service->ServiceName}.");

        return redirect()->route('configuration')->with('success', 'Service restored.');
    }

    public function archiveActivityLog($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        ActivityLog::findOrFail($id)->update(['IsArchived' => true]);

        return redirect()->route('configuration')->with('success', 'Activity log archived.');
    }

    public function unarchiveActivityLog($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        ActivityLog::findOrFail($id)->update(['IsArchived' => false]);

        return redirect()->route('configuration')->with('success', 'Activity log restored.');
    }

    public function archiveAuditLog($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        AuditLog::findOrFail($id)->update(['IsArchived' => true]);

        return redirect()->route('configuration')->with('success', 'Audit log archived.');
    }

    public function unarchiveAuditLog($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        AuditLog::findOrFail($id)->update(['IsArchived' => false]);

        return redirect()->route('configuration')->with('success', 'Audit log restored.');
    }
}
