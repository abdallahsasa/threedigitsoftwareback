<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountingProjectResource\Pages;
use App\Models\AccountingClient;
use App\Models\AccountingProject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AccountingProjectResource extends Resource
{
    protected static ?string $model = AccountingProject::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Accounting & Finance';

    protected static ?string $navigationLabel = 'Projects & Profitability';

    protected static ?string $modelLabel = 'Project';

    protected static ?string $pluralModelLabel = 'Projects';

    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->canViewAccounting());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Project Title')
                    ->required(),
                Forms\Components\Select::make('client_id')
                    ->label('Client')
                    ->options(AccountingClient::pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\TextInput::make('total_price')
                    ->label('Contract Value (USD)')
                    ->numeric()
                    ->prefix('$')
                    ->helperText('Total agreed contract price; recorded as realized revenue only upon actual collection.'),
                Forms\Components\TextInput::make('direct_costs')
                    ->label('Direct Project Costs & Labor (USD)')
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\Select::make('status')
                    ->label('Project Status')
                    ->options([
                        'in_progress' => 'In Progress',
                        'done' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('in_progress')
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('Project Notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Project Name')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Contract Value')
                    ->money('USD')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('total_collected')
                    ->label('Collected')
                    ->money('USD')
                    ->color('success')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('remaining_balance')
                    ->label('Contract Balance')
                    ->money('USD')
                    ->badge()
                    ->color(fn ($state) => (float)$state > 0 ? 'warning' : 'gray'),

                Tables\Columns\TextColumn::make('direct_costs')
                    ->label('Direct Costs')
                    ->money('USD')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('net_profit')
                    ->label('Project Gross Margin')
                    ->money('USD')
                    ->color('info')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'done' => 'Completed',
                        'cancelled' => 'Cancelled',
                        default => 'In Progress',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'done' => 'success',
                        'cancelled' => 'danger',
                        default => 'warning',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'done' => 'Completed',
                        'in_progress' => 'In Progress',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountingProjects::route('/'),
        ];
    }
}
