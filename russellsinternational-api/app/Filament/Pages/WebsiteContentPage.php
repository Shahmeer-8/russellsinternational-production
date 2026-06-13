<?php

namespace App\Filament\Pages;

use App\Filament\Resources\PageResource;
use App\Filament\Resources\PageSectionResource;
use App\Models\Page;
use App\Models\PageSection;
use Filament\Pages\Page as FilamentPage;

abstract class WebsiteContentPage extends FilamentPage
{
    protected static string $view = 'filament.pages.website-content-page';

    protected static ?string $navigationGroup = null;

    protected ?string $maxContentWidth = '7xl';

    /**
     * @return array<int, array{title: string, description?: string, sections: array<int, array<string, mixed>>}>
     */
    abstract protected function contentGroups(): array;

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'groups' => $this->contentGroups(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function pageSection(string $title, string $description, string $pageSlug, string $sectionKey): array
    {
        $section = PageSection::query()
            ->where('page_slug', $pageSlug)
            ->where('section_key', $sectionKey)
            ->first();

        return [
            'title' => $title,
            'description' => $description,
            'meta' => "{$pageSlug} / {$sectionKey}",
            'status' => $section ? ($section->is_active ? 'Visible' : 'Hidden') : 'Missing',
            'statusColor' => $section ? ($section->is_active ? 'success' : 'warning') : 'danger',
            'previewTitle' => $section?->title ?: $section?->eyebrow,
            'previewBody' => $section?->subtitle ?: $section?->body,
            'previewImage' => $section?->image_url,
            'url' => $section
                ? PageSectionResource::getUrl('edit', ['record' => $section])
                : PageSectionResource::getUrl('create'),
            'action' => $section ? 'Edit Section' : 'Create Section',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function pageRecord(string $title, string $description, string $slug): array
    {
        $page = Page::query()->where('slug', $slug)->first();

        return [
            'title' => $title,
            'description' => $description,
            'meta' => "page / {$slug}",
            'status' => $page ? ($page->is_active ? 'Visible' : 'Hidden') : 'Missing',
            'statusColor' => $page ? ($page->is_active ? 'success' : 'warning') : 'danger',
            'previewTitle' => $page?->meta_title ?: $page?->name,
            'previewBody' => $page?->meta_description,
            'previewImage' => null,
            'url' => $page
                ? PageResource::getUrl('edit', ['record' => $page])
                : PageResource::getUrl('create'),
            'action' => $page ? 'Edit SEO' : 'Create Page',
        ];
    }

    /**
     * @param  class-string  $resource
     * @return array<string, mixed>
     */
    protected function resourceList(string $title, string $description, string $resource, string $action = 'Manage Items'): array
    {
        return [
            'title' => $title,
            'description' => $description,
            'meta' => 'multiple records',
            'status' => 'Editable',
            'statusColor' => 'info',
            'previewTitle' => null,
            'previewBody' => null,
            'previewImage' => null,
            'url' => $resource::getUrl(),
            'action' => $action,
        ];
    }
}
