<?php

namespace App\Filament\Pages;

use App\Filament\Resources\CareerApplicationResource;
use App\Filament\Resources\InternshipResource;
use App\Filament\Resources\JobResource;

class CareersPageContent extends WebsiteContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'Careers Page';

    protected static ?string $title = 'Careers Page';

    protected static ?string $slug = 'website/careers-page';

    protected static ?int $navigationSort = 6;

    protected function contentGroups(): array
    {
        return [
            [
                'title' => 'Careers page sections',
                'description' => 'Hero, jobs, internships and application submissions.',
                'sections' => [
                    $this->pageRecord('Page SEO and status', 'Page registry, active status and SEO metadata for Careers.', 'careers'),
                    $this->pageSection('Page hero', 'Top hero image, title, subtitle and eyebrow for Careers.', 'careers', 'hero'),
                    $this->resourceList('Jobs', 'Open positions shown on the Careers page.', JobResource::class, 'Manage Jobs'),
                    $this->resourceList('Internships', 'Internship cards shown on the Careers page.', InternshipResource::class, 'Manage Internships'),
                    $this->resourceList('Career applications', 'Applications submitted from the public career form.', CareerApplicationResource::class, 'Review Applications'),
                    $this->pageSection('Global CTA banner', 'Shared call-to-action banner shown near the bottom.', 'global', 'cta'),
                ],
            ],
        ];
    }
}
