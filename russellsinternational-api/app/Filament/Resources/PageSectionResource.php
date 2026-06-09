<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageSectionResource\Pages;
use App\Models\PageSection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PageSectionResource extends Resource
{
    protected static ?string $model = PageSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Home Page';

    protected static ?string $navigationLabel = 'Page Sections';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Placement')->schema([
                Forms\Components\TextInput::make('page_slug')
                    ->helperText('Examples: home, about, skills, study-abroad, languages, careers, events, global')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('section_key')
                    ->helperText('Stable key used by the frontend, for example hero, cta, footer, process')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('name')->required()->maxLength(150),
                Forms\Components\TextInput::make('sort_order')->numeric()->minValue(0)->maxValue(65535)->default(0),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(3),

            Forms\Components\Section::make('Content')->schema([
                Forms\Components\TextInput::make('eyebrow')->maxLength(150),
                Forms\Components\TextInput::make('title')->maxLength(250),
                Forms\Components\Textarea::make('subtitle')->rows(2),
                Forms\Components\Textarea::make('body')->rows(6)->columnSpanFull(),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->visibility('public')
                    ->directory('page-sections')
                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif', 'image/avif', 'image/bmp', 'image/svg+xml'])
                    ->maxSize(2048)
                    ->imagePreviewHeight('180')
                    ->imageEditor()
                    ->downloadable()
                    ->openable(),
            ])->columns(2),

            Forms\Components\Section::make('Calls To Action')->schema([
                Forms\Components\TextInput::make('cta_label')->maxLength(100),
                Forms\Components\TextInput::make('cta_url')->maxLength(500),
                Forms\Components\TextInput::make('secondary_cta_label')->maxLength(100),
                Forms\Components\TextInput::make('secondary_cta_url')->maxLength(500),
            ])->columns(2),

            Forms\Components\Section::make('Structured Fields')->schema([
                Forms\Components\KeyValue::make('extra')
                    ->keyLabel('Field')
                    ->valueLabel('Value')
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('items')
                    ->helperText('For the homepage study/skills section use keys like country_1_code, country_1_name, country_1_meta, course_1_title, course_1_meta.')
                    ->keyLabel('Field')
                    ->valueLabel('Value')
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('page_slug')->badge()->searchable(),
                Tables\Columns\TextColumn::make('section_key')->fontFamily('mono')->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('title')->limit(40),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
                Tables\Columns\ToggleColumn::make('is_active'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('page_slug')
                    ->options([
                        'global' => 'Global',
                        'home' => 'Home',
                        'about' => 'About',
                        'skills' => 'Skills',
                        'study-abroad' => 'Study Abroad',
                        'languages' => 'Languages',
                        'careers' => 'Careers',
                        'events' => 'Events',
                    ]),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPageSections::route('/'),
            'create' => Pages\CreatePageSection::route('/create'),
            'edit' => Pages\EditPageSection::route('/{record}/edit'),
        ];
    }
}
