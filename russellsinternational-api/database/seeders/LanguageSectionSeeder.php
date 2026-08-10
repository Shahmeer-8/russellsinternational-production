<?php

namespace Database\Seeders;

use App\Models\LanguageSection;
use Illuminate\Database\Seeder;

class LanguageSectionSeeder extends Seeder
{
    /**
     * Mirrors the hardcoded GROUPS array the frontend used before sections became
     * editable, so migrating changes nothing on the live page. Keyed on slug so
     * re-running never duplicates a section and never overwrites wording the
     * owner has since edited.
     */
    public function run(): void
    {
        $sections = [
            [
                'slug' => 'english',
                'label' => 'English Tests',
                'short_label' => 'English',
                'heading' => 'English Test Preparation',
                'subtitle' => 'IELTS, PTE, LanguageCert and university-ready English coaching.',
                'icon_name' => 'Languages',
                'color_class' => 'bg-blue-50 text-blue-600',
                'sort_order' => 1,
            ],
            [
                'slug' => 'german',
                'label' => 'German Tests',
                'short_label' => 'German',
                'heading' => 'German Language & Exams',
                'subtitle' => 'A1 to B2 pathways plus Goethe, TestDaF and telc exam readiness.',
                'icon_name' => 'BookOpenText',
                'color_class' => 'bg-amber-50 text-amber-600',
                'sort_order' => 2,
            ],
            [
                'slug' => 'korean',
                'label' => 'Korean Tests',
                'short_label' => 'Korean',
                'heading' => 'Korean Language & EPS',
                'subtitle' => 'TOPIK, EPS-TOPIK and practical Korean for study or work.',
                'icon_name' => 'MessageCircle',
                'color_class' => 'bg-rose-50 text-rose-600',
                'sort_order' => 3,
            ],
        ];

        foreach ($sections as $section) {
            LanguageSection::query()->firstOrCreate(
                ['slug' => $section['slug']],
                $section
            );
        }
    }
}
