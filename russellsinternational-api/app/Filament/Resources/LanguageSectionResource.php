<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LanguageSectionResource\Pages;
use App\Models\LanguageSection;
use App\Support\AdminChoices;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LanguageSectionResource extends Resource
{
    protected static ?string $model = LanguageSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = 'Languages';

    protected static ?string $navigationLabel = 'Language Sections';

    protected static ?string $modelLabel = 'language section';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Tab')->schema([
                Forms\Components\TextInput::make('label')
                    ->label('Tab name')
                    ->helperText('The tab text on the Languages page, for example "Arabic Tests".')
                    ->required()
                    ->maxLength(60),
                Forms\Components\TextInput::make('short_label')
                    ->label('Short tab name (mobile)')
                    ->helperText('Shorter text used on small screens. Leave blank to reuse the tab name.')
                    ->maxLength(30),
            ])->columns(2),

            Forms\Components\Section::make('Section heading')->schema([
                Forms\Components\TextInput::make('heading')
                    ->label('Heading')
                    ->helperText('The large heading shown under the tabs, for example "Arabic Language & Exams".')
                    ->required()
                    ->maxLength(120),
                Forms\Components\Textarea::make('subtitle')
                    ->label('Short description')
                    ->helperText('One line under the heading. Optional.')
                    ->rows(2)
                    ->maxLength(300),
            ])->columns(1),

            Forms\Components\Section::make('Look and order')->schema([
                Forms\Components\Select::make('icon_name')
                    ->label('Icon')
                    ->helperText("Shown on the tab and on this section's cards.")
                    ->options(AdminChoices::icons())
                    ->default('Globe')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('color_class')
                    ->label('Colour')
                    ->helperText('Background colour of the icon badge.')
                    ->options(AdminChoices::colors())
                    ->default('bg-blue-50 text-blue-600')
                    ->required(),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Order')
                    ->helperText('Lower numbers appear first.')
                    ->numeric()->minValue(0)->maxValue(255)->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->label('Visible on website')
                    ->helperText('Turn off to hide this tab without deleting it.')
                    ->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('label')->label('Tab')->searchable(),
                Tables\Columns\TextColumn::make('heading')->limit(40)->searchable(),
                Tables\Columns\TextColumn::make('icon_name')->label('Icon')->badge(),
                Tables\Columns\TextColumn::make('color_class')
                    ->label('Colour')
                    ->formatStateUsing(fn (?string $state) => AdminChoices::colors()[$state] ?? $state),
                Tables\Columns\TextColumn::make('programs_count')
                    ->label('Programs')
                    ->counts('programs')
                    ->badge()
                    ->color(fn ($state) => (int) $state > 0 ? 'success' : 'warning')
                    ->tooltip('A section with no programs is hidden from the website.'),
                Tables\Columns\TextColumn::make('sort_order')->label('Order')->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')->label('Visible'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Visible'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLanguageSections::route('/'),
            'create' => Pages\CreateLanguageSection::route('/create'),
            'edit' => Pages\EditLanguageSection::route('/{record}/edit'),
        ];
    }
}
