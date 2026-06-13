<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudyDestinationResource\Pages;
use App\Models\StudyDestination;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StudyDestinationResource extends Resource
{
    protected static ?string $model = StudyDestination::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = 'Study Abroad';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Country Details')->schema([
                Forms\Components\TextInput::make('flag_emoji')->required()->placeholder('🇬🇧'),
                Forms\Components\TextInput::make('country')->required(),
                Forms\Components\TextInput::make('partner_unis_count')->required()->placeholder('40+'),
                Forms\Components\TextInput::make('highlight_unis')->required()->placeholder('Oxford, Cambridge, UCL'),
                Forms\Components\TextInput::make('intake_periods')->required()->placeholder('Sept & Jan'),
                Forms\Components\TextInput::make('visa_success_rate')->required()->placeholder('98% success rate'),
                Forms\Components\Textarea::make('description')->required()->rows(3)->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Services & Scholarships')->schema([
                Forms\Components\Repeater::make('services')
                    ->schema([Forms\Components\TextInput::make('item')->required()])
                    ->defaultItems(5)->collapsible(),
                Forms\Components\Repeater::make('scholarships')
                    ->schema([Forms\Components\TextInput::make('item')->required()])
                    ->defaultItems(2)->collapsible(),
            ])->columns(2),

            Forms\Components\Section::make('Media & Status')->schema([
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->visibility('public')
                    ->directory('destinations')
                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif', 'image/avif', 'image/bmp', 'image/svg+xml'])
                    ->maxSize(2048)
                    ->imagePreviewHeight('180')
                    ->imageEditor()
                    ->downloadable()
                    ->openable(),
                Forms\Components\TextInput::make('sort_order')->numeric()->minValue(0)->maxValue(255)->default(0),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('flag_emoji')->label('Flag'),
                Tables\Columns\TextColumn::make('country')->searchable(),
                Tables\Columns\TextColumn::make('partner_unis_count'),
                Tables\Columns\TextColumn::make('visa_success_rate'),
                Tables\Columns\ToggleColumn::make('is_active'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudyDestinations::route('/'),
            'create' => Pages\CreateStudyDestination::route('/create'),
            'edit' => Pages\EditStudyDestination::route('/{record}/edit'),
        ];
    }
}
