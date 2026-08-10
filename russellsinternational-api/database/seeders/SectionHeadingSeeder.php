<?php

namespace Database\Seeders;

use App\Models\PageSection;
use Illuminate\Database\Seeder;

class SectionHeadingSeeder extends Seeder
{
    /**
     * Section headings used to be hardcoded in the frontend, so the owner could see
     * them on the site but could not change them. Each row below reproduces the
     * text that shipped in the component, byte for byte, so seeding changes nothing
     * on screen — it only makes the words editable.
     *
     * firstOrCreate on (page_slug, section_key): re-running never overwrites wording
     * the owner has since edited.
     */
    public function run(): void
    {
        foreach ($this->headings() as $heading) {
            PageSection::query()->firstOrCreate(
                ['page_slug' => $heading['page_slug'], 'section_key' => $heading['section_key']],
                $heading + ['is_active' => true, 'sort_order' => 0]
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function headings(): array
    {
        return [
            [
                'page_slug' => 'home',
                'section_key' => 'why_choose_us',
                'name' => 'Home — Why choose us heading',
                'eyebrow' => "Why Russell's International",
                'title' => 'Your Trusted Partner in Growth',
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'testimonials',
                'name' => 'Home — Testimonials heading',
                'eyebrow' => 'Student Stories',
                'title' => 'Real Success, Real People',
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'news',
                'name' => 'Home — News carousel heading',
                'eyebrow' => 'Stay Updated',
                'title' => 'Latest News & Events',
                'subtitle' => 'Stay updated with our latest activities, events, and announcements.',
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'contact',
                'name' => 'Home — Contact heading',
                'eyebrow' => 'Get In Touch',
                'title' => 'Ready to Take the Next Step?',
                'subtitle' => 'Fill in the form and our team will get back to you within 24 hours with personalized guidance.',
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'stats',
                'name' => 'Home — Stats strip heading',
                'eyebrow' => 'By the numbers',
                'title' => 'Trusted by thousands of students',
            ],
            [
                'page_slug' => 'home',
                'section_key' => 'services',
                'name' => 'Home — Services heading',
                'eyebrow' => 'What We Do',
                'title' => 'Everything You Need in One Place',
                'subtitle' => 'From skills training to study abroad and career placement, all under one roof.',
            ],
            [
                'page_slug' => 'skills',
                'section_key' => 'courses',
                'name' => 'Skills — Courses heading',
                'eyebrow' => 'Featured Programs',
                'title' => 'Elevate Your Skillset',
                'subtitle' => 'Industry-aligned training programs designed to make you job-ready from day one.',
            ],
            [
                'page_slug' => 'careers',
                'section_key' => 'jobs',
                'name' => 'Careers — Jobs heading',
                'eyebrow' => 'Career Opportunities',
                'title' => 'Join Our Team or Our Partners',
                'subtitle' => "Explore open positions at Russell's International and our partner organizations.",
            ],
            [
                'page_slug' => 'careers',
                'section_key' => 'internships',
                'name' => 'Careers — Internships heading',
                'eyebrow' => 'Internships',
                'title' => 'Gain Real-World Experience',
                'subtitle' => 'Bridge the gap between learning and working. Our internship programs place you in real projects with industry mentors, giving you hands-on experience that employers value.',
            ],
            [
                'page_slug' => 'events',
                'section_key' => 'news',
                'name' => 'Events — News heading',
                'eyebrow' => 'News & Events',
                'title' => "What's Happening",
                'subtitle' => 'Stay updated with our latest events, workshops, and admissions announcements.',
            ],
            [
                'page_slug' => 'events',
                'section_key' => 'gallery',
                'name' => 'Events — Gallery heading',
                'eyebrow' => 'Gallery',
                'title' => "Life at Russell's International",
                'subtitle' => 'A glimpse into our campus, events, training sessions, and student life.',
            ],
            [
                'page_slug' => 'languages',
                'section_key' => 'intro',
                'name' => 'Languages — Section heading',
                'eyebrow' => 'Language Programs',
                'title' => 'Speak the World',
                'subtitle' => 'Exam-focused language training for study abroad, visa pathways, work routes and global careers.',
            ],
            [
                'page_slug' => 'study-abroad',
                'section_key' => 'destinations',
                'name' => 'Study Abroad — Destinations heading',
                'eyebrow' => 'Global Opportunities',
                'title' => 'Top Study Destinations',
                'subtitle' => 'Explore world-class education opportunities across the globe.',
            ],
        ];
    }
}
