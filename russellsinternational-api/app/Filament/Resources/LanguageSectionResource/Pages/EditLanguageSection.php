<?php

namespace App\Filament\Resources\LanguageSectionResource\Pages;

use App\Filament\Resources\LanguageSectionResource;
use App\Models\LanguageSection;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLanguageSection extends EditRecord
{
    protected static string $resource = LanguageSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                // The foreign key nulls on delete, which would drop this section's
                // programs off the website with no warning. Refuse instead, and say
                // exactly what to do about it.
                ->before(function (Actions\DeleteAction $action, LanguageSection $record) {
                    $count = $record->programs()->count();

                    if ($count === 0) {
                        return;
                    }

                    Notification::make()
                        ->danger()
                        ->title('This section still has programs')
                        ->body("Move or delete this section's {$count} program(s) first, otherwise they would disappear from the website.")
                        ->persistent()
                        ->send();

                    $action->cancel();
                }),
        ];
    }
}
