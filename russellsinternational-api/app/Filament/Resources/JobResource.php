<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobResource\Pages;
use App\Models\Job;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JobResource extends Resource
{
    protected static ?string $model = Job::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = 'Careers';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Job Details')->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(200),
                Forms\Components\TextInput::make('company')->required()->maxLength(150),
                Forms\Components\TextInput::make('location')->required()->maxLength(150),
                Forms\Components\Select::make('type')
                    ->options(['Full-Time' => 'Full-Time', 'Part-Time' => 'Part-Time', 'Contract' => 'Contract', 'Remote' => 'Remote'])
                    ->required(),
                Forms\Components\TextInput::make('salary')->placeholder('PKR 80K–120K'),
                Forms\Components\DatePicker::make('deadline')->label('Application Deadline'),
            ])->columns(2),

            Forms\Components\Section::make('Description & Requirements')->schema([
                Forms\Components\Textarea::make('description')->required()->rows(4)->maxLength(1000),
                Forms\Components\Repeater::make('requirements')
                    ->schema([Forms\Components\TextInput::make('item')->required()])
                    ->defaultItems(3)
                    ->collapsible(),
            ]),

            Forms\Components\Section::make('Settings')->schema([
                Forms\Components\TextInput::make('application_email')->email(),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->searchable(),
            Tables\Columns\TextColumn::make('company')->searchable(),
            Tables\Columns\TextColumn::make('type')->badge(),
            Tables\Columns\TextColumn::make('salary'),
            Tables\Columns\TextColumn::make('deadline')->date()->sortable(),
            Tables\Columns\ToggleColumn::make('is_active'),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(true),
        ])
            ->filters([Tables\Filters\TernaryFilter::make('is_active')])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobs::route('/'),
            'create' => Pages\CreateJob::route('/create'),
            'edit' => Pages\EditJob::route('/{record}/edit'),
        ];
    }
}
