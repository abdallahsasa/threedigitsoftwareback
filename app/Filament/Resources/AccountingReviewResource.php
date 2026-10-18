<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountingReviewResource\Pages;
use App\Models\AccountingAuditLog;
use App\Models\AccountingImportItem;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AccountingReviewResource extends Resource
{
    protected static ?string $model = AccountingImportItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Accounting & Finance';

    protected static ?string $navigationLabel = 'Audit & Review Queue';

    protected static ?string $modelLabel = 'Review Queue Item';

    protected static ?string $pluralModelLabel = 'Audit & Review Queue';

    protected static ?int $navigationSort = 6;

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->canViewAccounting());
    }

    public static function getNavigationBadge(): ?string
    {
        $count = AccountingImportItem::where('review_status', 'pending_review')->count();
        return $count > 0 ? (string)$count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('source_location')
                    ->label('Source Sheet')
                    ->state(fn (AccountingImportItem $record) => "{$record->sheet_name} (Row {$record->row_index})")
                    ->fontFamily('mono')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('item_description')
                    ->label('Excel Record Description')
                    ->state(function (AccountingImportItem $record) {
                        return $record->raw_data['Vendor / Service']
                            ?? $record->raw_data['Project / Service']
                            ?? $record->raw_data['Project Name']
                            ?? $record->raw_data['Client Name']
                            ?? 'Financial Item';
                    })
                    ->weight('bold')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('raw_data', 'like', "%{$search}%");
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('USD')
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('raw_payer')
                    ->label('Original Payer')
                    ->state(function (AccountingImportItem $record) {
                        return $record->raw_data['Paid By']
                            ?? $record->raw_data['founders ']
                            ?? '-';
                    }),

                Tables\Columns\TextColumn::make('review_reason')
                    ->label('Review Justification')
                    ->limit(50)
                    ->color('danger')
                    ->tooltip(fn ($state) => $state),

                Tables\Columns\TextColumn::make('proposed_type')
                    ->label('Proposed Classification')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('review_status')
                    ->label('Audit Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending_review' => 'Pending Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected / Excluded',
                        'ignored' => 'Ignored',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending_review' => 'danger',
                        'approved' => 'success',
                        'rejected' => 'gray',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('final_decision')
                    ->label('Final Decision')
                    ->placeholder('Unresolved'),

                Tables\Columns\TextColumn::make('reviewer.name')
                    ->label('Auditor')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('reviewed_at')
                    ->label('Decision Date')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('-'),
            ])
            ->defaultSort('review_status', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('review_status')
                    ->label('Audit Status')
                    ->options([
                        'pending_review' => 'Pending Review Only',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending_review'),

                Tables\Filters\SelectFilter::make('sheet_name')
                    ->label('Sheet Name')
                    ->options([
                        'Income' => 'Income',
                        'Expenses' => 'Expenses',
                        'Projects' => 'Projects',
                        'Sheet1' => 'Sheet1',
                    ]),
            ])
            ->actions([
                // 1. Quick Approve Action
                Tables\Actions\Action::make('approve')
                    ->label('Approve Proposed')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Proposed Accounting Classification')
                    ->modalDescription(fn (AccountingImportItem $record) => "Approve this transaction as ({$record->proposed_type})?")
                    ->form([
                        Forms\Components\Textarea::make('decision_notes')
                            ->label('Audit Decision Notes')
                            ->placeholder('Add any relevant documentation notes...'),
                    ])
                    ->action(function (AccountingImportItem $record, array $data) {
                        $record->update([
                            'review_status' => 'approved',
                            'final_decision' => $record->proposed_type,
                            'decision_notes' => $data['decision_notes'] ?? 'Approved as proposed by management',
                            'reviewed_by' => Auth::id(),
                            'reviewed_at' => now(),
                        ]);

                        AccountingAuditLog::create([
                            'user_id' => Auth::id(),
                            'action' => 'approve_review_item',
                            'entity_type' => AccountingImportItem::class,
                            'entity_id' => $record->id,
                            'description' => "Approved review item from sheet {$record->sheet_name} row {$record->row_index} as {$record->proposed_type}",
                            'new_values' => $record->toArray(),
                        ]);

                        Notification::make()
                            ->title('Item approved successfully')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (AccountingImportItem $record) => $record->review_status === 'pending_review'),

                // 2. Reclassify Action
                Tables\Actions\Action::make('reclassify')
                    ->label('Reclassify')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->modalHeading('Reclassify Transaction & Approve')
                    ->form([
                        Forms\Components\Select::make('new_type')
                            ->label('New Accounting Classification')
                            ->options([
                                'partner_personal_expense' => 'Partner Personal Expense (Increases Debt to Partner)',
                                'company_expense' => 'Company Expense (Paid from Treasury)',
                                'partner_distribution' => 'Partner Drawing / Profit Draw (Segregated from Debt)',
                                'partner_project_labor' => 'Partner Project Execution Labor',
                                'partner_debt_repayment' => 'Partner Debt Repayment',
                                'client_collection' => 'Client Collection',
                                'capital_injection' => 'Founding Capital Injection (Equity)',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('decision_notes')
                            ->label('Reclassification Justification')
                            ->required(),
                    ])
                    ->action(function (AccountingImportItem $record, array $data) {
                        $record->update([
                            'proposed_type' => $data['new_type'],
                            'review_status' => 'approved',
                            'final_decision' => $data['new_type'],
                            'decision_notes' => $data['decision_notes'],
                            'reviewed_by' => Auth::id(),
                            'reviewed_at' => now(),
                        ]);

                        AccountingAuditLog::create([
                            'user_id' => Auth::id(),
                            'action' => 'reclassify_review_item',
                            'entity_type' => AccountingImportItem::class,
                            'entity_id' => $record->id,
                            'description' => "Reclassified review item {$record->sheet_name}:R{$record->row_index} to {$data['new_type']}",
                            'new_values' => $record->toArray(),
                        ]);

                        Notification::make()
                            ->title('Classification updated and approved')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (AccountingImportItem $record) => $record->review_status === 'pending_review'),

                // 3. Reject / Ignore Action
                Tables\Actions\Action::make('reject')
                    ->label('Exclude')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Exclude Transaction from Active Ledger')
                    ->form([
                        Forms\Components\Textarea::make('decision_notes')
                            ->label('Exclusion Reason')
                            ->required(),
                    ])
                    ->action(function (AccountingImportItem $record, array $data) {
                        $record->update([
                            'review_status' => 'rejected',
                            'final_decision' => 'rejected',
                            'decision_notes' => $data['decision_notes'],
                            'reviewed_by' => Auth::id(),
                            'reviewed_at' => now(),
                        ]);

                        AccountingAuditLog::create([
                            'user_id' => Auth::id(),
                            'action' => 'reject_review_item',
                            'entity_type' => AccountingImportItem::class,
                            'entity_id' => $record->id,
                            'description' => "Excluded review item {$record->sheet_name}:R{$record->row_index}",
                            'new_values' => $record->toArray(),
                        ]);

                        Notification::make()
                            ->title('Item excluded from active ledger')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (AccountingImportItem $record) => $record->review_status === 'pending_review'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountingReviews::route('/'),
        ];
    }
}
