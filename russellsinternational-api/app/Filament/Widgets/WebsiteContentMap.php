<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CareerApplicationResource;
use App\Filament\Resources\ContactSubmissionResource;
use App\Filament\Resources\CourseResource;
use App\Filament\Resources\EventResource;
use App\Filament\Resources\GalleryPhotoResource;
use App\Filament\Resources\HeroSlideResource;
use App\Filament\Resources\InternshipResource;
use App\Filament\Resources\JobResource;
use App\Filament\Resources\LanguageProgramResource;
use App\Filament\Resources\NavigationItemResource;
use App\Filament\Resources\PageResource;
use App\Filament\Resources\PageSectionResource;
use App\Filament\Resources\SettingResource;
use App\Filament\Resources\StudyDestinationResource;
use App\Filament\Resources\TeamMemberResource;
use App\Filament\Resources\TestimonialResource;
use App\Filament\Resources\TickerItemResource;
use App\Filament\Resources\WhyChooseUsItemResource;
use Filament\Widgets\Widget;

class WebsiteContentMap extends Widget
{
    protected static ?int $sort = 2;

    protected static string $view = 'filament.widgets.website-content-map';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return [
            'groups' => [
                [
                    'title' => 'Home page',
                    'description' => 'Sections currently visible on the public homepage.',
                    'items' => [
                        ['label' => 'Hero carousel', 'hint' => 'Large first-screen slides, text, buttons and hero images.', 'url' => HeroSlideResource::getUrl()],
                        ['label' => 'Announcement ticker', 'hint' => 'Moving announcement strip below the hero.', 'url' => TickerItemResource::getUrl()],
                        ['label' => 'Why choose us cards', 'hint' => 'Small benefit cards under the homepage hero.', 'url' => WhyChooseUsItemResource::getUrl()],
                        ['label' => 'Dual focus section', 'hint' => 'Study abroad and skills preview cards. Use page: home.', 'url' => PageSectionResource::getUrl()],
                        ['label' => 'News carousel', 'hint' => 'Featured news and events shown on homepage.', 'url' => EventResource::getUrl()],
                        ['label' => 'Testimonials', 'hint' => 'Student/client reviews on the homepage.', 'url' => TestimonialResource::getUrl()],
                        ['label' => 'CTA and contact content', 'hint' => 'Global CTA uses page: global. Contact/footer values are settings.', 'url' => PageSectionResource::getUrl()],
                    ],
                ],
                [
                    'title' => 'Inner pages',
                    'description' => 'Hero sections, page-specific content and list data for each route.',
                    'items' => [
                        ['label' => 'Page heroes and SEO', 'hint' => 'Hero data lives in page sections with section key: hero. SEO lives in page registry.', 'url' => PageResource::getUrl()],
                        ['label' => 'Editable page sections', 'hint' => 'About sections, global CTA, and custom structured homepage fields.', 'url' => PageSectionResource::getUrl()],
                        ['label' => 'About team', 'hint' => 'Team members shown on the About page.', 'url' => TeamMemberResource::getUrl()],
                        ['label' => 'Skills courses', 'hint' => 'Paid and NAVTTC courses shown on the Skills page.', 'url' => CourseResource::getUrl()],
                        ['label' => 'Study destinations', 'hint' => 'Country cards and study-abroad details.', 'url' => StudyDestinationResource::getUrl()],
                        ['label' => 'Language programs', 'hint' => 'Language course cards shown on the Languages page.', 'url' => LanguageProgramResource::getUrl()],
                        ['label' => 'Jobs', 'hint' => 'Open positions shown on the Careers page.', 'url' => JobResource::getUrl()],
                        ['label' => 'Internships', 'hint' => 'Internship cards shown on the Careers page.', 'url' => InternshipResource::getUrl()],
                        ['label' => 'Events and news', 'hint' => 'Events and news cards shown on Events page and homepage carousel.', 'url' => EventResource::getUrl()],
                        ['label' => 'Gallery photos', 'hint' => 'Gallery images shown on the Events page.', 'url' => GalleryPhotoResource::getUrl()],
                    ],
                ],
                [
                    'title' => 'Global site controls',
                    'description' => 'Shared header, footer, forms and incoming leads.',
                    'items' => [
                        ['label' => 'Header and footer navigation', 'hint' => 'Menu links, footer columns, badges and external links.', 'url' => NavigationItemResource::getUrl()],
                        ['label' => 'Footer/contact settings', 'hint' => 'Phone, email, address, social links, map and site text.', 'url' => SettingResource::getUrl()],
                        ['label' => 'Contact enquiries', 'hint' => 'Messages submitted from the public contact form.', 'url' => ContactSubmissionResource::getUrl()],
                        ['label' => 'Career applications', 'hint' => 'Job and internship applications with CV uploads.', 'url' => CareerApplicationResource::getUrl()],
                    ],
                ],
                [
                    'title' => 'Current frontend status',
                    'description' => 'These older admin modules are not shown in the current public pages.',
                    'items' => [
                        ['label' => 'Services', 'hint' => 'API still exists, but the current frontend does not render the old Services section.', 'url' => null],
                        ['label' => 'Stats', 'hint' => 'API still exists, but the current homepage does not render the old Stats strip.', 'url' => null],
                    ],
                ],
            ],
        ];
    }
}
