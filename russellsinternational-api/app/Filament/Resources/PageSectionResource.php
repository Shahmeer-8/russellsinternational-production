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

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = 'Page Heroes & Sections';

    protected static ?string $navigationLabel = 'Editable Page Sections';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Where this appears on the website')->schema([
                Forms\Components\Select::make('page_slug')
                    ->label('Website page')
                    ->options([
                        'global' => 'Global shared sections',
                        'home' => 'Home page',
                        'about' => 'About page',
                        'skills' => 'Skills page',
                        'study-abroad' => 'Study Abroad page',
                        'languages' => 'Languages page',
                        'careers' => 'Careers page',
                        'events' => 'Events page',
                    ])
                    ->searchable()
                    ->native(false)
                    ->helperText('Choose the public page where this section is rendered.')
                    ->required(),
                Forms\Components\TextInput::make('section_key')
                    ->label('Section key')
                    ->datalist([
                        'hero',
                        'cta',
                        'dual_focus',
                        'dual_focus_study',
                        'dual_focus_skills',
                        'campus_life',
                        'founder_message',
                        'foundation',
                    ])
                    ->helperText('Frontend key. Common live keys: hero, cta, dual_focus, dual_focus_study, dual_focus_skills, campus_life, founder_message, foundation.')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('name')
                    ->label('Admin label')
                    ->helperText('Human-friendly name shown only inside the admin panel.')
                    ->required()
                    ->maxLength(150),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Sort order')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(65535)
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->label('Visible on website')
                    ->default(true),
            ])->columns(3),

            Forms\Components\Section::make('Editable content')->schema([
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

            Forms\Components\Section::make('Buttons / calls to action')->schema([
                Forms\Components\TextInput::make('cta_label')->maxLength(100),
                Forms\Components\TextInput::make('cta_url')->maxLength(500),
                Forms\Components\TextInput::make('secondary_cta_label')->maxLength(100),
                Forms\Components\TextInput::make('secondary_cta_url')->maxLength(500),
            ])->columns(2),

            Forms\Components\Section::make('Advanced structured fields')->schema([
                Forms\Components\KeyValue::make('extra')
                    ->helperText('Optional extra fields used by specific sections, such as badge or footnote.')
                    ->keyLabel('Field')
                    ->valueLabel('Value')
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('items')
                    ->helperText('For homepage dual focus cards: country_1_code, country_1_name, country_1_meta, course_1_title, course_1_meta, etc.')
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
                Tables\Columns\TextColumn::make('page_slug')->label('Website page')->badge()->searchable(),
                Tables\Columns\TextColumn::make('section_key')->label('Section key')->fontFamily('mono')->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Admin label')->searchable(),
                Tables\Columns\TextColumn::make('title')->limit(40),
                Tables\Columns\TextColumn::make('sort_order')->label('Sort')->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')->label('Visible'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('page_slug')
                    ->label('Website page')
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
                Tables\Filters\TernaryFilter::make('is_active')->label('Visible on website'),
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
