<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Events & Gallery';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Content')->schema([
                Forms\Components\Select::make('content_type')
                    ->options(['event' => 'Event', 'news' => 'News'])
                    ->required(),
                Forms\Components\TextInput::make('tag')
                    ->required()
                    ->placeholder('Workshop, Seminar, Admissions, News'),
                Forms\Components\TextInput::make('tag_color')
                    ->default('bg-blue-50 text-blue-700')
                    ->label('Tag Tailwind Color Classes'),
                Forms\Components\TextInput::make('title')->required()->maxLength(250)->columnSpanFull(),
                Forms\Components\DatePicker::make('event_date')->label('Event Date'),
                Forms\Components\TextInput::make('venue'),
                Forms\Components\TextInput::make('capacity')->numeric(),
                Forms\Components\Textarea::make('short_description')->required()->rows(2)->maxLength(300),
                Forms\Components\Textarea::make('full_details')->rows(5)->maxLength(2000),
            ])->columns(2),

            Forms\Components\Section::make('Media & Status')->schema([
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->visibility('public')
                    ->directory('events')
                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif', 'image/avif', 'image/bmp', 'image/svg+xml'])
                    ->maxSize(2048)
                    ->imagePreviewHeight('180')
                    ->imageEditor()
                    ->downloadable()
                    ->openable(),
                Forms\Components\Toggle::make('is_featured'),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('image')->disk('public')->square(),
            Tables\Columns\TextColumn::make('content_type')->badge()
                ->color(fn ($state) => $state === 'event' ? 'primary' : 'info'),
            Tables\Columns\TextColumn::make('tag'),
            Tables\Columns\TextColumn::make('title')->searchable()->limit(40),
            Tables\Columns\TextColumn::make('event_date')->date()->sortable(),
            Tables\Columns\IconColumn::make('is_featured')->boolean(),
            Tables\Columns\ToggleColumn::make('is_active'),
        ])
            ->filters([
                Tables\Filters\SelectFilter::make('content_type')
                    ->options(['event' => 'Events', 'news' => 'News']),
                Tables\Filters\TernaryFilter::make('is_featured'),
            ])
            ->defaultSort('event_date', 'desc')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
