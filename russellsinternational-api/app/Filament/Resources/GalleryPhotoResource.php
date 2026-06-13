<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryPhotoResource\Pages;
use App\Models\GalleryPhoto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GalleryPhotoResource extends Resource
{
    protected static ?string $model = GalleryPhoto::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = 'Events & Gallery';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('image')
                ->image()
                ->disk('public')
                ->visibility('public')
                ->required()
                ->directory('gallery')
                ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif', 'image/avif', 'image/bmp', 'image/svg+xml'])
                ->maxSize(2048)
                ->imagePreviewHeight('180')
                ->imageEditor()
                ->downloadable()
                ->openable()
                ->columnSpanFull(),
            Forms\Components\TextInput::make('alt_text')->required()->maxLength(200),
            Forms\Components\Select::make('category')
                ->options(['Campus' => 'Campus', 'Training' => 'Training', 'Events' => 'Events', 'Workshop' => 'Workshop', 'Seminar' => 'Seminar', 'Team' => 'Team', 'Graduation' => 'Graduation'])
                ->required(),
            Forms\Components\TextInput::make('sort_order')->numeric()->minValue(0)->maxValue(255)->default(0),
            Forms\Components\Toggle::make('is_active')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('image')->disk('public')->square(),
                Tables\Columns\TextColumn::make('alt_text')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('category')->badge(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
                Tables\Columns\ToggleColumn::make('is_active'),
            ])
            ->filters([Tables\Filters\SelectFilter::make('category')])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGalleryPhotos::route('/'),
            'create' => Pages\CreateGalleryPhoto::route('/create'),
            'edit' => Pages\EditGalleryPhoto::route('/{record}/edit'),
        ];
    }
}
