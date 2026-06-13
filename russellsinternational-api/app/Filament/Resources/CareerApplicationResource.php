<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CareerApplicationResource\Pages;
use App\Models\CareerApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CareerApplicationResource extends Resource
{
    protected static ?string $model = CareerApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Forms & Applications';

    protected static ?int $navigationSort = 2;

    public static function getBadge(): ?string
    {
        $count = CareerApplication::where('status', 'new')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('status')
                ->options(['new' => 'New', 'reviewing' => 'Reviewing', 'shortlisted' => 'Shortlisted', 'rejected' => 'Rejected', 'hired' => 'Hired'])
                ->required(),
            Forms\Components\Textarea::make('admin_notes')->rows(3)->label('Internal Notes'),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Application Details')->schema([
                Infolists\Components\TextEntry::make('application_type')->badge(),
                Infolists\Components\TextEntry::make('position_title'),
                Infolists\Components\TextEntry::make('name'),
                Infolists\Components\TextEntry::make('email'),
                Infolists\Components\TextEntry::make('phone'),
                Infolists\Components\TextEntry::make('portfolio_url')->url(fn (?string $state): ?string => $state),
                Infolists\Components\TextEntry::make('cover_letter')->columnSpanFull(),
                Infolists\Components\TextEntry::make('cv_path')
                    ->label('CV Download')
                    ->formatStateUsing(fn ($state) => $state ? 'View CV' : 'Not submitted')
                    ->url(fn (CareerApplication $record): ?string => $record->cv_url),
                Infolists\Components\TextEntry::make('created_at')->dateTime(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('application_type')->badge(),
            Tables\Columns\TextColumn::make('position_title')->searchable()->limit(30),
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('email')->searchable(),
            Tables\Columns\SelectColumn::make('status')
                ->options(['new' => 'New', 'reviewing' => 'Reviewing', 'shortlisted' => 'Shortlisted', 'rejected' => 'Rejected', 'hired' => 'Hired']),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
        ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('application_type')->options(['job' => 'Job', 'internship' => 'Internship']),
                Tables\Filters\SelectFilter::make('status')->options(['new' => 'New', 'reviewing' => 'Reviewing', 'shortlisted' => 'Shortlisted']),
            ])
            ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCareerApplications::route('/'),
            'view' => Pages\ViewCareerApplication::route('/{record}'),
            'edit' => Pages\EditCareerApplication::route('/{record}/edit'),
        ];
    }
}
