<?php

namespace Database\Seeders;

use App\Models\LanguageProgram;
use App\Models\LanguageSection;
use App\Models\Page;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Stat;
use App\Models\StudyDestination;
use App\Models\TickerItem;
use App\Models\WhyChooseUsItem;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        // ── Site settings ──────────────────────────────────────────────
        $settings = [
            ['key' => 'phone',         'value' => '+92 304 111 2233',               'type' => 'text',    'group' => 'contact', 'label' => 'Phone Number'],
            ['key' => 'email',         'value' => 'info@russellsinternational.com',  'type' => 'text',    'group' => 'contact', 'label' => 'Email Address'],
            ['key' => 'address',       'value' => 'Islamabad, Pakistan',             'type' => 'text',    'group' => 'contact', 'label' => 'Office Address'],
            ['key' => 'whatsapp',      'value' => '+92 304 111 2233',               'type' => 'text',    'group' => 'contact', 'label' => 'WhatsApp Number'],
            ['key' => 'facebook',      'value' => 'https://facebook.com/',           'type' => 'url',     'group' => 'social',  'label' => 'Facebook URL'],
            ['key' => 'instagram',     'value' => 'https://instagram.com/',          'type' => 'url',     'group' => 'social',  'label' => 'Instagram URL'],
            ['key' => 'linkedin',      'value' => 'https://linkedin.com/',           'type' => 'url',     'group' => 'social',  'label' => 'LinkedIn URL'],
            ['key' => 'youtube',       'value' => 'https://youtube.com/',            'type' => 'url',     'group' => 'social',  'label' => 'YouTube URL'],
            ['key' => 'google_map',    'value' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3321.1!2d73.0!3d33.7!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzPCsDQy!5e0!3m2!1sen!2spk!4v0', 'type' => 'url', 'group' => 'contact', 'label' => 'Google Map Embed URL'],
            ['key' => 'footer_about',  'value' => "Russell's International is a leading consultancy providing study abroad counseling, skill training, IELTS preparation, internship placement, and career services.", 'type' => 'textarea', 'group' => 'footer', 'label' => 'Footer About Text'],
            ['key' => 'meta_title',    'value' => "Russell's International | Study Abroad, Skills & Career Services", 'type' => 'text', 'group' => 'seo', 'label' => 'Default Meta Title'],
            ['key' => 'meta_description', 'value' => "Russell's International helps students achieve their global ambitions through study abroad counseling, skill training, and career services.", 'type' => 'textarea', 'group' => 'seo', 'label' => 'Default Meta Description'],
        ];

        foreach ($settings as $s) {
            Setting::create($s);
        }

        // ── Stats ───────────────────────────────────────────────────────
        $stats = [
            ['value' => '5,000+', 'label' => 'Students Placed',     'icon_name' => 'Users',       'sort_order' => 1],
            ['value' => '95%',    'label' => 'Visa Success Rate',    'icon_name' => 'ShieldCheck', 'sort_order' => 2],
            ['value' => '50+',    'label' => 'Partner Universities', 'icon_name' => 'Globe',       'sort_order' => 3],
            ['value' => '10+',    'label' => 'Years of Experience',  'icon_name' => 'Award',       'sort_order' => 4],
        ];

        foreach ($stats as $s) {
            Stat::create(array_merge($s, ['is_active' => true]));
        }

        // ── Ticker items ────────────────────────────────────────────────
        $tickers = [
            ['emoji' => '🎓', 'text' => 'Admissions Open for September 2026 Intake',  'sort_order' => 1],
            ['emoji' => '🌍', 'text' => '95% Visa Success Rate for UK, Canada & AU',  'sort_order' => 2],
            ['emoji' => '💼', 'text' => 'New IT Courses Starting Monthly',             'sort_order' => 3],
            ['emoji' => '🏆', 'text' => 'NAVTTC Free Training Now Available',          'sort_order' => 4],
            ['emoji' => '📢', 'text' => 'IELTS Batch Starting – Limited Seats',        'sort_order' => 5],
        ];

        foreach ($tickers as $t) {
            TickerItem::create(array_merge($t, ['is_active' => true]));
        }

        // ── Why Choose Us ───────────────────────────────────────────────
        $whyItems = [
            ['icon_name' => 'Award',       'title' => 'Experienced Consultants',  'description' => 'Over a decade of success in global education and IT training.',            'color_class' => 'bg-blue-50 text-blue-600'],
            ['icon_name' => 'ShieldCheck', 'title' => '95% Visa Success',         'description' => 'Proven track record with transparent guidance at every step.',              'color_class' => 'bg-green-50 text-green-600'],
            ['icon_name' => 'Globe',       'title' => 'Global University Network', 'description' => 'Partnerships with 50+ top universities across UK, Canada, Australia.',      'color_class' => 'bg-purple-50 text-purple-600'],
            ['icon_name' => 'TrendingUp',  'title' => 'Career-Focused Training',  'description' => 'Industry-aligned IT programs with job placement support.',                  'color_class' => 'bg-orange-50 text-orange-600'],
            ['icon_name' => 'Users',       'title' => '5,000+ Alumni',            'description' => 'A thriving community of successful graduates worldwide.',                    'color_class' => 'bg-pink-50 text-pink-600'],
            ['icon_name' => 'Headphones',  'title' => 'End-to-End Support',       'description' => "From application to arrival, we're with you every step.",                  'color_class' => 'bg-indigo-50 text-indigo-600'],
        ];

        foreach ($whyItems as $i => $item) {
            WhyChooseUsItem::create(array_merge($item, ['sort_order' => $i + 1, 'is_active' => true]));
        }

        // ── Services ────────────────────────────────────────────────────
        $services = [
            ['icon_name' => 'Laptop',      'title' => 'IT & Skill Training',        'description' => 'Industry-certified programs in web development, AI, data science, and more.',    'details' => 'Our IT training covers Full Stack Development, AI & Machine Learning, Data Science, Digital Marketing, UI/UX Design, Cloud Computing, and Cybersecurity.',    'color_class' => 'bg-blue-50 text-blue-600'],
            ['icon_name' => 'Globe2',      'title' => 'Study Abroad Consultancy',   'description' => 'Expert guidance for admissions to top universities in UK, Canada, Australia, and USA.', 'details' => 'We provide end-to-end support: university selection, application assistance, SOP writing, scholarship guidance, visa preparation, and pre-departure orientation.', 'color_class' => 'bg-green-50 text-green-600'],
            ['icon_name' => 'Languages',   'title' => 'IELTS Preparation',          'description' => 'Comprehensive IELTS coaching with practice tests and band score strategies.',      'details' => 'Our IELTS program covers all four modules. Expert British Council-trained instructors, weekly mock tests, and personalized feedback.',                           'color_class' => 'bg-purple-50 text-purple-600'],
            ['icon_name' => 'GraduationCap', 'title' => 'NAVTTC Programs',         'description' => 'Free government-funded vocational training programs in IT and technical skills.',  'details' => 'Government-funded training under NAVTTC covering web development, Python, e-commerce, freelancing, and more. 100% free with certification.',                    'color_class' => 'bg-emerald-50 text-emerald-600'],
            ['icon_name' => 'Briefcase',   'title' => 'Career Counseling',          'description' => 'Personalized career guidance and job placement assistance.',                        'details' => 'One-on-one career counseling sessions, resume building workshops, interview preparation, and direct connections with hiring companies.',                          'color_class' => 'bg-orange-50 text-orange-600'],
            ['icon_name' => 'BookOpen',    'title' => 'Corporate Trainings',        'description' => 'Customized training programs for organizations looking to upskill.',                'details' => 'Tailored training solutions for businesses including team workshops, bootcamps, and certification programs.',                                                      'color_class' => 'bg-pink-50 text-pink-600'],
        ];

        foreach ($services as $i => $service) {
            Service::create(array_merge($service, ['sort_order' => $i + 1, 'is_active' => true, 'key_benefits' => ['Expert instructors with industry experience', 'Hands-on practical approach', 'Certification upon completion', 'Career placement support', 'Flexible scheduling options']]));
        }

        // ── Study destinations ───────────────────────────────────────────
        $destinations = [
            ['flag_emoji' => '🇬🇧', 'country' => 'United Kingdom', 'partner_unis_count' => '40+', 'description' => 'World-renowned universities with rich heritage and cutting-edge research.', 'highlight_unis' => 'Oxford, Cambridge, UCL',      'intake_periods' => 'Sept & Jan', 'visa_success_rate' => '98% success rate'],
            ['flag_emoji' => '🇨🇦', 'country' => 'Canada',         'partner_unis_count' => '35+', 'description' => 'Affordable excellence with post-study work permits up to 3 years.',         'highlight_unis' => 'Toronto, UBC, McGill',        'intake_periods' => 'Sept, Jan & May', 'visa_success_rate' => '95% success rate'],
            ['flag_emoji' => '🇦🇺', 'country' => 'Australia',      'partner_unis_count' => '30+', 'description' => 'Top-ranked universities in stunning locations with great quality of life.',   'highlight_unis' => 'Melbourne, Sydney, ANU',      'intake_periods' => 'Feb & July',     'visa_success_rate' => '96% success rate'],
            ['flag_emoji' => '🇺🇸', 'country' => 'United States',  'partner_unis_count' => '45+', 'description' => 'Ivy League and cutting-edge research institutions with global recognition.',  'highlight_unis' => 'MIT, Stanford, Harvard',      'intake_periods' => 'Fall & Spring',  'visa_success_rate' => '93% success rate'],
        ];

        $defaultServices = ['University selection & shortlisting', 'Application & documentation support', 'Visa preparation & mock interviews', 'Pre-departure orientation', 'Post-arrival support'];
        foreach ($destinations as $i => $d) {
            StudyDestination::create(array_merge($d, ['services' => $defaultServices, 'sort_order' => $i + 1, 'is_active' => true]));
        }

        // ── Language programs ────────────────────────────────────────────
        // Sections own the grouping, so each program is filed under one by slug.
        $languages = [
            ['slug' => 'english', 'flag_emoji' => '🇬🇧', 'icon_name' => 'Languages',     'title' => 'IELTS Preparation',      'duration' => '8 Weeks',           'badge' => 'Most Popular',    'description' => 'Comprehensive coaching across all four IELTS modules with weekly mock tests.',        'color_class' => 'bg-blue-50 text-blue-600',  'benefits' => ['British Council-trained instructors', 'Weekly full-length mocks', 'Personalized band score strategy', 'Speaking practice sessions']],
            ['slug' => 'german',  'flag_emoji' => '🇩🇪', 'icon_name' => 'BookOpenText',  'title' => 'German Language (A1–B2)', 'duration' => '12 Weeks per level', 'badge' => 'Visa-Ready',      'description' => 'Goethe-aligned curriculum to prepare you for studies, work, and life in Germany.',    'color_class' => 'bg-amber-50 text-amber-600', 'benefits' => ['Goethe Institute syllabus', 'Native-speaking conversation labs', 'Exam preparation included', 'Pathway to Ausbildung & study']],
            ['slug' => 'korean',  'flag_emoji' => '🇰🇷', 'icon_name' => 'MessageCircle', 'title' => 'Korean Language (TOPIK)', 'duration' => '10 Weeks',           'badge' => 'EPS-TOPIK Ready', 'description' => 'From Hangul basics to TOPIK exam mastery — perfect for Korea study or EPS programs.', 'color_class' => 'bg-rose-50 text-rose-600',   'benefits' => ['TOPIK I & II preparation', 'Cultural immersion sessions', 'EPS exam support', 'Real conversation practice']],
        ];

        (new LanguageSectionSeeder)->run();

        foreach ($languages as $i => $lang) {
            $slug = $lang['slug'];
            unset($lang['slug']);

            LanguageProgram::create(array_merge($lang, [
                'language_section_id' => LanguageSection::query()->where('slug', $slug)->value('id'),
                'sort_order' => $i + 1,
                'is_active' => true,
            ]));
        }

        // ── Pages SEO ────────────────────────────────────────────────────
        $pages = [
            ['slug' => 'home',         'name' => 'Home',        'meta_title' => "Russell's International | Study Abroad, Skills & Career Services"],
            ['slug' => 'about',        'name' => 'About Us',    'meta_title' => "About Us | Russell's International"],
            ['slug' => 'skills',       'name' => 'Skills',      'meta_title' => "IT Courses & Skills Training | Russell's International"],
            ['slug' => 'study-abroad', 'name' => 'Study Abroad', 'meta_title' => "Study Abroad Consultancy | Russell's International"],
            ['slug' => 'languages',    'name' => 'Languages',   'meta_title' => "IELTS, German & Korean Language Programs | Russell's International"],
            ['slug' => 'careers',      'name' => 'Careers',     'meta_title' => "Jobs & Internships | Russell's International"],
            ['slug' => 'events',       'name' => 'Events',      'meta_title' => "Events, News & Gallery | Russell's International"],
            ['slug' => 'contact',      'name' => 'Contact',     'meta_title' => "Contact Us | Russell's International"],
        ];

        foreach ($pages as $p) {
            Page::create(array_merge($p, ['is_active' => true]));
        }
    }
}
