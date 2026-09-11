<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

/**
 * Starter service categories so a freshly provisioned database doesn't show
 * an empty "Our Services" section on the landing page. Guarded by name via
 * firstOrCreate, so running this against the already-live database never
 * duplicates or overwrites categories/services an admin has since added or
 * edited through Configuration.
 */
class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'General Dentistry' => [
                'icon' => 'fa-solid fa-tooth',
                'services' => [
                    ['name' => 'Cleaning', 'description' => 'Routine oral prophylaxis to remove plaque and tartar buildup.', 'duration' => 60],
                ],
            ],
            'Prosthodontic Treatment' => [
                'icon' => 'fa-solid fa-teeth',
                'services' => [
                    ['name' => 'Tooth Extraction', 'description' => 'Removal of a damaged, decayed, or problematic tooth.', 'duration' => 60],
                ],
            ],
        ];

        foreach ($categories as $name => $data) {
            $category = ServiceCategory::firstOrCreate(
                ['Name' => $name],
                ['Icon' => $data['icon'], 'DisplayOrder' => (int) ServiceCategory::max('DisplayOrder') + 1]
            );

            foreach ($data['services'] as $service) {
                Service::firstOrCreate(
                    ['ServiceName' => $service['name']],
                    [
                        'CategoryID' => $category->CategoryID,
                        'Description' => $service['description'],
                        'DurationMinutes' => $service['duration'],
                        'IsArchived' => false,
                    ]
                );
            }
        }

        $this->fixMalformedIcons();
        $this->fillEmptyCategories();
    }

    /**
     * Some existing categories were saved with a bare icon name (e.g.
     * "fa-tooth" instead of "fa-solid fa-tooth") — predates the Rule::in
     * validation on ServiceCategory::iconOptions() in ConfigurationController,
     * so it silently renders no icon at all on the landing page. Repair any
     * value that becomes valid once the missing style prefix is restored.
     */
    protected function fixMalformedIcons(): void
    {
        $validIcons = array_keys(ServiceCategory::iconOptions());

        foreach (ServiceCategory::whereNotNull('Icon')->get() as $category) {
            if (in_array($category->Icon, $validIcons, true)) {
                continue;
            }

            $fixed = 'fa-solid ' . ltrim($category->Icon);
            if (in_array($fixed, $validIcons, true)) {
                $category->update(['Icon' => $fixed]);
            }
        }
    }

    /**
     * Every category should show at least one service card on the landing
     * page — fills in a generic consultation placeholder for any category
     * an admin created but hasn't added services to yet.
     */
    protected function fillEmptyCategories(): void
    {
        $placeholders = [
            'Orthodontic Treatment' => ['name' => 'Braces Consultation', 'description' => 'Initial evaluation and treatment planning for braces.'],
            'Oral Surgery & Specialized' => ['name' => 'Consultation', 'description' => 'Initial evaluation for oral surgery and specialized procedures.'],
        ];

        $categories = ServiceCategory::withCount(['services' => fn ($q) => $q->where('IsArchived', false)])->get();

        foreach ($categories as $category) {
            if ($category->services_count > 0) {
                continue;
            }

            $placeholder = $placeholders[$category->Name] ?? [
                'name' => 'Consultation',
                'description' => "Initial evaluation and consultation for {$category->Name}.",
            ];

            Service::firstOrCreate(
                ['ServiceName' => $placeholder['name'], 'CategoryID' => $category->CategoryID],
                ['Description' => $placeholder['description'], 'DurationMinutes' => 60, 'IsArchived' => false]
            );
        }
    }
}
