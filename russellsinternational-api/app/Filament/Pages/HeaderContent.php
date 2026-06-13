<?php

namespace App\Filament\Pages;

use App\Filament\Resources\NavigationItemResource;
use App\Filament\Resources\SettingResource;

class HeaderContent extends WebsiteContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $navigationLabel = 'Header';

    protected static ?string $title = 'Header';

    protected static ?string $slug = 'website/header';

    protected static ?int $navigationSort = 8;

    protected function contentGroups(): array
    {
        return [
            [
                'title' => 'Header controls',
                'description' => 'Header logo/site name, menu items and CTA link.',
                'sections' => [
                    $this->resourceList('Header navigation links', 'Top menu links, order, badges, external links and active status.', NavigationItemResource::class, 'Manage Navigation'),
                    $this->resourceList('Site name and global settings', 'Site name and shared settings used by the header.', SettingResource::class, 'Manage Settings'),
                ],
            ],
        ];
    }
}
