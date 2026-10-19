<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountingQuotationResource\Pages;
use App\Models\AccountingClient;
use App\Models\AccountingProject;
use App\Models\AccountingQuotation;
use App\Models\AccountingService;
use App\Services\DocumentSharingService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AccountingQuotationResource extends Resource
{
    protected static ?string $model = AccountingQuotation::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationGroup = 'Accounting & Finance';

    protected static ?string $navigationLabel = 'Quotations';

    protected static ?string $modelLabel = 'Quotation';

    protected static ?string $pluralModelLabel = 'Quotations';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('General Information / المعلومات الأساسية')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('quotation_number')
                                ->label('Quotation Number / رقم عرض السعر')
                                ->default(fn() => AccountingQuotation::generateQuotationNumber())
                                ->disabled()
                                ->dehydrated()
                                ->required(),

                            Select::make('client_id')
                                ->label('Client / العميل')
                                ->relationship('client', 'name')
                                ->getOptionLabelFromRecordUsing(fn($record) => $record->display_name)
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('project_id')
                                ->label('Related Project / المشروع المرتبط (اختياري)')
                                ->relationship('project', 'name')
                                ->searchable()
                                ->preload(),

                            TextInput::make('title')
                                ->label('Quotation Subject / موضوع أو عنوان العرض')
                                ->required()
                                ->columnSpan(2),

                            Select::make('status')
                                ->label('Status / الحالة')
                                ->options([
                                    'draft' => 'Draft / مسودة',
                                    'sent' => 'Sent / مرسل للعميل',
                                    'accepted' => 'Accepted / مقبول',
                                    'rejected' => 'Rejected / مرفوض',
                                    'expired' => 'Expired / منتهي الصلاحية',
                                ])
                                ->default('draft')
                                ->required(),

                            DatePicker::make('issue_date')
                                ->label('Issue Date / تاريخ الإصدار')
                                ->default(now())
                                ->required(),

                            DatePicker::make('valid_until')
                                ->label('Valid Until / صالح حتى تاريخ')
                                ->default(now()->addDays(30))
                                ->required(),

                            Select::make('document_language')
                                ->label('Document Language / لغة الوثيقة')
                                ->options([
                                    'ar' => 'العربية (Arabic)',
                                    'en' => 'English (الإنجليزية)',
                                ])
                                ->default('ar')
                                ->required(),
                        ]),
                    ]),

                Section::make('Quotation Items / بنود عرض السعر')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Grid::make(6)->schema([
                                    Select::make('service_id')
                                        ->label('Service / الخدمة من الدليل')
                                        ->options(AccountingService::where('is_active', true)->pluck('name_ar', 'id'))
                                        ->searchable()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            if ($srv = AccountingService::find($state)) {
                                                $set('service_name', $srv->name_ar);
                                                $set('unit_price', $srv->default_price);
                                                $set('unit', $srv->unit);
                                            }
                                        })
                                        ->columnSpan(2),

                                    TextInput::make('service_name')
                                        ->label('Service Name / مسمى البند')
                                        ->required()
                                        ->columnSpan(2),

                                    TextInput::make('quantity')
                                        ->label('Quantity / الكمية')
                                        ->numeric()
                                        ->default(1)
                                        ->required(),

                                    TextInput::make('unit')
                                        ->label('Unit / الوحدة')
                                        ->default('خدمة')
                                        ->required(),

                                    TextInput::make('unit_price')
                                        ->label('Unit Price / سعر الوحدة')
                                        ->numeric()
                                        ->prefix('$')
                                        ->required()
                                        ->columnSpan(2),

                                    Select::make('discount_type')
                                        ->label('Discount Type / نوع الخصم')
                                        ->options([
                                            'none' => 'None / بدون',
                                            'percentage' => 'Percentage (%)',
                                            'fixed' => 'Fixed ($)',
                                        ])
                                        ->default('none'),

                                    TextInput::make('discount_value')
                                        ->label('Discount Value / قيمة الخصم')
                                        ->numeric()
                                        ->default(0.00),

                                    TextInput::make('tax_rate')
                                        ->label('Tax Rate (%) / نسبة الضريبة')
                                        ->numeric()
                                        ->default(0.00)
                                        ->suffix('%'),

                                    Textarea::make('description')
                                        ->label('Detailed Work Description / تفاصيل ومواصفات البند')
                                        ->rows(2)
                                        ->columnSpanFull(),
                                ]),
                            ])
                            ->defaultItems(1)
                            ->columns(1),
                    ]),

                Section::make('Scope, Deliverables & Terms / نطاق العمل والشروط')
                    ->schema([
                        Grid::make(2)->schema([
                            Textarea::make('scope')
                                ->label('Project Scope / نطاق العمل التفصيلي')
                                ->rows(3),

                            Textarea::make('deliverables')
                                ->label('Deliverables / المخرجات والتسليمات')
                                ->rows(3),

                            Textarea::make('payment_schedule')
                                ->label('Payment Schedule / جدول الدفعات المقترح')
                                ->rows(2),

                            Textarea::make('terms_and_conditions')
                                ->label('Terms & Conditions / الشروط والأحكام')
                                ->rows(2),

                            Textarea::make('customer_notes')
                                ->label('Customer Facing Notes / ملاحظات تظهر للعميل')
                                ->rows(2),

                            Textarea::make('private_notes')
                                ->label('Private Internal Notes / ملاحظات داخلية سرية (لا تظهر للعميل مطلقاً)')
                                ->rows(2),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quotation_number')
                    ->label('رقم العرض')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('client.name')
                    ->label('العميل')
                    ->searchable(),

                TextColumn::make('title')
                    ->label('موضوع العرض')
                    ->limit(30),

                TextColumn::make('total_amount')
                    ->label('إجمالي القيمة')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        'expired' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('issue_date')
                    ->label('تاريخ الإصدار')
                    ->date(),

                TextColumn::make('valid_until')
                    ->label('صالح حتى')
                    ->date(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Sent',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                        'expired' => 'Expired',
                    ]),
            ])
            ->actions([
                // 1. PDF Preview / Download
                Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn(AccountingQuotation $record) => route('admin.pdf.quotation', $record->id))
                    ->openUrlInNewTab(),

                // 2. Share via WhatsApp
                Action::make('whatsapp')
                    ->label('واتساب')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->action(function (AccountingQuotation $record, DocumentSharingService $shareService) {
                        $share = $shareService->getOrCreateShare('quotation', $record->id, $record->client_id, 'whatsapp');
                        $url = $shareService->getWhatsAppUrl($share);
                        return redirect()->away($url);
                    }),

                // 3. Convert to Project
                Action::make('convert_to_project')
                    ->label('تحويل لمشروع')
                    ->icon('heroicon-o-briefcase')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('تحويل عرض السعر إلى مشروع جديد')
                    ->modalDescription('سيتم إنشاء مشروع رسمي ونقل القيمة والنطاق والمخرجات تلقائياً دون إعادة إدخالها.')
                    ->visible(fn(AccountingQuotation $record) => empty($record->converted_to_project_id))
                    ->action(function (AccountingQuotation $record) {
                        $project = $record->convertToProject();
                        Notification::make()
                            ->title("تم إنشاء المشروع {$project->name} بنجاح!")
                            ->success()
                            ->send();
                    }),

                // 4. Convert to Invoice
                Action::make('convert_to_invoice')
                    ->label('تحويل لفاتورة')
                    ->icon('heroicon-o-document-text')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('تحويل عرض السعر إلى مسودة فاتورة')
                    ->modalDescription('سيتم إنشاء مسودة فاتورة بكافة البند والأسعار والخصومات الجاهزة للمراجعة والإصدار.')
                    ->visible(fn(AccountingQuotation $record) => empty($record->converted_to_invoice_id))
                    ->action(function (AccountingQuotation $record) {
                        $invoice = $record->convertToInvoice();
                        Notification::make()
                            ->title("تم إنشاء مسودة الفاتورة {$invoice->invoice_number} بنجاح!")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountingQuotations::route('/'),
            'create' => Pages\CreateAccountingQuotation::route('/create'),
            'edit' => Pages\EditAccountingQuotation::route('/{record}/edit'),
        ];
    }
}
