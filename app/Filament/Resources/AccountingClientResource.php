<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountingClientResource\Pages;
use App\Models\AccountingClient;
use App\Models\AccountingTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AccountingClientResource extends Resource
{
    protected static ?string $model = AccountingClient::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Accounting & Finance';

    protected static ?string $navigationLabel = 'Clients & Receivables';

    protected static ?string $modelLabel = 'Client';

    protected static ?string $pluralModelLabel = 'Clients';

    protected static ?int $navigationSort = 4;

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
                    ->label('Client / Company Name')
                    ->required(),
                Forms\Components\TextInput::make('contact_person')
                    ->label('Contact Person'),
                Forms\Components\TextInput::make('phone')
                    ->label('Phone Number'),
                Forms\Components\TextInput::make('email')
                    ->label('Email Address')
                    ->email(),
                Forms\Components\TextInput::make('potential_receivable_amount')
                    ->label('Potential Unconfirmed Receivable (USD)')
                    ->numeric()
                    ->prefix('$')
                    ->helperText('Notes from Excel records (e.g. Kilani $350, FRS $315, Max Pro $150). Tracked as potential unconfirmed receivable until verified.'),
                Forms\Components\Textarea::make('notes')
                    ->label('Client Notes & Reconciliation Background')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Client Name')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_collected')
                    ->label('Realized Collections')
                    ->state(fn (AccountingClient $record) => (float) AccountingTransaction::where('client_id', $record->id)
                        ->where('transaction_type', 'client_collection')
                        ->where('status', 'completed')
                        ->sum('amount'))
                    ->money('USD')
                    ->color('success')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('potential_receivable_amount')
                    ->label('Potential Receivable')
                    ->money('USD')
                    ->badge()
                    ->color(fn ($state) => (float)$state > 0 ? 'warning' : 'gray'),

                Tables\Columns\TextColumn::make('projects_count')
                    ->label('Projects')
                    ->counts('projects')
                    ->badge(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Receivable Notes')
                    ->limit(40),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountingClients::route('/'),
        ];
    }
}
