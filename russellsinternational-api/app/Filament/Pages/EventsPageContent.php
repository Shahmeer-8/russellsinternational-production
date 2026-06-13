<?php

namespace App\Filament\Pages;

use App\Filament\Resources\EventResource;
use App\Filament\Resources\GalleryPhotoResource;

class EventsPageContent extends WebsiteContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Events Page';

    protected static ?string $title = 'Events Page';

    protected static ?string $slug = 'website/events-page';

    protected static ?int $navigationSort = 7;

    protected function contentGroups(): array
    {
        return [
            [
                'title' => 'Events page sections',
                'description' => 'Hero, events, news and gallery content shown on the Events page.',
                'sections' => [
                    $this->pageRecord('Page SEO and status', 'Page registry, active status and SEO metadata for Events.', 'events'),
                    $this->pageSection('Page hero', 'Top hero image, title, subtitle and eyebrow for Events.', 'events', 'hero'),
                    $this->resourceList('Events and news', 'Event and news cards shown on Events page and homepage carousel.', EventResource::class, 'Manage Events'),
                    $this->resourceList('Gallery photos', 'Gallery images shown on the Events page.', GalleryPhotoResource::class, 'Manage Gallery'),
                    $this->pageSection('Global CTA banner', 'Shared call-to-action banner shown near the bottom.', 'global', 'cta'),
                ],
            ],
        ];
    }
}
