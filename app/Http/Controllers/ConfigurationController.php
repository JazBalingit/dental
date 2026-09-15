<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AppointmentStep;
use App\Models\DentistSchedule;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SystemSetting;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConfigurationController extends Controller
{
    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    /**
     * Every distinct ActivityType the unified trail can hold — drives the
     * "filter by type" dropdown on the Activity Logs tab. 'Login' covers
     * both the sign-in and its later sign-out on the same row.
     */
    protected array $actionTypes = [
        'Login', 'Logout', 'Failed Login',
        'Password Changed', 'Failed Password Change',
        'Appointment Booked', 'Appointment Cancelled', 'Appointment Rescheduled', 'Failed Booking',
        'Profile Updated',
        'Super Admin Activated', 'Super Admin Released', 'Failed Super Admin Activation', 'Failed Super Admin Release',
        'Create', 'Edit', 'Archive', 'Unarchive', 'Approve', 'Decline', 'Complete', 'Cancel',
    ];

    public function index(Request $request)
    {
        $serviceSearch = $request->query('serviceSearch');
        $activitySearch = $request->query('activitySearch');
        $activityType = $request->query('activityType');

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

        // The single system-wide trail: login/logout sessions, failed
        // logins, password changes, and every admin/staff/patient action.
        $buildActivity = function (bool $archived) use ($activitySearch, $activityType) {
            $q = ActivityLog::with(['userAccount.patientInfo', 'userAccount.staffInfo'])
                ->where('IsArchived', $archived);

            if ($activityType) {
                $q->where('ActivityType', $activityType);
            }

            if ($activitySearch) {
                $q->where(function ($outer) use ($activitySearch) {
                    $outer->where('ActorName', 'like', "%{$activitySearch}%")
                        ->orWhere('Description', 'like', "%{$activitySearch}%")
                        ->orWhere('ActivityType', 'like', "%{$activitySearch}%")
                        ->orWhereHas('userAccount', function ($u) use ($activitySearch) {
                            $u->where('Email', 'like', "%{$activitySearch}%")
                                ->orWhereHas('patientInfo', function ($p) use ($activitySearch) {
                                    $p->where('FirstName', 'like', "%{$activitySearch}%")->orWhere('LastName', 'like', "%{$activitySearch}%");
                                })
                                ->orWhereHas('staffInfo', function ($s) use ($activitySearch) {
                                    $s->where('FirstName', 'like', "%{$activitySearch}%")->orWhere('LastName', 'like', "%{$activitySearch}%");
                                });
                        });
                });
            }

            return $q->orderByRaw('COALESCE(LoggedInTime, created_at) DESC');
        };

        $activeActivity = $buildActivity(false);
        $archivedActivity = $buildActivity(true);

        $activeSteps = AppointmentStep::where('IsArchived', false)->orderBy('DisplayOrder');
        $archivedSteps = AppointmentStep::where('IsArchived', true)->orderBy('DisplayOrder');

        $activeCategories = ServiceCategory::withCount('services')->where('IsArchived', false)->orderBy('DisplayOrder')->orderBy('Name');
        $archivedCategories = ServiceCategory::withCount('services')->where('IsArchived', true)->orderBy('DisplayOrder')->orderBy('Name');

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
            // Page links need the owning settings tab (and, for split
            // active/archived tables, the sub-tab too) baked in explicitly —
            // switching tabs is client-side only (Bootstrap pills), so it
            // never lands in the request's own query string for
            // withQueryString() to pick up on its own.
            'appointmentSteps' => $activeSteps->paginate(10, ['*'], 'appointmentSteps_page')->withQueryString()
                ->appends(['settingsTab' => 'appointment', 'appointmentStepsTab' => 'active']),
            'archivedAppointmentSteps' => $archivedSteps->paginate(10, ['*'], 'appointmentSteps_archived_page')->withQueryString()
                ->appends(['settingsTab' => 'appointment', 'appointmentStepsTab' => 'archived']),
            // Active only — used both for the Manage Categories "Active" tab
            // and as the choices offered when assigning a category to a
            // service (an archived category shouldn't be newly assignable).
            'categories' => $activeCategories->get(),
            'archivedCategories' => $archivedCategories->get(),
            'categoriesTab' => $request->query('categoriesTab') === 'archived' ? 'archived' : 'active',
            'services' => $activeServices->orderBy('ServiceName')->paginate(10, ['*'], 'services_page')->withQueryString()
                ->appends(['settingsTab' => 'services', 'servicesTab' => 'active']),
            'archivedServices' => $archivedServices->orderBy('ServiceName')->paginate(10, ['*'], 'services_archived_page')->withQueryString()
                ->appends(['settingsTab' => 'services', 'servicesTab' => 'archived']),
            'activityLogs' => $activeActivity->paginate(15, ['*'], 'activity_page')->withQueryString()
                ->appends(['settingsTab' => 'activity', 'activityTab' => 'active']),
            'archivedActivityLogs' => $archivedActivity->paginate(15, ['*'], 'activity_archived_page')->withQueryString()
                ->appends(['settingsTab' => 'activity', 'activityTab' => 'archived']),
            'servicesTab' => $request->query('servicesTab') === 'archived' ? 'archived' : 'active',
            'activityTab' => $request->query('activityTab') === 'archived' ? 'archived' : 'active',
            'appointmentStepsTab' => $request->query('appointmentStepsTab') === 'archived' ? 'archived' : 'active',
            'serviceSearch' => $serviceSearch,
            'activitySearch' => $activitySearch,
            'activityType' => $activityType,
            'actionTypes' => $this->actionTypes,
            'logoVersion' => file_exists($logoPath) ? filemtime($logoPath) : time(),
        ]);
    }

    public function updateAboutInfo(Request $request)
    {
        $data = $request->validate([
            'address' => 'required|string|max:255',
            'operating_days' => 'required|string|max:100',
            'booking_open_time' => ['required', 'date_format:H:i', 'regex:/^\d{2}:(00|30)$/'],
            'booking_close_time' => ['required', 'date_format:H:i', 'regex:/^\d{2}:(00|30)$/', 'after:booking_open_time'],
            'lunch_enabled' => 'nullable|boolean',
            'booking_lunch_start' => ['nullable', 'required_with:booking_lunch_end', 'date_format:H:i', 'regex:/^\d{2}:(00|30)$/'],
            'booking_lunch_end' => ['nullable', 'required_with:booking_lunch_start', 'date_format:H:i', 'regex:/^\d{2}:(00|30)$/', 'after:booking_lunch_start'],
            'hero_title' => 'nullable|string|max:100',
            'hero_subtitle' => 'nullable|string|max:200',
            'hero_description' => 'nullable|string|max:400',
            'about_description' => 'nullable|string|max:1000',
            'footer_description' => 'nullable|string|max:1000',
            'footer_copyright' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'contact_mobile' => 'nullable|string|max:50',
            'contact_email' => 'required|email|max:150',
            'logo' => 'nullable|file|mimes:jpg,jpeg,png,svg|max:2048',
            'hero_image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'about_image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        // Appointment booking window — governs every slot grid across the app
        // (see DentistSchedule::slotTimes). The lunch break is optional; when
        // its times are blank or the toggle is off, the day has no break.
        $lunchEnabled = $request->boolean('lunch_enabled')
            && !empty($data['booking_lunch_start'])
            && !empty($data['booking_lunch_end']);

        SystemSetting::set('booking_open_time', $data['booking_open_time']);
        SystemSetting::set('booking_close_time', $data['booking_close_time']);
        SystemSetting::set('booking_lunch_enabled', $lunchEnabled ? '1' : '0');
        if (!empty($data['booking_lunch_start']) && !empty($data['booking_lunch_end'])) {
            SystemSetting::set('booking_lunch_start', $data['booking_lunch_start']);
            SystemSetting::set('booking_lunch_end', $data['booking_lunch_end']);
        }

        DentistSchedule::flushClinicHoursCache();

        SystemSetting::set('about_address', $data['address']);
        SystemSetting::set('about_operating_days', $data['operating_days']);
        // Keep the public "Operating Hours" line in step with the booking window.
        SystemSetting::set('about_operating_hours', DentistSchedule::clinicHoursLabel());
        SystemSetting::set('hero_title', $data['hero_title'] ?? '');
        SystemSetting::set('hero_subtitle', $data['hero_subtitle'] ?? '');
        SystemSetting::set('hero_description', $data['hero_description'] ?? '');
        SystemSetting::set('about_description', $data['about_description'] ?? '');
        SystemSetting::set('footer_description', $data['footer_description'] ?? '');
        SystemSetting::set('footer_copyright', $data['footer_copyright'] ?? '');
        SystemSetting::set('contact_phone', $data['contact_phone'] ?? '');
        SystemSetting::set('contact_mobile', $data['contact_mobile'] ?? '');
        SystemSetting::set('contact_email', $data['contact_email']);

        // Clinic logo — fixed filename so every <img src="…/puspus_logo.png">
        // across the app picks it up (cache-busted by filemtime in the view).
        if ($request->hasFile('logo')) {
            $request->file('logo')->move(public_path('images'), 'puspus_logo.png');
        }

        // Landing-page hero background + About-section photo — timestamped
        // filenames so the browser always fetches the new one.
        foreach (['hero_image' => 'hero_', 'about_image' => 'about_'] as $field => $prefix) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = $prefix . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images'), $filename);
                SystemSetting::set($field === 'hero_image' ? 'hero_image' : 'about_image', '/images/' . $filename);
            }
        }

        $this->activityLog->log('Edit', 'Updated the clinic branding and system information.');

        return back()->with('success', 'System information updated.');
    }

    public function updatePrivacyLegal(Request $request)
    {
        $data = $request->validate([
            'privacy_policy' => 'nullable|string',
            'legal_terms' => 'nullable|string',
        ]);

        SystemSetting::set('privacy_policy_content', $data['privacy_policy'] ?? '');
        SystemSetting::set('legal_terms_content', $data['legal_terms'] ?? '');

        $this->activityLog->log('Edit', 'Updated the Privacy Policy and Legal Terms.');

        return back()->with('success', 'Privacy and legal terms updated.');
    }

    public function storeAppointmentStep(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'required|string|max:500',
        ]);

        AppointmentStep::create([
            'Title' => $data['title'],
            'Description' => $data['description'],
            'DisplayOrder' => (int) AppointmentStep::max('DisplayOrder') + 1,
            'IsArchived' => false,
        ]);

        $this->activityLog->log('Create', "Added a new appointment-process step: {$data['title']}.");

        return back()->with('success', 'Step added.');
    }

    public function updateAppointmentStep(Request $request, $id)
    {
        $data = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'required|string|max:500',
        ]);

        $step = AppointmentStep::findOrFail($id);
        $step->update([
            'Title' => $data['title'],
            'Description' => $data['description'],
        ]);

        $this->activityLog->log('Edit', "Edited appointment-process step: {$data['title']}.");

        return back()->with('success', 'Step updated.');
    }

    public function archiveAppointmentStep($id)
    {
        $step = AppointmentStep::findOrFail($id);
        $step->update(['IsArchived' => true]);

        $this->activityLog->log('Archive', "Archived appointment-process step: {$step->Title}.");

        return back()->with('success', 'Step archived.');
    }

    public function unarchiveAppointmentStep($id)
    {
        $step = AppointmentStep::findOrFail($id);
        $step->update(['IsArchived' => false]);

        $this->activityLog->log('Unarchive', "Unarchived appointment-process step: {$step->Title}.");

        return back()->with('success', 'Step restored.');
    }

    public function storeService(Request $request)
    {
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

        $this->activityLog->log('Create', "Added a new service: {$data['service_name']}.");

        return back()->with('success', 'Service added.');
    }

    public function updateService(Request $request, $id)
    {
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

        $this->activityLog->log('Edit', "Edited service: {$data['service_name']}.");

        return back()->with('success', 'Service updated.');
    }

    public function archiveService($id)
    {
        $service = Service::findOrFail($id);
        $service->update(['IsArchived' => true]);

        $this->activityLog->log('Archive', "Archived service: {$service->ServiceName}.");

        return back()->with('success', 'Service archived.');
    }

    public function unarchiveService($id)
    {
        $service = Service::findOrFail($id);
        $service->update(['IsArchived' => false]);

        $this->activityLog->log('Unarchive', "Unarchived service: {$service->ServiceName}.");

        return back()->with('success', 'Service restored.');
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'icon' => ['nullable', 'string', Rule::in(array_keys(ServiceCategory::iconOptions()))],
        ]);

        ServiceCategory::create([
            'Name' => $data['name'],
            'Icon' => $data['icon'] ?? null,
            'DisplayOrder' => (int) ServiceCategory::max('DisplayOrder') + 1,
        ]);

        $this->activityLog->log('Create', "Added a new service category: {$data['name']}.");

        return back()->with('success', 'Category added.');
    }

    public function updateCategory(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'icon' => ['nullable', 'string', Rule::in(array_keys(ServiceCategory::iconOptions()))],
        ]);

        $category = ServiceCategory::findOrFail($id);
        $category->update([
            'Name' => $data['name'],
            'Icon' => $data['icon'] ?? null,
        ]);

        $this->activityLog->log('Edit', "Edited service category: {$data['name']}.");

        return back()->with('success', 'Category updated.');
    }

    public function archiveCategory($id)
    {
        $category = ServiceCategory::findOrFail($id);
        $category->update(['IsArchived' => true]);

        $this->activityLog->log('Archive', "Archived service category: {$category->Name}.");

        return back()->with('success', 'Category archived.');
    }

    public function unarchiveCategory($id)
    {
        $category = ServiceCategory::findOrFail($id);
        $category->update(['IsArchived' => false]);

        $this->activityLog->log('Unarchive', "Unarchived service category: {$category->Name}.");

        return back()->with('success', 'Category restored.');
    }

    public function archiveActivityLog($id)
    {
        ActivityLog::findOrFail($id)->update(['IsArchived' => true]);

        $this->activityLog->log('Archive', "Archived activity log entry #{$id}.");

        return back()->with('success', 'Activity log archived.');
    }

    public function unarchiveActivityLog($id)
    {
        ActivityLog::findOrFail($id)->update(['IsArchived' => false]);

        $this->activityLog->log('Unarchive', "Restored activity log entry #{$id}.");

        return back()->with('success', 'Activity log restored.');
    }
}
