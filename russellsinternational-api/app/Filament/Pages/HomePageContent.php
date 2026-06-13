<?php

namespace App\Filament\Pages;

use App\Filament\Resources\EventResource;
use App\Filament\Resources\HeroSlideResource;
use App\Filament\Resources\TestimonialResource;
use App\Filament\Resources\TickerItemResource;
use App\Filament\Resources\WhyChooseUsItemResource;

class HomePageContent extends WebsiteContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Home Page';

    protected static ?string $title = 'Home Page';

    protected static ?string $slug = 'website/home-page';

    protected static ?int $navigationSort = 1;

    protected function contentGroups(): array
    {
        return [
            [
                'title' => 'Home page sections',
                'description' => 'These are the sections currently rendered on the public home page, in website order.',
                'sections' => [
                    $this->resourceList('Hero carousel', 'First screen slider: image, heading, description and buttons.', HeroSlideResource::class, 'Manage Slides'),
                    $this->resourceList('Announcement ticker', 'Moving announcement strip below the hero carousel.', TickerItemResource::class, 'Manage Ticker'),
                    $this->resourceList('Why choose us cards', 'Benefit cards below the homepage hero area.', WhyChooseUsItemResource::class, 'Manage Cards'),
                    $this->pageSection('Dual focus heading', 'Main heading and intro copy for the Study Abroad and Skills preview section.', 'home', 'dual_focus'),
                    $this->pageSection('Study abroad preview card', 'Homepage Study Abroad card image, text, countries, CTA and badge fields.', 'home', 'dual_focus_study'),
                    $this->pageSection('Skills preview card', 'Homepage Skills card image, text, course highlights, CTA and badge fields.', 'home', 'dual_focus_skills'),
                    $this->resourceList('Homepage news carousel', 'Featured events/news cards shown on the homepage.', EventResource::class, 'Manage Events'),
                    $this->resourceList('Testimonials', 'Student/client reviews shown on the homepage.', TestimonialResource::class, 'Manage Reviews'),
                    $this->pageSection('Global CTA banner', 'Shared call-to-action banner shown on home and inner pages.', 'global', 'cta'),
                ],
            ],
        ];
    }
}
