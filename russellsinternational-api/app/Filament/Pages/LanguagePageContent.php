<?php

namespace App\Filament\Pages;

use App\Filament\Resources\LanguageProgramResource;
use App\Filament\Resources\LanguageSectionResource;

class LanguagePageContent extends WebsiteContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-language';

    protected static ?string $navigationLabel = 'Language Page';

    protected static ?string $title = 'Language Page';

    protected static ?string $slug = 'website/language-page';

    protected static ?int $navigationSort = 5;

    protected function contentGroups(): array
    {
        return [
            [
                'title' => 'Language page sections',
                'description' => 'Hero and language program cards shown on the Languages page.',
                'sections' => [
                    $this->pageRecord('Page SEO and status', 'Page registry, active status and SEO metadata for Languages.', 'languages'),
                    $this->pageSection('Page hero', 'Top hero image, title, subtitle and eyebrow for Languages.', 'languages', 'hero'),
                    $this->resourceList('Language sections (tabs)', 'The tabs on the Languages page. Add a section here to offer a new language.', LanguageSectionResource::class, 'Manage Sections'),
                    $this->resourceList('Language programs', 'Language course cards shown on the Languages page.', LanguageProgramResource::class, 'Manage Programs'),
                    $this->pageSection('Global CTA banner', 'Shared call-to-action banner shown near the bottom.', 'global', 'cta'),
                ],
            ],
        ];
    }
}
