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

    protected static ?string $navigationGroup = 'نظام المحاسبة والمالية';

    protected static ?string $navigationLabel = 'الحركات والقيود المالية';

    protected static ?string $modelLabel = 'حركة مالية';

    protected static ?string $pluralModelLabel = 'الحركات المالية';

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
                Forms\Components\Section::make('البيانات الأساسية للحركة')
                    ->description('اختر نوع الحركة بدقة؛ حيث يقوم النظام تلقائياً بتوليد القيود المتوازنة وفصل أموال الشركة عن ديون الشركاء.')
                    ->schema([
                        Forms\Components\Select::make('transaction_type')
                            ->label('نوع الحركة المحاسبية')
                            ->options([
                                'client_collection' => 'تحصيل من عميل (يزيد الصندوق أو يسدد مستحق العميل)',
                                'partner_personal_expense' => 'مصروف شخصي دفعه الشريك من جيبه (يزيد دين الشريك دون إنقاص الصندوق)',
                                'company_expense' => 'مصروف دفعته الشركة من أموالها (ينقص الصندوق ويزيد المصروف)',
                                'partner_funding' => 'تمويل شريك قابل للاسترداد (يزيد الصندوق ويزيد دين الشريك دون زيادة إيرادات)',
                                'partner_debt_repayment' => 'سداد جزء من دين الشريك (ينقص الصندوق وينقص دين الشريك دون مصروف)',
                                'partner_distribution' => 'سحب شريك / توزيع أرباح (ينقص الصندوق ولا يسدد دين التمويل)',
                                'partner_project_labor' => 'أجر فعلي للشريك مقابل تنفيذ مشروع (تكلفة مشروع مباشرة)',
                                'capital_injection' => 'رأس مال تأسيسي (حقوق ملكية منفصلة عن الإيرادات والديون)',
                                'internal_transfer' => 'تحويل بين حسابات الشركة',
                                'reconciliation_adjustment' => 'تسوية محاسبية موثقة',
                            ])
                            ->required()
                            ->live()
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('amount')
                            ->label('المبلغ بالدولار (USD)')
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->minValue(0.01)
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('transaction_date')
                            ->label('تاريخ الحركة')
                            ->default(now())
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Select::make('partner_id')
                            ->label('الشريك المعني')
                            ->options(AccountingPartner::pluck('name_ar', 'id'))
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
                            ->label('العميل')
                            ->options(AccountingClient::pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->visible(fn (Get $get) => in_array($get('transaction_type'), ['client_collection', 'partner_project_labor', 'company_expense']))
                            ->columnSpan(1),

                        Forms\Components\Select::make('project_id')
                            ->label('المشروع المرتبط')
                            ->options(fn (Get $get) => $get('client_id')
                                ? AccountingProject::where('client_id', $get('client_id'))->pluck('name', 'id')
                                : AccountingProject::pluck('name', 'id'))
                            ->searchable()
                            ->columnSpan(1),

                        Forms\Components\Select::make('expense_category')
                            ->label('تصنيف المصروف / الخدمة')
                            ->options([
                                'Office' => 'مصاريف المكتب والضيافة والنظافة',
                                'Rent' => 'إيجار مقر ومكاتب الشركة',
                                'Software' => 'برمجيات واشتراكات وسيرفرات (CapCut، أدوات)',
                                'Salaries' => 'أجور تنفيذ ومستحقات الشركاء',
                                'Marketing' => 'إعلانات وعمولات تسويق',
                                'Equipment' => 'معدات وأصول ثابتة (إنفرتر، بطاريات، أثاث)',
                                'Other' => 'مصاريف تشغيلية أخرى',
                            ])
                            ->visible(fn (Get $get) => in_array($get('transaction_type'), [
                                'partner_personal_expense',
                                'company_expense',
                                'partner_project_labor',
                            ]))
                            ->columnSpan(1),

                        Forms\Components\Select::make('money_source')
                            ->label('مصدر الأموال الفعلي')
                            ->options([
                                'company_funds' => 'صندوق / حساب أموال الشركة',
                                'personal_abdullah' => 'المال الشخصي للشريك عبدالله',
                                'personal_amer' => 'المال الشخصي للشريك عامر',
                                'othman' => 'عثمان (ذمة مستقلة)',
                                'undetermined' => 'غير محسوم (يحتاج مراجعة)',
                            ])
                            ->default(fn (Get $get) => match ($get('transaction_type')) {
                                'partner_personal_expense' => 'personal_abdullah',
                                default => 'company_funds',
                            })
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Select::make('payment_method')
                            ->label('طريقة الدفع')
                            ->options([
                                'Cash' => 'نقداً (Cash)',
                                'Bank Transfer' => 'تحويل بنكي',
                                'western union' => 'ويسترن يونيون',
                                'Credit Card' => 'بطاقة ائتمانية',
                                'Wise' => 'حساب وايز',
                                'Other' => 'أخرى',
                            ])
                            ->default('Cash')
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('notes')
                            ->label('البيان وتفاصيل الحركة')
                            ->rows(3)
                            ->columnSpan(2),

                        Forms\Components\FileUpload::make('attachments')
                            ->label('المرفقات والفواتير المؤيدة')
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
                    ->label('رقم الحركة')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('transaction_type')
                    ->label('نوع الحركة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'client_collection' => 'تحصيل من عميل',
                        'partner_funding' => 'تمويل شريك للصندوق',
                        'partner_personal_expense' => 'مصروف شخصي للشريك',
                        'company_expense' => 'مصروف أموال الشركة',
                        'partner_debt_repayment' => 'سداد دين شريك',
                        'partner_distribution' => 'سحب شريك / أرباح',
                        'partner_project_labor' => 'أجر تنفيذ مشروع',
                        'capital_injection' => 'رأس مال تأسيسي',
                        'internal_transfer' => 'تحويل داخلي',
                        'reconciliation_adjustment' => 'تسوية محاسبية',
                        default => $state,
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
                    ->label('المبلغ')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold')
                    ->color(fn (AccountingTransaction $record): string => match ($record->transaction_type) {
                        'client_collection', 'partner_funding' => 'success',
                        'company_expense', 'partner_debt_repayment', 'partner_distribution' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('party_name')
                    ->label('الطرف المعني')
                    ->state(fn (AccountingTransaction $record) => $record->partner?->name_ar
                        ?: ($record->client?->name ?: ($record->payer_name ?: '-')))
                    ->searchable(),

                Tables\Columns\TextColumn::make('money_source')
                    ->label('مصدر الأموال')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'company_funds' => 'أموال الشركة',
                        'personal_abdullah' => 'جيب عبدالله',
                        'personal_amer' => 'جيب عامر',
                        'othman' => 'عثمان',
                        default => $state ?: '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'company_funds' => 'success',
                        'personal_abdullah', 'personal_amer' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('notes')
                    ->label('البيان')
                    ->limit(35)
                    ->tooltip(fn ($state) => $state),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => 'مرحل ومعتمد',
                        'reversed' => 'معكوس',
                        'pending_review' => 'قيد المراجعة',
                        default => $state,
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
                    ->label('نوع الحركة')
                    ->options([
                        'client_collection' => 'تحصيل من عميل',
                        'partner_personal_expense' => 'مصروف شخصي للشريك',
                        'company_expense' => 'مصروف أموال الشركة',
                        'partner_funding' => 'تمويل شريك للصندوق',
                        'partner_debt_repayment' => 'سداد دين شريك',
                        'partner_distribution' => 'سحب شريك / توزيع أرباح',
                    ]),

                Tables\Filters\SelectFilter::make('partner_id')
                    ->label('الشريك')
                    ->options(AccountingPartner::pluck('name_ar', 'id')),

                Tables\Filters\SelectFilter::make('client_id')
                    ->label('العميل')
                    ->options(AccountingClient::pluck('name', 'id')),

                Tables\Filters\SelectFilter::make('money_source')
                    ->label('مصدر الأموال')
                    ->options([
                        'company_funds' => 'أموال الشركة',
                        'personal_abdullah' => 'المال الشخصي لعبدالله',
                        'personal_amer' => 'المال الشخصي لعامر',
                    ]),

                Tables\Filters\Filter::make('transaction_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('من تاريخ'),
                        Forms\Components\DatePicker::make('until')->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('transaction_date', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('transaction_date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض القيد'),

                Tables\Actions\Action::make('reverse')
                    ->label('قيد عكسي')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('إجراء قيد عكسي لتصحيح الحركة')
                    ->modalDescription('سيقوم النظام بإنشاء قيد عكسي متوازن يصحح أثر هذه الحركة مع توثيق سبب العكس في سجل التدقيق.')
                    ->form([
                        Forms\Components\Textarea::make('reversal_reason')
                            ->label('سبب العكس والتصحيح')
                            ->required(),
                    ])
                    ->visible(fn (AccountingTransaction $record) => $record->status !== 'reversed' && $record->journal_entry_id)
                    ->action(function (AccountingTransaction $record, array $data) {
                        try {
                            $reversal = $record->reverse($data['reversal_reason'], Auth::id());
                            Notification::make()
                                ->title('تم عكس الحركة بنجاح')
                                ->body("تم توليد القيد العكسي رقم {$reversal->entry_number}")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('خطأ في عكس الحركة')
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
