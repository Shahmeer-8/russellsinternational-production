<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\GalleryPhoto;
use App\Models\HeroSlide;
use App\Models\Internship;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\StudyDestination;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProductionContentBackfillSeeder extends Seeder
{
    public function run(): void
    {
        $this->copySeedMedia();
        $this->backfillPages();
        $this->backfillPageSections();
        $this->backfillModelMedia();

        $this->command?->info('Production content backfill completed.');
    }

    private function copySeedMedia(): void
    {
        $sourceRoot = database_path('seed-media');

        if (! File::isDirectory($sourceRoot)) {
            return;
        }

        foreach (File::allFiles($sourceRoot) as $file) {
            $relativePath = str_replace('\\', '/', $file->getRelativePathname());

            if (! Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->put($relativePath, File::get($file->getPathname()));
            }
        }
    }

    private function backfillPages(): void
    {
        foreach ($this->pages() as $slug => $data) {
            Page::firstOrCreate(['slug' => $slug], $data + ['is_active' => true]);
        }
    }

    private function backfillPageSections(): void
    {
        foreach ($this->sections() as $section) {
            PageSection::firstOrCreate(
                ['page_slug' => $section['page_slug'], 'section_key' => $section['section_key']],
                $section,
            );

            PageSection::query()
                ->where('page_slug', $section['page_slug'])
                ->where('section_key', $section['section_key'])
                ->whereNull('image')
                ->when(isset($section['image']), fn ($query) => $query->update(['image' => $section['image']]));
        }
    }

    private function backfillModelMedia(): void
    {
        HeroSlide::where('id', 1)->whereNull('image')->update(['image' => 'hero-slides/hero-students-clean.jpg']);
        HeroSlide::where('id', 2)->whereNull('image')->update(['image' => 'hero-slides/study-abroad-clean.jpg']);
        HeroSlide::where('id', 3)->whereNull('image')->update(['image' => 'hero-slides/skill-training.jpg']);

        foreach ([
            1 => 'gallery/gallery-campus.jpg',
            2 => 'gallery/gallery-lab.jpg',
            3 => 'gallery/gallery-graduation.jpg',
            4 => 'gallery/event-workshop.jpg',
            5 => 'gallery/event-seminar.jpg',
            6 => 'gallery/about-team.jpg',
        ] as $id => $image) {
            GalleryPhoto::where('id', $id)->whereNull('image')->update(['image' => $image]);
        }

        Event::where('tag', 'Admissions')->whereNull('image')->update(['image' => 'events/event-admissions.jpg']);
        Event::where('tag', 'Workshop')->whereNull('image')->update(['image' => 'events/event-workshop.jpg']);
        Event::where('tag', 'Seminar')->whereNull('image')->update(['image' => 'events/event-seminar.jpg']);

        StudyDestination::where('country', 'United Kingdom')->whereNull('image')->update(['image' => 'destinations/united-kingdom.jpg']);
        StudyDestination::where('country', 'Canada')->whereNull('image')->update(['image' => 'destinations/canada.jpg']);
        StudyDestination::where('country', 'Australia')->whereNull('image')->update(['image' => 'destinations/australia.jpg']);
        StudyDestination::where('country', 'United States')->whereNull('image')->update(['image' => 'destinations/united-states.jpg']);

        Internship::whereNull('image')->update(['image' => 'internships/internship.jpg']);

        Testimonial::where('name', 'Ayesha Khan')->whereNull('image')->update(['image' => 'testimonials/student-ayesha.jpg']);
        Testimonial::where('name', 'Omer Ali')->whereNull('image')->update(['image' => 'testimonials/student-omer.jpg']);
        Testimonial::where('name', 'Maria Santos')->whereNull('image')->update(['image' => 'testimonials/student-maria.jpg']);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function pages(): array
    {
        return [
            'home' => ['name' => 'Home', 'meta_title' => "Russell's International", 'meta_description' => 'Study abroad guidance, skill training, language programs, jobs and internships.'],
            'about' => ['name' => 'About Us', 'meta_title' => "About Russell's International", 'meta_description' => 'Learn about Russell International, our people, mission and learning ecosystem.'],
            'skills' => ['name' => 'Skills', 'meta_title' => 'Skills & Courses', 'meta_description' => 'Premium and NAVTTC skill training programs.'],
            'study-abroad' => ['name' => 'Study Abroad', 'meta_title' => 'Study Abroad', 'meta_description' => 'Admissions and visa guidance for global universities.'],
            'languages' => ['name' => 'Languages', 'meta_title' => 'Language Programs', 'meta_description' => 'IELTS, German and Korean language programs.'],
            'careers' => ['name' => 'Careers', 'meta_title' => 'Careers', 'meta_description' => 'Jobs, internships and career growth opportunities.'],
            'events' => ['name' => 'Events', 'meta_title' => 'Events & News', 'meta_description' => 'Workshops, seminars, news and gallery updates.'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function sections(): array
    {
        return [
            [
                'page_slug' => 'home',
                'section_key' => 'dual_focus',
                'name' => 'Homepage Study Abroad and Skills Heading',
                'eyebrow' => 'Study abroad and skills',
                'title' => 'Pick the pathway that fits your next move.',
                'body' => "A quick homepage preview of Russell's two core directions: global admissions support for students planning overseas study, and practical IT training for students building career-ready skills.",
                'sort_order' => 30,
                'is_active' => true,
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'dual_focus_study',
                'name' => 'Homepage Study Abroad Card',
                'eyebrow' => 'Study Abroad',
                'title' => 'From country shortlisting to visa file guidance.',
                'body' => 'Help students compare destinations, understand intakes, prepare documents and move toward international applications with a clearer plan.',
                'image' => 'hero-slides/study-abroad-clean.jpg',
                'cta_label' => 'Explore Study Abroad',
                'cta_url' => '/study-abroad',
                'items' => [
                    'country_1_code' => 'UK',
                    'country_1_name' => 'United Kingdom',
                    'country_1_meta' => '40+ universities',
                    'country_2_code' => 'CA',
                    'country_2_name' => 'Canada',
                    'country_2_meta' => '35+ universities',
                    'country_3_code' => 'AU',
                    'country_3_name' => 'Australia',
                    'country_3_meta' => '30+ universities',
                ],
                'extra' => [
                    'badge' => 'Admissions support',
                    'footnote' => 'Counselling, admissions, visa support',
                ],
                'sort_order' => 31,
                'is_active' => true,
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'dual_focus_skills',
                'name' => 'Homepage Skills Training Card',
                'eyebrow' => 'Skills Training',
                'title' => 'Practical programs for job-ready IT skills.',
                'body' => 'A focused training preview for students who want hands-on tech learning, portfolio work and marketable skills without searching through the whole site first.',
                'image' => 'hero-slides/skill-training.jpg',
                'cta_label' => 'View Skill Programs',
                'cta_url' => '/skills',
                'items' => [
                    'course_1_title' => 'Full Stack Web Development',
                    'course_1_meta' => '6 months',
                    'course_2_title' => 'AI & Machine Learning',
                    'course_2_meta' => '4 months',
                    'course_3_title' => 'Data Science & Analytics',
                    'course_3_meta' => '5 months',
                ],
                'extra' => [
                    'badge' => 'Skills focus',
                    'footnote' => 'Local training, global confidence',
                ],
                'sort_order' => 32,
                'is_active' => true,
            ],
            [
                'page_slug' => 'global',
                'section_key' => 'cta',
                'name' => 'Global CTA Banner',
                'title' => 'Ready to take the next step?',
                'subtitle' => 'Talk to our team about study abroad, skills training, languages, jobs or internships.',
                'cta_label' => 'Contact Us',
                'cta_url' => '/#contact',
                'sort_order' => 1,
                'is_active' => true,
            ],
            ...$this->heroSections(),
            ...$this->aboutSections(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function heroSections(): array
    {
        return [
            ['page_slug' => 'about', 'section_key' => 'hero', 'name' => 'About Hero', 'eyebrow' => 'About Us', 'title' => 'Change Begins With One Dream', 'subtitle' => 'A premier education consultancy and IT training institute bridging ambition with global opportunity.', 'image' => 'page-sections/about-hero.jpg', 'sort_order' => 0, 'is_active' => true],
            ['page_slug' => 'skills', 'section_key' => 'hero', 'name' => 'Skills Hero', 'eyebrow' => 'Skills & Courses', 'title' => 'Industry-Ready IT & Tech Training', 'subtitle' => 'Premium programs and NAVTTC government-funded courses designed to make you job-ready.', 'image' => 'hero-slides/skill-training.jpg', 'sort_order' => 0, 'is_active' => true],
            ['page_slug' => 'study-abroad', 'section_key' => 'hero', 'name' => 'Study Abroad Hero', 'eyebrow' => 'Study Abroad', 'title' => 'Your Pathway to Global Universities', 'subtitle' => 'End-to-end admissions and visa support for global universities.', 'image' => 'page-sections/study-abroad-hero.jpg', 'sort_order' => 0, 'is_active' => true],
            ['page_slug' => 'languages', 'section_key' => 'hero', 'name' => 'Languages Hero', 'eyebrow' => 'Language Programs', 'title' => 'Master a New Language. Unlock the World.', 'subtitle' => 'IELTS, German, and Korean taught by certified instructors.', 'image' => 'page-sections/languages-hero.jpg', 'sort_order' => 0, 'is_active' => true],
            ['page_slug' => 'careers', 'section_key' => 'hero', 'name' => 'Careers Hero', 'eyebrow' => 'Careers', 'title' => 'Jobs, Internships & Career Growth', 'subtitle' => 'Discover open positions and structured internships to launch your career.', 'image' => 'page-sections/jobs-career.jpg', 'sort_order' => 0, 'is_active' => true],
            ['page_slug' => 'events', 'section_key' => 'hero', 'name' => 'Events Hero', 'eyebrow' => 'Events, News & Gallery', 'title' => 'What Is Happening at Russell International', 'subtitle' => 'Workshops, seminars, admissions briefings and community moments.', 'image' => 'page-sections/events-hero.jpg', 'sort_order' => 0, 'is_active' => true],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function aboutSections(): array
    {
        return [
            ['page_slug' => 'about', 'section_key' => 'campus_life', 'name' => 'Campus Life', 'eyebrow' => 'Campus Life', 'title' => 'A Living, Learning Ecosystem', 'body' => 'A modern learning environment with training labs, counseling spaces, and student support facilities.', 'image' => 'page-sections/campus-life.jpg', 'cta_label' => 'Contact Us', 'cta_url' => '/#contact', 'sort_order' => 1, 'is_active' => true],
            ['page_slug' => 'about', 'section_key' => 'founder_message', 'name' => 'Founder Message', 'eyebrow' => 'Founder Message', 'title' => 'Dear Students, Parents and Well-Wishers', 'body' => 'Together, we shape brighter futures through education, skills, and global opportunity.', 'image' => 'page-sections/founder-portrait.jpg', 'sort_order' => 2, 'is_active' => true],
            ['page_slug' => 'about', 'section_key' => 'foundation', 'name' => 'Foundation', 'eyebrow' => 'What Drives Us', 'title' => 'Our Foundation', 'items' => ['Mission' => 'To deliver skill-based programs that prepare students for global success.', 'Vision' => 'To create a learning climate where students become productive and socially conscious.', 'Core Values' => 'Commitment, accessibility, and excellence in every learning journey.'], 'sort_order' => 3, 'is_active' => true],
        ];
    }
}
