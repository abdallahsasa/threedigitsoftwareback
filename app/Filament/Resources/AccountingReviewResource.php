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

    protected static ?string $navigationGroup = 'نظام المحاسبة والمالية';

    protected static ?string $navigationLabel = 'المراجعة والمطابقة المالية';

    protected static ?string $modelLabel = 'حركة معلقة للمراجعة';

    protected static ?string $pluralModelLabel = 'سجل المراجعة والمطابقة';

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
                    ->label('المصدر')
                    ->state(fn (AccountingImportItem $record) => "{$record->sheet_name} (صف {$record->row_index})")
                    ->fontFamily('mono')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('item_description')
                    ->label('البيان في ملف الإكسل')
                    ->state(function (AccountingImportItem $record) {
                        return $record->raw_data['Vendor / Service']
                            ?? $record->raw_data['Project / Service']
                            ?? $record->raw_data['Project Name']
                            ?? $record->raw_data['Client Name']
                            ?? 'سجل مالي';
                    })
                    ->weight('bold')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('raw_data', 'like', "%{$search}%");
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('USD')
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('raw_payer')
                    ->label('الدافع الأصلي')
                    ->state(function (AccountingImportItem $record) {
                        return $record->raw_data['Paid By']
                            ?? $record->raw_data['founders ']
                            ?? '-';
                    }),

                Tables\Columns\TextColumn::make('review_reason')
                    ->label('سبب التعليق للمراجعة')
                    ->limit(50)
                    ->color('danger')
                    ->tooltip(fn ($state) => $state),

                Tables\Columns\TextColumn::make('proposed_type')
                    ->label('التصنيف المقترح')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('review_status')
                    ->label('حالة المراجعة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending_review' => 'معلق للمراجعة',
                        'approved' => 'معتمد',
                        'rejected' => 'مستبعد / ملغى',
                        'ignored' => 'متجاهل',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending_review' => 'danger',
                        'approved' => 'success',
                        'rejected' => 'gray',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('final_decision')
                    ->label('القرار المعتمد')
                    ->placeholder('لم يُحسم بعد'),

                Tables\Columns\TextColumn::make('reviewer.name')
                    ->label('المدقق')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('reviewed_at')
                    ->label('تاريخ القرار')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('-'),
            ])
            ->defaultSort('review_status', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('review_status')
                    ->label('حالة المراجعة')
                    ->options([
                        'pending_review' => 'الحركات المعلقة فقط',
                        'approved' => 'المعتمدة',
                        'rejected' => 'المستبعدة',
                    ])
                    ->default('pending_review'),

                Tables\Filters\SelectFilter::make('sheet_name')
                    ->label('اسم ورقة العمل')
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
                    ->label('اعتماد التصنيف')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('اعتماد التصنيف المقترح للحركة')
                    ->modalDescription(fn (AccountingImportItem $record) => "هل تريد اعتماد تصنيف هذه الحركة كـ ({$record->proposed_type})؟")
                    ->form([
                        Forms\Components\Textarea::make('decision_notes')
                            ->label('ملاحظات الاعتماد والقرار النهائي')
                            ->placeholder('اكتب أي ملاحظة توثيقية إضافية...'),
                    ])
                    ->action(function (AccountingImportItem $record, array $data) {
                        $record->update([
                            'review_status' => 'approved',
                            'final_decision' => $record->proposed_type,
                            'decision_notes' => $data['decision_notes'] ?? 'تم الاعتماد وفق التصنيف المقترح',
                            'reviewed_by' => Auth::id(),
                            'reviewed_at' => now(),
                        ]);

                        AccountingAuditLog::create([
                            'user_id' => Auth::id(),
                            'action' => 'approve_review_item',
                            'entity_type' => AccountingImportItem::class,
                            'entity_id' => $record->id,
                            'description' => "اعتماد الحركة المعلقة من ورقة {$record->sheet_name} صف {$record->row_index} كتصنيف {$record->proposed_type}",
                            'new_values' => $record->toArray(),
                        ]);

                        Notification::make()
                            ->title('تم اعتماد الحركة بنجاح')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (AccountingImportItem $record) => $record->review_status === 'pending_review'),

                // 2. Reclassify Action
                Tables\Actions\Action::make('reclassify')
                    ->label('إعادة تصنيف')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->modalHeading('تعديل تصنيف الحركة واعتمادها')
                    ->form([
                        Forms\Components\Select::make('new_type')
                            ->label('التصنيف المحاسبي الجديد')
                            ->options([
                                'partner_personal_expense' => 'مصروف شخصي للشريك (يزيد دينه لدى الشركة)',
                                'company_expense' => 'مصروف دفعته الشركة من أموالها',
                                'partner_distribution' => 'سحب شريك / توزيع أرباح (منفصل عن الدين)',
                                'partner_project_labor' => 'أجر فعلي للشريك مقابل تنفيذ مشروع',
                                'partner_debt_repayment' => 'سداد جزء من دين الشريك',
                                'client_collection' => 'تحصيل إيراد عميل',
                                'capital_injection' => 'رأس مال تأسيسي (حقوق ملكية)',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('decision_notes')
                            ->label('سبب إعادة التصنيف والقرار النهائي')
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
                            'description' => "إعادة تصنيف الحركة المعلقة {$record->sheet_name}:R{$record->row_index} إلى {$data['new_type']}",
                            'new_values' => $record->toArray(),
                        ]);

                        Notification::make()
                            ->title('تم تعديل التصنيف واعتماد القرار بنجاح')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (AccountingImportItem $record) => $record->review_status === 'pending_review'),

                // 3. Reject / Ignore Action
                Tables\Actions\Action::make('reject')
                    ->label('استبعاد')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('استبعاد الحركة من القيود النشطة')
                    ->form([
                        Forms\Components\Textarea::make('decision_notes')
                            ->label('سبب الاستبعاد')
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
                            'description' => "استبعاد الحركة المعلقة {$record->sheet_name}:R{$record->row_index}",
                            'new_values' => $record->toArray(),
                        ]);

                        Notification::make()
                            ->title('تم استبعاد الحركة')
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
