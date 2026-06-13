<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = 'Header, Footer & Settings';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('key')->required()->unique('settings', 'key', ignoreRecord: true)->maxLength(100),
            Forms\Components\TextInput::make('label')->required(),
            Forms\Components\Select::make('group')
                ->options(['general' => 'General', 'contact' => 'Contact', 'social' => 'Social Links', 'seo' => 'SEO', 'footer' => 'Footer'])
                ->required(),
            Forms\Components\Select::make('type')
                ->options(['text' => 'Text', 'textarea' => 'Textarea', 'url' => 'URL', 'image' => 'Image', 'boolean' => 'Boolean'])
                ->required()->live(),
            Forms\Components\TextInput::make('value')
                ->visible(fn (Forms\Get $get) => in_array($get('type'), ['text', 'url'])),
            Forms\Components\Textarea::make('value')
                ->rows(3)
                ->visible(fn (Forms\Get $get) => $get('type') === 'textarea'),
            Forms\Components\FileUpload::make('value')
                ->image()->directory('settings')
                ->visible(fn (Forms\Get $get) => $get('type') === 'image'),
            Forms\Components\Toggle::make('value')
                ->visible(fn (Forms\Get $get) => $get('type') === 'boolean'),
            Forms\Components\Textarea::make('description')->rows(2)->label('Admin Description (internal)'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('key')->searchable()->fontFamily('mono'),
            Tables\Columns\TextColumn::make('label')->searchable(),
            Tables\Columns\TextColumn::make('group')->badge(),
            Tables\Columns\TextColumn::make('type')->badge(),
            Tables\Columns\TextColumn::make('value')->limit(50),
        ])
            ->filters([Tables\Filters\SelectFilter::make('group')
                ->options(['general' => 'General', 'contact' => 'Contact', 'social' => 'Social', 'seo' => 'SEO', 'footer' => 'Footer'])])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
