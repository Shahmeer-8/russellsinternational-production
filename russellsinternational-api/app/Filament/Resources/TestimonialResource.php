<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Testimonial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Home Page';

    protected static ?string $navigationLabel = 'Homepage Testimonials';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')
                ->options(['written' => 'Written Review', 'video' => 'YouTube Video'])
                ->required()->live(),
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('program')->required()->placeholder('Study Abroad – UK'),
            Forms\Components\Textarea::make('quote')
                ->rows(3)
                ->maxLength(500)
                ->visible(fn (Forms\Get $get) => $get('type') === 'written'),
            Forms\Components\FileUpload::make('image')
                ->image()
                ->disk('public')
                ->visibility('public')
                ->directory('testimonials')
                ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif', 'image/avif', 'image/bmp', 'image/svg+xml'])
                ->maxSize(2048)
                ->imagePreviewHeight('180')
                ->imageEditor()
                ->downloadable()
                ->openable()
                ->visible(fn (Forms\Get $get) => $get('type') === 'written'),
            Forms\Components\TextInput::make('youtube_id')
                ->label('YouTube Video URL or ID')
                ->placeholder('https://youtu.be/OMLItB0fYqU')
                ->helperText('Paste full YouTube URL or only the 11-character video ID. It will be saved as an embeddable ID.')
                ->visible(fn (Forms\Get $get) => $get('type') === 'video'),
            Forms\Components\Select::make('rating')
                ->options([1 => '★', 2 => '★★', 3 => '★★★', 4 => '★★★★', 5 => '★★★★★'])
                ->default(5),
            Forms\Components\TextInput::make('sort_order')->numeric()->minValue(0)->maxValue(255)->default(0),
            Forms\Components\Toggle::make('is_active')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('type')->badge()
                    ->color(fn ($state) => $state === 'written' ? 'primary' : 'danger'),
                Tables\Columns\ImageColumn::make('image')->disk('public')->circular(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('program'),
                Tables\Columns\TextColumn::make('rating'),
                Tables\Columns\ToggleColumn::make('is_active'),
            ])
            ->filters([Tables\Filters\SelectFilter::make('type')->options(['written' => 'Written', 'video' => 'Video'])])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
