<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NavigationItemResource\Pages;
use App\Models\NavigationItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class NavigationItemResource extends Resource
{
    protected static ?string $model = NavigationItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $navigationGroup = 'Header, Footer & Settings';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Placement')->schema([
                Forms\Components\Select::make('location')
                    ->options(['header' => 'Header', 'footer' => 'Footer'])
                    ->required()
                    ->live(),
                Forms\Components\TextInput::make('footer_column')
                    ->label('Footer Column')
                    ->helperText('Examples: Quick Links, Programs, More')
                    ->maxLength(100)
                    ->visible(fn (Forms\Get $get) => $get('location') === 'footer'),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(4294967295)
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ])->columns(4),

            Forms\Components\Section::make('Link')->schema([
                Forms\Components\TextInput::make('label')
                    ->required()
                    ->maxLength(150)
                    ->live(onBlur: true),
                Forms\Components\TextInput::make('url')
                    ->required()
                    ->maxLength(500)
                    ->helperText('Use internal paths like /skills or full URLs like https://example.com')
                    ->live(onBlur: true),
                Forms\Components\Select::make('target')
                    ->options(['_self' => 'Same tab', '_blank' => 'New tab'])
                    ->default('_self')
                    ->required(),
            ])->columns(3),

            Forms\Components\Section::make('Badge')->schema([
                Forms\Components\TextInput::make('badge_label')
                    ->label('Badge Text')
                    ->maxLength(40)
                    ->placeholder('New, Hot, Free')
                    ->live(onBlur: true),
                Forms\Components\Select::make('badge_variant')
                    ->options(self::badgeVariantOptions())
                    ->default('accent')
                    ->visible(fn (Forms\Get $get) => filled($get('badge_label')))
                    ->live(),
                Forms\Components\Select::make('badge_animation')
                    ->options(self::badgeAnimationOptions())
                    ->default('none')
                    ->visible(fn (Forms\Get $get) => filled($get('badge_label')))
                    ->live(),
            ])->columns(3),

            Forms\Components\Section::make('Preview Before Save')->schema([
                Forms\Components\Placeholder::make('preview')
                    ->label('')
                    ->content(fn (Forms\Get $get) => new HtmlString(self::previewHtml($get))),
                Forms\Components\Toggle::make('preview_approved')
                    ->label('I reviewed the preview and approve this header/footer item')
                    ->required()
                    ->accepted()
                    ->dehydrated(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('location')->badge(),
                Tables\Columns\TextColumn::make('footer_column')->placeholder('Header')->toggleable(),
                Tables\Columns\TextColumn::make('label')->searchable(),
                Tables\Columns\TextColumn::make('url')->limit(35)->searchable(),
                Tables\Columns\TextColumn::make('badge_label')->badge(),
                Tables\Columns\TextColumn::make('badge_variant')->toggleable(),
                Tables\Columns\TextColumn::make('badge_animation')->toggleable(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
                Tables\Columns\ToggleColumn::make('is_active'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('location')
                    ->options(['header' => 'Header', 'footer' => 'Footer']),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNavigationItems::route('/'),
            'create' => Pages\CreateNavigationItem::route('/create'),
            'edit' => Pages\EditNavigationItem::route('/{record}/edit'),
        ];
    }

    private static function badgeVariantOptions(): array
    {
        return [
            'accent' => 'Accent Orange',
            'primary' => 'Primary Blue',
            'success' => 'Success Green',
            'info' => 'Info Sky',
            'warning' => 'Warning Amber',
            'danger' => 'Danger Red',
            'purple' => 'Purple',
            'dark' => 'Dark',
        ];
    }

    private static function badgeAnimationOptions(): array
    {
        return [
            'none' => 'No animation',
            'pulse' => 'Soft pulse',
            'blink' => 'Blink',
            'bounce' => 'Bounce',
            'ping' => 'Ping dot',
            'shake' => 'Shake',
            'glow' => 'Glow',
            'slide' => 'Slide shine',
        ];
    }

    private static function previewHtml(Forms\Get $get): string
    {
        $label = e($get('label') ?: 'Link Label');
        $url = e($get('url') ?: '/example');
        $location = e(ucfirst($get('location') ?: 'header'));
        $column = e($get('footer_column') ?: 'Footer Column');
        $badge = trim((string) $get('badge_label'));
        $variant = $get('badge_variant') ?: 'accent';
        $animation = $get('badge_animation') ?: 'none';

        $badgeHtml = $badge
            ? '<span style="'.self::badgeInlineStyle($variant, $animation).'">'.e($badge).'</span>'
            : '';

        return <<<HTML
<div style="border:1px solid #d8dee9;border-radius:12px;padding:16px;background:#f8fafc">
  <div style="font-size:12px;color:#64748b;margin-bottom:10px">Preview: {$location} item {$column}</div>
  <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
    <span style="font-weight:700;color:#0f172a">{$label}</span>
    {$badgeHtml}
  </div>
  <div style="font-size:12px;color:#64748b;margin-top:8px">URL: {$url}</div>
</div>
HTML;
    }

    private static function badgeInlineStyle(string $variant, string $animation): string
    {
        $colors = [
            'accent' => ['#fff7ed', '#c2410c', '#fed7aa'],
            'primary' => ['#eff6ff', '#1d4ed8', '#bfdbfe'],
            'success' => ['#ecfdf5', '#047857', '#a7f3d0'],
            'info' => ['#ecfeff', '#0e7490', '#a5f3fc'],
            'warning' => ['#fffbeb', '#b45309', '#fde68a'],
            'danger' => ['#fef2f2', '#b91c1c', '#fecaca'],
            'purple' => ['#faf5ff', '#7e22ce', '#e9d5ff'],
            'dark' => ['#0f172a', '#f8fafc', '#334155'],
        ];
        [$bg, $fg, $border] = $colors[$variant] ?? $colors['accent'];
        $shadow = match ($animation) {
            'glow', 'pulse', 'ping' => 'box-shadow:0 0 0 3px rgba(249,115,22,.18);',
            default => '',
        };

        return "display:inline-flex;align-items:center;border:1px solid {$border};background:{$bg};color:{$fg};font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;border-radius:999px;padding:3px 8px;{$shadow}";
    }
}
