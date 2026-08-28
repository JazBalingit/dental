<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SystemSetting;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        $activeServices = Service::with('category')->where('IsArchived', false);
        $archivedServices = Service::with('category')->where('IsArchived', true);
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

        $settingsTab = in_array($request->query('settingsTab'), ['about', 'services', 'privacy', 'appointment', 'activity'], true)
            ? $request->query('settingsTab')
            : 'about';

        return view('superAdmin.configuration', [
            'settingsTab' => $settingsTab,
            'aboutInfo' => SystemSetting::aboutInfo(),
            'privacyLegal' => [
                'privacyPolicy' => SystemSetting::get('privacy_policy_content', ''),
                'legalTerms' => SystemSetting::get('legal_terms_content', ''),
            ],
            'appointmentSteps' => SystemSetting::appointmentSteps(),
            'categories' => ServiceCategory::withCount('services')->orderBy('DisplayOrder')->orderBy('Name')->get(),
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

    public function updateAboutInfo(Request $request)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $data = $request->validate([
            'address' => 'required|string|max:255',
            'operating_days' => 'required|string|max:100',
            'operating_hours' => 'required|string|max:150',
            'about_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        SystemSetting::set('about_address', $data['address']);
        SystemSetting::set('about_operating_days', $data['operating_days']);
        SystemSetting::set('about_operating_hours', $data['operating_hours']);

        if ($request->hasFile('about_image')) {
            $file = $request->file('about_image');
            $filename = 'about_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            SystemSetting::set('about_image', '/images/' . $filename);
        }

        $this->auditLog->log('Edit', 'Updated the About section information.');

        return redirect()->route('configuration', ['settingsTab' => 'about'])->with('success', 'System information updated.');
    }

    public function updatePrivacyLegal(Request $request)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $data = $request->validate([
            'privacy_policy' => 'nullable|string',
            'legal_terms' => 'nullable|string',
        ]);

        SystemSetting::set('privacy_policy_content', $data['privacy_policy'] ?? '');
        SystemSetting::set('legal_terms_content', $data['legal_terms'] ?? '');

        $this->auditLog->log('Edit', 'Updated the Privacy Policy and Legal Terms.');

        return redirect()->route('configuration', ['settingsTab' => 'privacy'])->with('success', 'Privacy and legal terms updated.');
    }

    public function updateAppointmentSteps(Request $request)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $data = $request->validate([
            'steps' => 'required|array|min:1|max:20',
            'steps.*.title' => 'required|string|max:150',
            'steps.*.desc' => 'required|string|max:500',
        ]);

        $steps = array_values($data['steps']);
        $oldCount = SystemSetting::appointmentStepCount();

        foreach ($steps as $i => $step) {
            $n = $i + 1;
            SystemSetting::set("appt_step_{$n}_title", $step['title']);
            SystemSetting::set("appt_step_{$n}_desc", $step['desc']);
        }

        for ($n = count($steps) + 1; $n <= $oldCount; $n++) {
            SystemSetting::forget("appt_step_{$n}_title");
            SystemSetting::forget("appt_step_{$n}_desc");
        }

        SystemSetting::set('appt_step_count', count($steps));

        $this->auditLog->log('Edit', 'Updated the Appointment Process steps.');

        return redirect()->route('configuration', ['settingsTab' => 'appointment'])->with('success', 'Appointment process updated.');
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
            'category_id' => 'nullable|exists:tbl_service_categories,CategoryID',
            'description' => 'nullable|string|max:1000',
            'duration_minutes' => 'required|integer|min:30|max:480|multiple_of:30',
        ]);

        Service::create([
            'ServiceName' => $data['service_name'],
            'CategoryID' => $data['category_id'] ?? null,
            'Description' => $data['description'] ?? null,
            'DurationMinutes' => $data['duration_minutes'],
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
            'category_id' => 'nullable|exists:tbl_service_categories,CategoryID',
            'description' => 'nullable|string|max:1000',
            'duration_minutes' => 'required|integer|min:30|max:480|multiple_of:30',
        ]);

        $service = Service::findOrFail($id);
        $service->update([
            'ServiceName' => $data['service_name'],
            'CategoryID' => $data['category_id'] ?? null,
            'Description' => $data['description'] ?? null,
            'DurationMinutes' => $data['duration_minutes'],
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

    public function storeCategory(Request $request)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'icon' => ['nullable', 'string', Rule::in(array_keys(ServiceCategory::iconOptions()))],
        ]);

        ServiceCategory::create([
            'Name' => $data['name'],
            'Icon' => $data['icon'] ?? null,
            'DisplayOrder' => (int) ServiceCategory::max('DisplayOrder') + 1,
        ]);

        $this->auditLog->log('Create', "Added a new service category: {$data['name']}.");

        return redirect()->route('configuration', ['settingsTab' => 'services'])->with('success', 'Category added.');
    }

    public function updateCategory(Request $request, $id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'icon' => ['nullable', 'string', Rule::in(array_keys(ServiceCategory::iconOptions()))],
        ]);

        $category = ServiceCategory::findOrFail($id);
        $category->update([
            'Name' => $data['name'],
            'Icon' => $data['icon'] ?? null,
        ]);

        $this->auditLog->log('Edit', "Edited service category: {$data['name']}.");

        return redirect()->route('configuration', ['settingsTab' => 'services'])->with('success', 'Category updated.');
    }

    /**
     * Deleting a category doesn't touch its services — the CategoryID
     * foreign key is set to null (see the migration), so they just fall
     * back to "Uncategorized" instead of disappearing.
     */
    public function destroyCategory($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $category = ServiceCategory::findOrFail($id);
        $category->delete();

        $this->auditLog->log('Edit', "Deleted service category: {$category->Name}.");

        return redirect()->route('configuration', ['settingsTab' => 'services'])->with('success', 'Category deleted.');
    }

    public function archiveActivityLog($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        ActivityLog::findOrFail($id)->update(['IsArchived' => true]);

        $this->auditLog->log('Archive', "Archived activity log entry #{$id}.");

        return redirect()->route('configuration')->with('success', 'Activity log archived.');
    }

    public function unarchiveActivityLog($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        ActivityLog::findOrFail($id)->update(['IsArchived' => false]);

        $this->auditLog->log('Unarchive', "Restored activity log entry #{$id}.");

        return redirect()->route('configuration')->with('success', 'Activity log restored.');
    }

    public function archiveAuditLog($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        AuditLog::findOrFail($id)->update(['IsArchived' => true]);

        $this->auditLog->log('Archive', "Archived audit log entry #{$id}.");

        return redirect()->route('configuration')->with('success', 'Audit log archived.');
    }

    public function unarchiveAuditLog($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        AuditLog::findOrFail($id)->update(['IsArchived' => false]);

        $this->auditLog->log('Unarchive', "Restored audit log entry #{$id}.");

        return redirect()->route('configuration')->with('success', 'Audit log restored.');
    }
}
