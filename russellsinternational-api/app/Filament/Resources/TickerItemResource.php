<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TickerItemResource\Pages;
use App\Models\TickerItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TickerItemResource extends Resource
{
    protected static ?string $model = TickerItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = 'Home Page';

    protected static ?string $navigationLabel = 'Announcement Ticker';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('emoji')->maxLength(20),
            Forms\Components\TextInput::make('text')->required()->maxLength(200),
            Forms\Components\TextInput::make('sort_order')->numeric()->minValue(0)->maxValue(255)->default(0),
            Forms\Components\Toggle::make('is_active')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->reorderable('sort_order')->columns([
            Tables\Columns\TextColumn::make('emoji'),
            Tables\Columns\TextColumn::make('text')->searchable(),
            Tables\Columns\TextColumn::make('sort_order')->sortable(),
            Tables\Columns\ToggleColumn::make('is_active'),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickerItems::route('/'),
            'create' => Pages\CreateTickerItem::route('/create'),
            'edit' => Pages\EditTickerItem::route('/{record}/edit'),
        ];
    }
}
