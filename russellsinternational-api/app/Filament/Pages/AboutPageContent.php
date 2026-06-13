<?php

namespace App\Filament\Pages;

use App\Filament\Resources\TeamMemberResource;

class AboutPageContent extends WebsiteContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-information-circle';

    protected static ?string $navigationLabel = 'About Us Page';

    protected static ?string $title = 'About Us Page';

    protected static ?string $slug = 'website/about-us-page';

    protected static ?int $navigationSort = 2;

    protected function contentGroups(): array
    {
        return [
            [
                'title' => 'About page sections',
                'description' => 'Editable content currently used on the About Us page.',
                'sections' => [
                    $this->pageRecord('Page SEO and status', 'Page registry, active status and SEO metadata for About Us.', 'about'),
                    $this->pageSection('Page hero', 'Top hero image, title, subtitle and eyebrow for About Us.', 'about', 'hero'),
                    $this->pageSection('Campus life section', 'Main about section with image, heading, body and CTA.', 'about', 'campus_life'),
                    $this->pageSection('Founder message', 'Founder/message block with image and body copy.', 'about', 'founder_message'),
                    $this->pageSection('Foundation values', 'Mission, vision and values cards stored as structured items.', 'about', 'foundation'),
                    $this->resourceList('Team members', 'People cards shown on the About page.', TeamMemberResource::class, 'Manage Team'),
                    $this->pageSection('Global CTA banner', 'Shared call-to-action banner shown near the bottom.', 'global', 'cta'),
                ],
            ],
        ];
    }
}
