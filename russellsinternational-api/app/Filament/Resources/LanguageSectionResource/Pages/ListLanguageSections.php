<?php

namespace App\Filament\Resources\LanguageSectionResource\Pages;

use App\Filament\Resources\LanguageSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLanguageSections extends ListRecords
{
    protected static string $resource = LanguageSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('New section')];
    }
}
