<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LanguageProgram;
use App\Models\LanguageSection;
use Illuminate\Http\JsonResponse;

class LanguageSectionController extends Controller
{
    /**
     * Active sections with their active programs nested — one request for the
     * whole Languages page. Sections with no active programs are omitted so a
     * visitor never lands on an empty tab.
     */
    public function index(): JsonResponse
    {
        $sections = LanguageSection::active()
            ->with(['programs' => fn ($query) => $query->active()])
            ->get()
            ->filter(fn (LanguageSection $section) => $section->programs->isNotEmpty())
            ->map(fn (LanguageSection $section) => $this->sectionPayload($section))
            ->values()
            ->all();

        return response()->json(['success' => true, 'data' => $sections]);
    }

    /**
     * @return array<string, mixed>
     */
    private function sectionPayload(LanguageSection $section): array
    {
        return [
            'id' => $section->id,
            'slug' => $section->slug,
            'label' => $section->label,
            'short_label' => $section->short_label,
            'tab_label' => $section->tab_label,
            'heading' => $section->heading,
            'subtitle' => $section->subtitle,
            'icon_name' => $section->icon_name,
            'color_class' => $section->color_class,
            'sort_order' => $section->sort_order,
            'programs' => $section->programs
                ->map(fn (LanguageProgram $program) => $this->programPayload($program))
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function programPayload(LanguageProgram $program): array
    {
        return [
            'id' => $program->id,
            'title' => $program->title,
            'duration' => $program->duration,
            'badge' => $program->badge,
            'description' => $program->description,
            'benefits' => $program->benefits,
            'color_class' => $program->color_class,
            'icon_name' => $program->icon_name,
            'image_url' => $program->image_url,
        ];
    }
}
