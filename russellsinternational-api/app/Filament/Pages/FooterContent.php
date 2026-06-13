<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ContactSubmissionResource;
use App\Filament\Resources\NavigationItemResource;
use App\Filament\Resources\SettingResource;

class FooterContent extends WebsiteContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-window';

    protected static ?string $navigationLabel = 'Footer';

    protected static ?string $title = 'Footer';

    protected static ?string $slug = 'website/footer';

    protected static ?int $navigationSort = 9;

    protected function contentGroups(): array
    {
        return [
            [
                'title' => 'Footer and contact controls',
                'description' => 'Footer columns, contact details, social links, map and public contact submissions.',
                'sections' => [
                    $this->resourceList('Footer navigation columns', 'Footer menu groups, links, order and active status.', NavigationItemResource::class, 'Manage Navigation'),
                    $this->resourceList('Footer/contact settings', 'Phone, email, address, map iframe, social links, copyright and footer text.', SettingResource::class, 'Manage Settings'),
                    $this->pageSection('Global CTA banner', 'Shared CTA content used above the footer on public pages.', 'global', 'cta'),
                    $this->resourceList('Contact enquiries', 'Messages submitted from the public contact form.', ContactSubmissionResource::class, 'Review Enquiries'),
                ],
            ],
        ];
    }
}
