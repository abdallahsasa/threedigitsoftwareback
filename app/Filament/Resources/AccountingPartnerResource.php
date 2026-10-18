<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountingPartnerResource\Pages;
use App\Models\AccountingPartner;
use App\Models\AccountingTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AccountingPartnerResource extends Resource
{
    protected static ?string $model = AccountingPartner::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Accounting & Finance';

    protected static ?string $navigationLabel = 'Partner Accounts & Debts';

    protected static ?string $modelLabel = 'Partner';

    protected static ?string $pluralModelLabel = 'Partners';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->canViewAccounting());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name_en')
                    ->label('Partner Name (English)')
                    ->required(),
                Forms\Components\TextInput::make('name_ar')
                    ->label('Partner Name (Arabic)')
                    ->required(),
                Forms\Components\TextInput::make('initial_debt_balance')
                    ->label('Cutover Verified Debt Balance (USD)')
                    ->numeric()
                    ->prefix('$')
                    ->required()
                    ->helperText('Confirmed baseline debt as of October 13, 2026.'),
                Forms\Components\Textarea::make('notes')
                    ->label('Settlement & Cutover Notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_en')
                    ->label('Partner')
                    ->weight('bold')
                    ->description(fn (AccountingPartner $record) => $record->name_ar),

                Tables\Columns\TextColumn::make('initial_debt_balance')
                    ->label('Cutover Verified Debt')
                    ->money('USD')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('new_funding')
                    ->label('Additional Funding')
                    ->state(function (AccountingPartner $record) {
                        return (float) AccountingTransaction::where('partner_id', $record->id)
                            ->where('transaction_type', 'partner_funding')
                            ->where('status', 'completed')
                            ->whereDate('transaction_date', '>', '2026-10-13')
                            ->sum('amount');
                    })
                    ->money('USD')
                    ->color('info'),

                Tables\Columns\TextColumn::make('personal_expenses')
                    ->label('Out-of-Pocket Expenses')
                    ->state(function (AccountingPartner $record) {
                        return (float) AccountingTransaction::where('partner_id', $record->id)
                            ->where('transaction_type', 'partner_personal_expense')
                            ->where('status', 'completed')
                            ->whereDate('transaction_date', '>', '2026-10-13')
                            ->sum('amount');
                    })
                    ->money('USD')
                    ->color('warning'),

                Tables\Columns\TextColumn::make('repayments')
                    ->label('Debt Repayments')
                    ->state(function (AccountingPartner $record) {
                        return (float) AccountingTransaction::where('partner_id', $record->id)
                            ->where('transaction_type', 'partner_debt_repayment')
                            ->where('status', 'completed')
                            ->whereDate('transaction_date', '>', '2026-10-13')
                            ->sum('amount');
                    })
                    ->money('USD')
                    ->color('success'),

                Tables\Columns\TextColumn::make('current_debt')
                    ->label('Outstanding Company Debt')
                    ->state(fn (AccountingPartner $record) => $record->calculated_debt_balance)
                    ->money('USD')
                    ->weight('extrabold')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('drawings')
                    ->label('Drawings & Profit Draws')
                    ->state(fn (AccountingPartner $record) => $record->total_drawings)
                    ->money('USD')
                    ->badge()
                    ->color('purple'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Partner Statement')
                    ->modalHeading(fn (AccountingPartner $record) => "Financial Statement: {$record->name_en}")
                    ->infolist([
                        Infolists\Components\Section::make('Partner Financial Position Summary')
                            ->schema([
                                Infolists\Components\TextEntry::make('name_en')->label('English Name'),
                                Infolists\Components\TextEntry::make('name_ar')->label('Arabic Name'),
                                Infolists\Components\TextEntry::make('initial_debt_balance')->label('Cutover Verified Debt (13 Oct 2026)')->money('USD'),
                                Infolists\Components\TextEntry::make('calculated_debt_balance')->label('Current Net Company Debt Owed to Partner')->money('USD')->color('danger'),
                                Infolists\Components\TextEntry::make('total_drawings')->label('Total Drawings & Equity Distributions (Segregated)')->money('USD')->color('purple'),
                                Infolists\Components\TextEntry::make('notes')->label('Settlement & Transition Notes')->columnSpanFull(),
                            ])
                            ->columns(2),
                    ]),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountingPartners::route('/'),
        ];
    }
}
