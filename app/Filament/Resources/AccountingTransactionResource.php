<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountingTransactionResource\Pages;
use App\Models\AccountingAccount;
use App\Models\AccountingClient;
use App\Models\AccountingPartner;
use App\Models\AccountingProject;
use App\Models\AccountingTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AccountingTransactionResource extends Resource
{
    protected static ?string $model = AccountingTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Accounting & Finance';

    protected static ?string $navigationLabel = 'Transactions & Journal';

    protected static ?string $modelLabel = 'Transaction';

    protected static ?string $pluralModelLabel = 'Transactions';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->canViewAccounting());
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->canCreateTransactions());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transaction Details')
                    ->description('Select the transaction nature accurately. The system automatically maintains double-entry balance and segregates company capital from partner personal debts.')
                    ->schema([
                        Forms\Components\Select::make('transaction_type')
                            ->label('Accounting Transaction Type')
                            ->options([
                                'client_collection' => 'Client Collection (Increases Cash / Settles Receivable)',
                                'partner_personal_expense' => 'Personal Out-of-Pocket Expense (Increases Partner Debt; Cash Unchanged)',
                                'company_expense' => 'Company Expense (Paid from Treasury; Decreases Cash)',
                                'partner_funding' => 'Refundable Partner Funding (Increases Cash & Partner Debt; Not Revenue)',
                                'partner_debt_repayment' => 'Partner Debt Repayment (Decreases Cash & Partner Debt; Not Expense)',
                                'partner_distribution' => 'Partner Drawing / Equity Distribution (Decreases Cash; Does Not Settle Debt)',
                                'partner_project_labor' => 'Partner Project Execution Labor (Direct Project Cost)',
                                'capital_injection' => 'Founding Capital Injection (Equity Contribution)',
                                'internal_transfer' => 'Internal Account Transfer',
                                'reconciliation_adjustment' => 'Reconciliation Adjustment',
                            ])
                            ->required()
                            ->live()
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('amount')
                            ->label('Amount (USD)')
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->minValue(0.01)
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('transaction_date')
                            ->label('Transaction Date')
                            ->default(now())
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Select::make('partner_id')
                            ->label('Target Partner')
                            ->options(AccountingPartner::pluck('name_en', 'id'))
                            ->searchable()
                            ->visible(fn (Get $get) => in_array($get('transaction_type'), [
                                'partner_personal_expense',
                                'partner_funding',
                                'partner_debt_repayment',
                                'partner_distribution',
                                'partner_project_labor',
                            ]))
                            ->required(fn (Get $get) => in_array($get('transaction_type'), [
                                'partner_personal_expense',
                                'partner_funding',
                                'partner_debt_repayment',
                                'partner_distribution',
                            ]))
                            ->columnSpan(1),

                        Forms\Components\Select::make('client_id')
                            ->label('Associated Client')
                            ->options(AccountingClient::pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->visible(fn (Get $get) => in_array($get('transaction_type'), ['client_collection', 'partner_project_labor', 'company_expense']))
                            ->columnSpan(1),

                        Forms\Components\Select::make('project_id')
                            ->label('Associated Project')
                            ->options(fn (Get $get) => $get('client_id')
                                ? AccountingProject::where('client_id', $get('client_id'))->pluck('name', 'id')
                                : AccountingProject::pluck('name', 'id'))
                            ->searchable()
                            ->columnSpan(1),

                        Forms\Components\Select::make('expense_category')
                            ->label('Expense Category')
                            ->options([
                                'Office' => 'Office, Hospitality & Supplies',
                                'Rent' => 'Office Space Rent',
                                'Software' => 'Software, Digital Tools & Subscriptions',
                                'Salaries' => 'Partner Project Labor & Execution Salaries',
                                'Marketing' => 'Marketing, Ads & Referral Commissions',
                                'Equipment' => 'Office Equipment & Fixed Assets (Inverters, Batteries)',
                                'Other' => 'Other Operating Expenses',
                            ])
                            ->visible(fn (Get $get) => in_array($get('transaction_type'), [
                                'partner_personal_expense',
                                'company_expense',
                                'partner_project_labor',
                            ]))
                            ->columnSpan(1),

                        Forms\Components\Select::make('money_source')
                            ->label('Actual Source of Funds')
                            ->options([
                                'company_funds' => 'Company Treasury / Bank',
                                'personal_abdullah' => 'Abdullah Personal Funds',
                                'personal_amer' => 'Amer Personal Funds',
                                'othman' => 'Othman (Separate Account)',
                                'undetermined' => 'Undetermined / Pending Audit',
                            ])
                            ->default(fn (Get $get) => match ($get('transaction_type')) {
                                'partner_personal_expense' => 'personal_abdullah',
                                default => 'company_funds',
                            })
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'Cash' => 'Cash in Hand',
                                'Bank Transfer' => 'Bank Transfer',
                                'Western Union' => 'Western Union',
                                'Credit Card' => 'Credit / Debit Card',
                                'Wise' => 'Wise Account',
                                'Other' => 'Other',
                            ])
                            ->default('Cash')
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('notes')
                            ->label('Description & Notes')
                            ->rows(3)
                            ->columnSpan(2),

                        Forms\Components\FileUpload::make('attachments')
                            ->label('Supporting Receipts & Invoices')
                            ->multiple()
                            ->directory('accounting/attachments')
                            ->columnSpan(2),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_number')
                    ->label('Ref #')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Date')
                    ->date('Y-m-d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('transaction_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'client_collection' => 'Client Collection',
                        'partner_funding' => 'Partner Funding',
                        'partner_personal_expense' => 'Out-of-Pocket Expense',
                        'company_expense' => 'Company Expense',
                        'partner_debt_repayment' => 'Debt Repayment',
                        'partner_distribution' => 'Partner Drawing',
                        'partner_project_labor' => 'Project Labor',
                        'capital_injection' => 'Founding Capital',
                        'internal_transfer' => 'Internal Transfer',
                        'reconciliation_adjustment' => 'Reconciliation Adj.',
                        default => ucwords(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'client_collection' => 'success',
                        'partner_funding' => 'info',
                        'partner_personal_expense' => 'warning',
                        'company_expense' => 'danger',
                        'partner_debt_repayment' => 'primary',
                        'partner_distribution' => 'gray',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold')
                    ->color(fn (AccountingTransaction $record): string => match ($record->transaction_type) {
                        'client_collection', 'partner_funding' => 'success',
                        'company_expense', 'partner_debt_repayment', 'partner_distribution' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('party_name')
                    ->label('Counterparty')
                    ->state(fn (AccountingTransaction $record) => $record->partner?->name_en
                        ?: ($record->client?->name ?: ($record->payer_name ?: '-')))
                    ->searchable(),

                Tables\Columns\TextColumn::make('money_source')
                    ->label('Source of Funds')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'company_funds' => 'Company Treasury',
                        'personal_abdullah' => 'Abdullah Personal',
                        'personal_amer' => 'Amer Personal',
                        'othman' => 'Othman',
                        default => $state ? ucwords(str_replace('_', ' ', $state)) : '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'company_funds' => 'success',
                        'personal_abdullah', 'personal_amer' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Description')
                    ->limit(35)
                    ->tooltip(fn ($state) => $state),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => 'Posted',
                        'reversed' => 'Reversed',
                        'pending_review' => 'Pending Review',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'reversed' => 'danger',
                        'pending_review' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('transaction_type')
                    ->label('Transaction Type')
                    ->options([
                        'client_collection' => 'Client Collection',
                        'partner_personal_expense' => 'Personal Out-of-Pocket Expense',
                        'company_expense' => 'Company Expense',
                        'partner_funding' => 'Partner Funding',
                        'partner_debt_repayment' => 'Partner Debt Repayment',
                        'partner_distribution' => 'Partner Drawing / Distribution',
                    ]),

                Tables\Filters\SelectFilter::make('partner_id')
                    ->label('Partner')
                    ->options(AccountingPartner::pluck('name_en', 'id')),

                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Client')
                    ->options(AccountingClient::pluck('name', 'id')),

                Tables\Filters\SelectFilter::make('money_source')
                    ->label('Source of Funds')
                    ->options([
                        'company_funds' => 'Company Treasury',
                        'personal_abdullah' => 'Abdullah Personal',
                        'personal_amer' => 'Amer Personal',
                    ]),

                Tables\Filters\Filter::make('transaction_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From Date'),
                        Forms\Components\DatePicker::make('until')->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('transaction_date', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('transaction_date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('View Journal Entry'),

                Tables\Actions\Action::make('reverse')
                    ->label('Reverse Entry')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Execute Corrective Reversal Entry')
                    ->modalDescription('The system will generate a balanced reversing journal entry that neutralizes this transaction and logs the reason in the audit trail.')
                    ->form([
                        Forms\Components\Textarea::make('reversal_reason')
                            ->label('Reversal Justification')
                            ->required(),
                    ])
                    ->visible(fn (AccountingTransaction $record) => $record->status !== 'reversed' && $record->journal_entry_id)
                    ->action(function (AccountingTransaction $record, array $data) {
                        try {
                            $reversal = $record->reverse($data['reversal_reason'], Auth::id());
                            Notification::make()
                                ->title('Transaction reversed successfully')
                                ->body("Generated reversing journal entry #{$reversal->entry_number}")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Reversal Error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountingTransactions::route('/'),
            'create' => Pages\CreateAccountingTransaction::route('/create'),
            'edit' => Pages\EditAccountingTransaction::route('/{record}/edit'),
        ];
    }
}
