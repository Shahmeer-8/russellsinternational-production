<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhyChooseUsItemResource\Pages;
use App\Models\WhyChooseUsItem;
use App\Support\AdminChoices;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WhyChooseUsItemResource extends Resource
{
    protected static ?string $model = WhyChooseUsItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = 'Home Page';

    protected static ?string $navigationLabel = 'Why Choose Us Cards';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('icon_name')
                ->label('Icon')
                ->helperText('Shown next to this reason on the home page.')
                ->options(AdminChoices::icons())
                ->searchable()
                ->required(),
            Forms\Components\TextInput::make('title')->required()->maxLength(200),
            Forms\Components\Select::make('color_class')
                ->label('Colour')
                ->helperText('Background colour of the icon badge.')
                ->options(AdminChoices::colors())
                ->default('bg-blue-50 text-blue-600'),
            Forms\Components\Textarea::make('description')->required()->rows(3)->columnSpanFull(),
            Forms\Components\TextInput::make('sort_order')->numeric()->minValue(0)->maxValue(255)->default(0),
            Forms\Components\Toggle::make('is_active')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->reorderable('sort_order')->columns([
            Tables\Columns\TextColumn::make('title')->searchable(),
            Tables\Columns\TextColumn::make('icon_name'),
            Tables\Columns\TextColumn::make('sort_order')->sortable(),
            Tables\Columns\ToggleColumn::make('is_active'),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWhyChooseUsItems::route('/'),
            'create' => Pages\CreateWhyChooseUsItem::route('/create'),
            'edit' => Pages\EditWhyChooseUsItem::route('/{record}/edit'),
        ];
    }
}
