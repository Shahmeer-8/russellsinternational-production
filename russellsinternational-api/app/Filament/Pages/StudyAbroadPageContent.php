<?php

namespace App\Filament\Pages;

use App\Filament\Resources\StudyDestinationResource;

class StudyAbroadPageContent extends WebsiteContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationLabel = 'Study Abroad Page';

    protected static ?string $title = 'Study Abroad Page';

    protected static ?string $slug = 'website/study-abroad-page';

    protected static ?int $navigationSort = 4;

    protected function contentGroups(): array
    {
        return [
            [
                'title' => 'Study Abroad page sections',
                'description' => 'Hero and destination cards shown on the Study Abroad page.',
                'sections' => [
                    $this->pageRecord('Page SEO and status', 'Page registry, active status and SEO metadata for Study Abroad.', 'study-abroad'),
                    $this->pageSection('Page hero', 'Top hero image, title, subtitle and eyebrow for Study Abroad.', 'study-abroad', 'hero'),
                    $this->resourceList('Study destinations', 'Country cards and destination details.', StudyDestinationResource::class, 'Manage Destinations'),
                    $this->pageSection('Global CTA banner', 'Shared call-to-action banner shown near the bottom.', 'global', 'cta'),
                ],
            ],
        ];
    }
}
