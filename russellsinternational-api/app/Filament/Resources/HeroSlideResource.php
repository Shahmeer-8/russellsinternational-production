<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroSlideResource\Pages;
use App\Models\HeroSlide;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HeroSlideResource extends Resource
{
    protected static ?string $model = HeroSlide::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = 'Home Page';

    protected static ?string $navigationLabel = 'Hero Carousel';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Slide Content')->schema([
                Forms\Components\TextInput::make('eyebrow')
                    ->label('Eyebrow Label')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Admissions Open 2026'),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(200)
                    ->placeholder('Your Global Career Starts Here'),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->maxLength(300)
                    ->rows(2),
            ])->columns(1),

            Forms\Components\Section::make('CTA Buttons')->schema([
                Forms\Components\TextInput::make('cta_label')->required()->default('Explore Programs'),
                Forms\Components\TextInput::make('cta_url')->required()->default('/skills'),
                Forms\Components\TextInput::make('secondary_cta_label')->default('Free Consultation'),
                Forms\Components\TextInput::make('secondary_cta_url')->default('/#contact'),
            ])->columns(2),

            Forms\Components\Section::make('Image & Settings')->schema([
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->visibility('public')
                    ->directory('hero-slides')
                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif', 'image/avif', 'image/bmp', 'image/svg+xml'])
                    ->maxSize(2048)
                    ->imagePreviewHeight('180')
                    ->imageEditor()
                    ->downloadable()
                    ->openable()
                    ->required(),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()->minValue(0)->maxValue(255)->default(0),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('image')->disk('public')->square(),
                Tables\Columns\TextColumn::make('eyebrow')->searchable(),
                Tables\Columns\TextColumn::make('title')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('cta_label'),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
                Tables\Columns\ToggleColumn::make('is_active'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHeroSlides::route('/'),
            'create' => Pages\CreateHeroSlide::route('/create'),
            'edit' => Pages\EditHeroSlide::route('/{record}/edit'),
        ];
    }
}
