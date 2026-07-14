<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Filament\Resources\LeadResource\RelationManagers;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('full_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('company_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('country')
                    ->maxLength(255),
                Forms\Components\TextInput::make('project_type')
                    ->maxLength(255),
                Forms\Components\Textarea::make('project_description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('existing_website')
                    ->maxLength(255),
                Forms\Components\Textarea::make('required_features')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('preferred_launch_date')
                    ->maxLength(255),
                Forms\Components\TextInput::make('budget')
                    ->maxLength(255),
                Forms\Components\TextInput::make('utm_source')
                    ->maxLength(255),
                Forms\Components\TextInput::make('utm_medium')
                    ->maxLength(255),
                Forms\Components\TextInput::make('utm_campaign')
                    ->maxLength(255),
                Forms\Components\TextInput::make('referral_source')
                    ->maxLength(255),
                Forms\Components\TextInput::make('landing_page')
                    ->maxLength(255),
                Forms\Components\TextInput::make('selected_language')
                    ->maxLength(255),
                Forms\Components\TextInput::make('user_agent')
                    ->maxLength(255),
                Forms\Components\TextInput::make('ip_address')
                    ->maxLength(255),
                Forms\Components\Toggle::make('privacy_consent')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('country')
                    ->searchable(),
                Tables\Columns\TextColumn::make('project_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('existing_website')
                    ->searchable(),
                Tables\Columns\TextColumn::make('preferred_launch_date')
                    ->searchable(),
                Tables\Columns\TextColumn::make('budget')
                    ->searchable(),
                Tables\Columns\TextColumn::make('utm_source')
                    ->searchable(),
                Tables\Columns\TextColumn::make('utm_medium')
                    ->searchable(),
                Tables\Columns\TextColumn::make('utm_campaign')
                    ->searchable(),
                Tables\Columns\TextColumn::make('referral_source')
                    ->searchable(),
                Tables\Columns\TextColumn::make('landing_page')
                    ->searchable(),
                Tables\Columns\TextColumn::make('selected_language')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_agent')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->searchable(),
                Tables\Columns\IconColumn::make('privacy_consent')
                    ->boolean(),
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
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
