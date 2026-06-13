<?php

namespace App\Filament\Pages;

use App\Filament\Resources\CourseResource;

class SkillsPageContent extends WebsiteContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Skills Page';

    protected static ?string $title = 'Skills Page';

    protected static ?string $slug = 'website/skills-page';

    protected static ?int $navigationSort = 3;

    protected function contentGroups(): array
    {
        return [
            [
                'title' => 'Skills page sections',
                'description' => 'Hero and course content shown on the Skills page.',
                'sections' => [
                    $this->pageRecord('Page SEO and status', 'Page registry, active status and SEO metadata for Skills.', 'skills'),
                    $this->pageSection('Page hero', 'Top hero image, title, subtitle and eyebrow for Skills.', 'skills', 'hero'),
                    $this->resourceList('Courses', 'Paid and NAVTTC courses shown in the Skills page tabs.', CourseResource::class, 'Manage Courses'),
                    $this->pageSection('Global CTA banner', 'Shared call-to-action banner shown near the bottom.', 'global', 'cta'),
                ],
            ],
        ];
    }
}
