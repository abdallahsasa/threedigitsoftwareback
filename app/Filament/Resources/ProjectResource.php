<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\Concerns\Translatable;

class ProjectResource extends Resource
{
    use Translatable;
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('slug')
                    ->required(),
                Forms\Components\TextInput::make('client_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('project_category_id')
                    ->numeric(),
                Forms\Components\TextInput::make('industry_id')
                    ->numeric(),
                Forms\Components\TextInput::make('launch_year')
                    ->maxLength(255),
                Forms\Components\TextInput::make('live_url')
                    ->maxLength(255),
                Forms\Components\TextInput::make('overview'),
                Forms\Components\TextInput::make('business_challenge'),
                Forms\Components\TextInput::make('proposed_solution'),
                Forms\Components\TextInput::make('engineering_challenges'),
                Forms\Components\TextInput::make('results'),
                Forms\Components\FileUpload::make('main_image')
                    ->image()
                    ->disk('public_uploads')
                    ->directory('projects'),
                Forms\Components\TextInput::make('desktop_mockup')
                    ->maxLength(255),
                Forms\Components\TextInput::make('mobile_mockup')
                    ->maxLength(255),
                Forms\Components\TextInput::make('preview_video')
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_featured')
                    ->required(),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('draft'),
                Forms\Components\TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('project_category_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('industry_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('launch_year')
                    ->searchable(),
                Tables\Columns\TextColumn::make('live_url')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('main_image')->disk('public_uploads'),
                Tables\Columns\TextColumn::make('desktop_mockup')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mobile_mockup')
                    ->searchable(),
                Tables\Columns\TextColumn::make('preview_video')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
