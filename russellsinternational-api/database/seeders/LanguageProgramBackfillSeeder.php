<?php

namespace Database\Seeders;

use App\Models\LanguageProgram;
use App\Models\LanguageSection;
use Illuminate\Database\Seeder;

class LanguageProgramBackfillSeeder extends Seeder
{
    /**
     * Promotes the frontend's former DEFAULT_PROGRAMS fallback into real, editable
     * records so deleting that array changes nothing on the page, and hands the
     * owner eight ready-made programs.
     *
     * Matches on (section, title) so it never duplicates or overwrites. Run
     * explicitly — never from a migration, or every test database inherits these
     * eight rows and ContentLifecycleTest's ordering assertion turns flaky.
     */
    public function run(): void
    {
        (new LanguageSectionSeeder)->run();

        // Lorem test data that reached the live site during earlier QA.
        LanguageProgram::query()->where('title', 'Acton Kim')->delete();

        foreach ($this->programs() as $slug => $programs) {
            $sectionId = LanguageSection::query()->where('slug', $slug)->value('id');

            if (! $sectionId) {
                continue;
            }

            foreach ($programs as $index => $program) {
                LanguageProgram::query()->firstOrCreate(
                    ['language_section_id' => $sectionId, 'title' => $program['title']],
                    $program + [
                        'language_section_id' => $sectionId,
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    /**
     * Copied verbatim from the DEFAULT_PROGRAMS array the frontend used to ship.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function programs(): array
    {
        return [
            'english' => [
                [
                    'flag_emoji' => 'GB',
                    'title' => 'IELTS Preparation',
                    'duration' => '8 Weeks',
                    'badge' => 'Most Popular',
                    'description' => 'Complete coaching for listening, reading, writing and speaking with weekly mock tests.',
                    'benefits' => ['Band score strategy', 'Writing task feedback', 'Speaking interview practice', 'Full-length mock exams'],
                    'color_class' => 'bg-blue-50 text-blue-600',
                    'icon_name' => 'Languages',
                ],
                [
                    'flag_emoji' => 'GB',
                    'title' => 'PTE Academic',
                    'duration' => '6 Weeks',
                    'badge' => 'Fast Track',
                    'description' => 'Computer-based practice focused on scoring patterns, fluency, pronunciation and time control.',
                    'benefits' => ['AI-scored practice', 'Template drills', 'Speaking fluency sessions', 'Target-score roadmap'],
                    'color_class' => 'bg-cyan-50 text-cyan-600',
                    'icon_name' => 'ScrollText',
                ],
                [
                    'flag_emoji' => 'GB',
                    'title' => 'LanguageCert',
                    'duration' => '6 Weeks',
                    'badge' => 'Visa Ready',
                    'description' => 'Preparation for LanguageCert ESOL and SELT-style assessment routes.',
                    'benefits' => ['Exam format training', 'Grammar refreshers', 'Writing correction', 'Interview-style speaking'],
                    'color_class' => 'bg-indigo-50 text-indigo-600',
                    'icon_name' => 'Award',
                ],
            ],
            'german' => [
                [
                    'flag_emoji' => 'DE',
                    'title' => 'Goethe A1-B2',
                    'duration' => '12 Weeks per level',
                    'badge' => 'Visa Ready',
                    'description' => 'Goethe-aligned German classes for study, Ausbildung, family reunion and work pathways.',
                    'benefits' => ['A1 to B2 levels', 'Grammar and vocabulary labs', 'Model papers', 'Conversation practice'],
                    'color_class' => 'bg-amber-50 text-amber-600',
                    'icon_name' => 'BookOpenText',
                ],
                [
                    'flag_emoji' => 'DE',
                    'title' => 'TestDaF Preparation',
                    'duration' => '8 Weeks',
                    'badge' => 'University Track',
                    'description' => 'Academic German preparation for students targeting German university admission.',
                    'benefits' => ['Reading and listening drills', 'Academic writing', 'Speaking simulations', 'Timed practice tests'],
                    'color_class' => 'bg-red-50 text-red-600',
                    'icon_name' => 'ScrollText',
                ],
                [
                    'flag_emoji' => 'DE',
                    'title' => 'telc German',
                    'duration' => '8 Weeks',
                    'badge' => 'Exam Ready',
                    'description' => 'Structured telc preparation for everyday, professional and visa-focused German exams.',
                    'benefits' => ['Exam sections breakdown', 'Writing samples', 'Pair speaking practice', 'Level assessment'],
                    'color_class' => 'bg-yellow-50 text-yellow-700',
                    'icon_name' => 'Award',
                ],
            ],
            'korean' => [
                [
                    'flag_emoji' => 'KR',
                    'title' => 'TOPIK Preparation',
                    'duration' => '10 Weeks',
                    'badge' => 'Study Track',
                    'description' => 'From Hangul foundations to TOPIK I and II preparation for Korean study pathways.',
                    'benefits' => ['Hangul mastery', 'Vocabulary sets', 'Reading practice', 'Mock TOPIK papers'],
                    'color_class' => 'bg-rose-50 text-rose-600',
                    'icon_name' => 'MessageCircle',
                ],
                [
                    'flag_emoji' => 'KR',
                    'title' => 'EPS-TOPIK',
                    'duration' => '8 Weeks',
                    'badge' => 'EPS Ready',
                    'description' => 'Work-route Korean preparation with practical vocabulary and EPS-style question practice.',
                    'benefits' => ['Workplace vocabulary', 'Listening drills', 'EPS model tests', 'Application guidance'],
                    'color_class' => 'bg-emerald-50 text-emerald-600',
                    'icon_name' => 'Award',
                ],
            ],
        ];
    }
}
