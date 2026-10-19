<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountingProjectResource\Pages;
use App\Models\AccountingClient;
use App\Models\AccountingProject;
use App\Models\AccountingService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->canViewAccounting() || $user->canManageClientsAndProjects());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Project Overview / نظرة عامة على المشروع')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('name')
                                ->label('Project Title / اسم المشروع')
                                ->required(),

                            Select::make('client_id')
                                ->label('Client / العميل')
                                ->relationship('client', 'name')
                                ->getOptionLabelFromRecordUsing(fn($record) => $record->display_name)
                                ->searchable()
                                ->preload()
                                ->required(),

                            TextInput::make('business_sector')
                                ->label('Business Sector / قطاع الأعمال')
                                ->placeholder('مثال: تجارة سيارات، عيادات، تعليم'),

                            Select::make('service_categories')
                                ->label('Service Categories / تصنيفات الخدمات المشولة')
                                ->multiple()
                                ->options(AccountingService::CATEGORIES),

                            Select::make('status')
                                ->label('Project Status / حالة المشروع')
                                ->options([
                                    'in_progress' => 'In Progress / قيد التنفيذ',
                                    'done' => 'Completed / منجز',
                                    'cancelled' => 'Cancelled / ملغى',
                                ])
                                ->default('in_progress')
                                ->required(),

                            TextInput::make('currency')
                                ->label('Currency / العملة')
                                ->default('USD')
                                ->required(),

                            DatePicker::make('start_date')
                                ->label('Start Date / تاريخ البدء'),

                            DatePicker::make('delivery_date')
                                ->label('Target Delivery Date / موعد التسليم المتوقع'),

                            TextInput::make('time_spent_hours')
                                ->label('Time Spent (Hours) / ساعات العمل المنفذة')
                                ->numeric(),
                        ]),
                    ]),

                Section::make('Contract & Financial Controls / الضوابط المالية للمشروع')
                    ->description('تنبيه محاسبي: قيمة العقد لا تُعد نقداً مقبوضاً، والمشاريع الملغاة لا تُعد ذمماً مدينة تلقائياً.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('total_price')
                                ->label('Agreed Contract Value / قيمة العقد الإجمالية المتفق عليها')
                                ->numeric()
                                ->prefix('$')
                                ->required()
                                ->helperText('القيمة التعاقدية الكاملة؛ لا تحتسب كإيراد فعلي إلا عند إصدار الفاتورة أو التحصيل.'),

                            TextInput::make('direct_costs')
                                ->label('Direct Project Costs & Freelancers / التكاليف المباشرة وأجور التنفيذ')
                                ->numeric()
                                ->prefix('$')
                                ->default(0.0000)
                                ->helperText('تكاليف السيرفرات والتنفيذ الخارجي المباشر المرتبطة بالمشروع.'),
                        ]),
                    ]),

                Section::make('Scope, Deliverables & Attachments / النطاق والتسليمات')
                    ->schema([
                        Grid::make(2)->schema([
                            Textarea::make('description')
                                ->label('Description / وصف المشروع')
                                ->rows(2),

                            Textarea::make('scope')
                                ->label('Scope of Work / نطاق العمل التفصيلي')
                                ->rows(2),

                            Textarea::make('deliverables')
                                ->label('Deliverables / مخرجات المشروع المسلمة')
                                ->rows(2),

                            Textarea::make('notes')
                                ->label('Internal Project Notes / ملاحظات إدارية')
                                ->rows(2),

                            FileUpload::make('attachments')
                                ->label('Project Documents & Attachments / وثائق وملفات المشروع')
                                ->multiple()
                                ->directory('attachments/projects')
                                ->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('المشروع')
                    ->weight('bold')
                    ->searchable()
                    ->description(fn(AccountingProject $record) => $record->business_sector),

                TextColumn::make('client.name')
                    ->label('العميل')
                    ->searchable(),

                TextColumn::make('total_price')
                    ->label('قيمة العقد')
                    ->money('USD')
                    ->weight('bold'),

                TextColumn::make('total_invoiced')
                    ->label('المفوتر')
                    ->money('USD')
                    ->color('info'),

                TextColumn::make('total_collected')
                    ->label('المحصّل نقداً')
                    ->money('USD')
                    ->color('success')
                    ->weight('bold'),

                TextColumn::make('direct_costs')
                    ->label('التكاليف المباشرة')
                    ->money('USD')
                    ->color('danger'),

                TextColumn::make('project_margin')
                    ->label('هامش الربح')
                    ->money('USD')
                    ->color(fn(AccountingProject $record) => $record->project_margin >= 0 ? 'success' : 'danger')
                    ->weight('bold'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'done' => 'منجز',
                        'cancelled' => 'ملغى',
                        default => 'قيد التنفيذ',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'done' => 'success',
                        'cancelled' => 'danger',
                        default => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'in_progress' => 'قيد التنفيذ',
                        'done' => 'منجز',
                        'cancelled' => 'ملغى',
                    ]),
            ])
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountingProjects::route('/'),
            'create' => Pages\CreateAccountingProject::route('/create'),
            'edit' => Pages\EditAccountingProject::route('/{record}/edit'),
        ];
    }
}
