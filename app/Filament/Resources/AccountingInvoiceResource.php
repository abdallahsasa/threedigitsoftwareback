<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountingInvoiceResource\Pages;
use App\Models\AccountingAccount;
use App\Models\AccountingClient;
use App\Models\AccountingCreditNote;
use App\Models\AccountingInvoice;
use App\Models\AccountingPayment;
use App\Models\AccountingProject;
use App\Models\AccountingService;
use App\Services\DocumentSharingService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class AccountingInvoiceResource extends Resource
{
    protected static ?string $model = AccountingInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Accounting & Finance';

    protected static ?string $navigationLabel = 'Invoices';

    protected static ?string $modelLabel = 'Invoice';

    protected static ?string $pluralModelLabel = 'Invoices';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Invoice Header / بيانات الفاتورة')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('invoice_number')
                                ->label('Invoice Number / رقم الفاتورة')
                                ->default(fn() => AccountingInvoice::generateInvoiceNumber())
                                ->disabled(fn($record) => $record && $record->document_status === 'issued')
                                ->dehydrated()
                                ->required(),

                            Select::make('client_id')
                                ->label('Client / العميل')
                                ->relationship('client', 'name')
                                ->getOptionLabelFromRecordUsing(fn($record) => $record->display_name)
                                ->searchable()
                                ->preload()
                                ->disabled(fn($record) => $record && $record->document_status === 'issued')
                                ->required(),

                            Select::make('project_id')
                                ->label('Related Project / المشروع المرتبط (اختياري)')
                                ->relationship('project', 'name')
                                ->searchable()
                                ->preload()
                                ->disabled(fn($record) => $record && $record->document_status === 'issued'),

                            TextInput::make('title')
                                ->label('Invoice Title / عنوان الفاتورة')
                                ->placeholder('مثال: الدفعة الأولى لتطوير المنصة')
                                ->columnSpan(2),

                            TextInput::make('reference')
                                ->label('Reference / PO / المرجع أو أمر الشراء'),

                            DatePicker::make('issue_date')
                                ->label('Issue Date / تاريخ التحرير')
                                ->default(now())
                                ->disabled(fn($record) => $record && $record->document_status === 'issued')
                                ->required(),

                            DatePicker::make('due_date')
                                ->label('Due Date / تاريخ الاستحقاق')
                                ->default(now()->addDays(14))
                                ->required(),

                            Select::make('document_language')
                                ->label('Document Language / لغة الوثيقة')
                                ->options([
                                    'ar' => 'العربية (Arabic)',
                                    'en' => 'English (الإنجليزية)',
                                ])
                                ->default('ar')
                                ->required(),

                            Toggle::make('is_advance')
                                ->label('Customer Advance Invoice / فاتورة دفعة مقدمة (إيراد مؤجل)')
                                ->helperText('عند التفعيل، يُقيد الإيراد في حساب إيرادات مؤجلة (2300) حتى تقديم الخدمة.')
                                ->disabled(fn($record) => $record && $record->document_status === 'issued')
                                ->default(false),
                        ]),
                    ]),

                Section::make('Invoice Line Items / بنود الفاتورة')
                    ->description('تُقفل البنود وتُحفظ كنسخة مجمدة غير قابلة للتعديل عند إصدار الفاتورة.')
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
                                        ->disabled(fn(callable $get, $record) => $record && $record->document_status === 'issued')
                                        ->columnSpan(2),

                                    TextInput::make('service_name')
                                        ->label('Item Name / مسمى البند')
                                        ->required()
                                        ->disabled(fn(callable $get, $record) => $record && $record->document_status === 'issued')
                                        ->columnSpan(2),

                                    TextInput::make('quantity')
                                        ->label('Quantity / الكمية')
                                        ->numeric()
                                        ->default(1)
                                        ->disabled(fn(callable $get, $record) => $record && $record->document_status === 'issued')
                                        ->required(),

                                    TextInput::make('unit')
                                        ->label('Unit / الوحدة')
                                        ->default('خدمة')
                                        ->disabled(fn(callable $get, $record) => $record && $record->document_status === 'issued')
                                        ->required(),

                                    TextInput::make('unit_price')
                                        ->label('Unit Price / سعر الوحدة')
                                        ->numeric()
                                        ->prefix('$')
                                        ->disabled(fn(callable $get, $record) => $record && $record->document_status === 'issued')
                                        ->required()
                                        ->columnSpan(2),

                                    Select::make('discount_type')
                                        ->label('Discount Type / نوع الخصم')
                                        ->options([
                                            'none' => 'None / بدون',
                                            'percentage' => 'Percentage (%)',
                                            'fixed' => 'Fixed ($)',
                                        ])
                                        ->disabled(fn(callable $get, $record) => $record && $record->document_status === 'issued')
                                        ->default('none'),

                                    TextInput::make('discount_value')
                                        ->label('Discount Value / قيمة الخصم')
                                        ->numeric()
                                        ->disabled(fn(callable $get, $record) => $record && $record->document_status === 'issued')
                                        ->default(0.00),

                                    TextInput::make('tax_rate')
                                        ->label('Tax Rate (%) / نسبة الضريبة')
                                        ->numeric()
                                        ->default(0.00)
                                        ->disabled(fn(callable $get, $record) => $record && $record->document_status === 'issued')
                                        ->suffix('%'),

                                    Textarea::make('description')
                                        ->label('Detailed Work Description / تفاصيل العمل المنجز والمفوتر')
                                        ->rows(2)
                                        ->disabled(fn(callable $get, $record) => $record && $record->document_status === 'issued')
                                        ->columnSpanFull(),
                                ]),
                            ])
                            ->defaultItems(1)
                            ->columns(1),
                    ]),

                Section::make('Terms, Notes & Instructions / التعليمات والملاحظات')
                    ->schema([
                        Grid::make(2)->schema([
                            Textarea::make('payment_terms')
                                ->label('Payment Terms / شروط السداد')
                                ->rows(2),

                            Textarea::make('payment_instructions')
                                ->label('Payment Instructions / تعليمات التحويل والحسابات')
                                ->rows(2),

                            Textarea::make('customer_notes')
                                ->label('Customer Facing Notes / ملاحظات تظهر للعميل في الفاتورة')
                                ->rows(2),

                            Textarea::make('private_notes')
                                ->label('Private Internal Notes / ملاحظات داخلية سرية (لا تظهر بالفاتورة أو الرابط)')
                                ->rows(2),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('رقم الفاتورة')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('client.name')
                    ->label('العميل')
                    ->searchable(),

                TextColumn::make('title')
                    ->label('العنوان')
                    ->limit(25),

                TextColumn::make('total_amount')
                    ->label('الإجمالي')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('المسدد')
                    ->money('USD')
                    ->color('success'),

                TextColumn::make('balance_due')
                    ->label('المتبقي')
                    ->money('USD')
                    ->weight('bold')
                    ->color(fn(AccountingInvoice $record) => (float)$record->balance_due > 0 ? 'danger' : 'success'),

                TextColumn::make('document_status')
                    ->label('حالة الوثيقة')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'issued' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('payment_status')
                    ->label('حالة الدفع')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'unpaid' => 'danger',
                        'partially_paid' => 'warning',
                        'paid' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('due_date')
                    ->label('الاستحقاق')
                    ->date()
                    ->description(fn(AccountingInvoice $record) => $record->is_overdue ? 'متأخرة عن موعدها' : null)
                    ->color(fn(AccountingInvoice $record) => $record->is_overdue ? 'danger' : null),
            ])
            ->filters([
                SelectFilter::make('document_status')
                    ->label('حالة الوثيقة')
                    ->options([
                        'draft' => 'مسودة / Draft',
                        'issued' => 'صادرة / Issued',
                        'cancelled' => 'ملغاة / Cancelled',
                    ]),

                SelectFilter::make('payment_status')
                    ->label('حالة الدفع')
                    ->options([
                        'unpaid' => 'غير مدفوعة / Unpaid',
                        'partially_paid' => 'مدفوعة جزئياً / Partially Paid',
                        'paid' => 'مدفوعة بالكامل / Paid',
                    ]),
            ])
            ->actions([
                // 1. Issue Invoice Action
                Action::make('issue')
                    ->label('إصدار الفاتورة')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn(AccountingInvoice $record) => $record->document_status === 'draft')
                    ->requiresConfirmation()
                    ->modalHeading('اعتماد وإصدار الفاتورة وترحيل القيد المحاسبي')
                    ->modalDescription('عند الإصدار: سيتم تجميد بيانات وهوية الشركة وبيانات العميل والبنود، وترحيل القيد المحاسبي المزدوج. يرجى التأكد من إدخال الاسم القانوني والرقم الضريبي في إعدادات الشركة.')
                    ->action(function (AccountingInvoice $record) {
                        try {
                            $record->issueInvoice(auth()->user());
                            Notification::make()
                                ->title("تم إصدار الفاتورة {$record->invoice_number} وترحيل القيد المحاسبي بنجاح!")
                                ->success()
                                ->send();
                        } catch (ValidationException $e) {
                            Notification::make()
                                ->title('تعذر إصدار الفاتورة')
                                ->body(collect($e->errors())->flatten()->first())
                                ->danger()
                                ->send();
                        }
                    }),

                // 2. PDF Download / Preview
                Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn(AccountingInvoice $record) => route('admin.pdf.invoice', $record->id))
                    ->openUrlInNewTab(),

                // 3. WhatsApp Share Link
                Action::make('whatsapp')
                    ->label('واتساب')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->action(function (AccountingInvoice $record, DocumentSharingService $shareService) {
                        $share = $shareService->getOrCreateShare('invoice', $record->id, $record->client_id, 'whatsapp');
                        $url = $shareService->getWhatsAppUrl($share);
                        return redirect()->away($url);
                    }),

                // 4. Send Email Modal
                Action::make('email')
                    ->label('إرسال بالبريد')
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->form(function (AccountingInvoice $record, DocumentSharingService $shareService) {
                        $share = $shareService->getOrCreateShare('invoice', $record->id, $record->client_id, 'email');
                        $preview = $shareService->previewEmail($share);
                        return [
                            TextInput::make('recipient_email')
                                ->label('Recipient Email / البريد الإلكتروني للمستلم')
                                ->default($preview['recipient_email'])
                                ->required(),

                            TextInput::make('subject')
                                ->label('Subject / موضوع الرسالة')
                                ->default($preview['subject'])
                                ->required(),

                            Textarea::make('body')
                                ->label('Message Body / نص الرسالة')
                                ->default($preview['body'])
                                ->rows(5)
                                ->required(),

                            TextInput::make('attachment_name')
                                ->label('Attached PDF / الملف المرفق')
                                ->default($preview['attachment_name'])
                                ->disabled(),
                        ];
                    })
                    ->action(function (AccountingInvoice $record, array $data, DocumentSharingService $shareService) {
                        $share = $shareService->getOrCreateShare('invoice', $record->id, $record->client_id, 'email');
                        try {
                            $shareService->sendEmail($share, $data);
                            Notification::make()
                                ->title('تم إرسال الفاتورة بالبريد الإلكتروني بنجاح')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('فشل إرسال البريد')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // 5. Quick Payment Action
                Action::make('record_payment')
                    ->label('تسجيل دفعة')
                    ->icon('heroicon-o-banknotes')
                    ->color('warning')
                    ->visible(fn(AccountingInvoice $record) => $record->document_status === 'issued' && (float)$record->balance_due > 0)
                    ->form(fn(AccountingInvoice $record) => [
                        DatePicker::make('payment_date')
                            ->label('Payment Date / تاريخ الدفعة')
                            ->default(now())
                            ->required(),

                        TextInput::make('amount')
                            ->label('Payment Amount / المبلغ')
                            ->numeric()
                            ->default($record->balance_due)
                            ->prefix('$')
                            ->required(),

                        Select::make('receiving_account_id')
                            ->label('Receiving Account / الحساب المستلم')
                            ->options(AccountingAccount::whereIn('sub_type', ['cash', 'bank'])->pluck('name_ar', 'id'))
                            ->required(),

                        Select::make('payment_method')
                            ->label('Payment Method / طريقة الدفع')
                            ->options([
                                'cash' => 'Cash / نقداً في الصندوق',
                                'bank_transfer' => 'Bank Transfer / تحويل بنكي',
                                'crypto' => 'Crypto / USDT',
                                'other' => 'Other / أخرى',
                            ])
                            ->default('cash')
                            ->required(),

                        TextInput::make('handled_by')
                            ->label('Handled By / الشخص المستلم')
                            ->default(auth()->user()?->name),

                        TextInput::make('reference')
                            ->label('Reference / رقم الإيصال أو الحوالة'),

                        Textarea::make('notes')
                            ->label('Notes / ملاحظات')
                            ->rows(2),
                    ])
                    ->action(function (AccountingInvoice $record, array $data) {
                        $payment = AccountingPayment::create([
                            'payment_number' => AccountingPayment::generatePaymentNumber(),
                            'client_id' => $record->client_id,
                            'payment_date' => $data['payment_date'],
                            'amount' => $data['amount'],
                            'receiving_account_id' => $data['receiving_account_id'],
                            'payment_method' => $data['payment_method'],
                            'handled_by' => $data['handled_by'] ?? null,
                            'reference' => $data['reference'] ?? null,
                            'notes' => $data['notes'] ?? "سداد للفاتورة {$record->invoice_number}",
                            'type' => 'invoice_payment',
                            'created_by' => auth()->id(),
                        ]);

                        $receipt = $payment->postPayment([
                            ['invoice_id' => $record->id, 'amount' => $data['amount']],
                        ]);

                        Notification::make()
                            ->title("تم تسجيل وسداد الدفعة بنجاح! رقم الإيصال: {$receipt->receipt_number}")
                            ->success()
                            ->send();
                    }),

                // 6. Credit Note Action
                Action::make('credit_note')
                    ->label('إشعار دائن')
                    ->icon('heroicon-o-receipt-percent')
                    ->color('danger')
                    ->visible(fn(AccountingInvoice $record) => $record->document_status === 'issued')
                    ->form(fn(AccountingInvoice $record) => [
                        DatePicker::make('issue_date')
                            ->label('Issue Date / تاريخ الإشعار الدائن')
                            ->default(now())
                            ->required(),

                        TextInput::make('amount')
                            ->label('Credit Amount / مبلغ التخفيض الدائن')
                            ->numeric()
                            ->default($record->balance_due > 0 ? $record->balance_due : 0)
                            ->prefix('$')
                            ->required(),

                        Textarea::make('reason')
                            ->label('Reason for Credit Note / سبب التخفيض أو التسوية')
                            ->required()
                            ->rows(2),
                    ])
                    ->action(function (AccountingInvoice $record, array $data) {
                        $cn = AccountingCreditNote::create([
                            'credit_note_number' => AccountingCreditNote::generateCreditNoteNumber(),
                            'invoice_id' => $record->id,
                            'client_id' => $record->client_id,
                            'issue_date' => $data['issue_date'],
                            'amount' => $data['amount'],
                            'reason' => $data['reason'],
                            'status' => 'draft',
                            'created_by' => auth()->id(),
                        ]);

                        $cn->issueCreditNote();

                        Notification::make()
                            ->title("تم اعتماد الإشعار الدائن {$cn->credit_note_number} وتخفيض رصيد الفاتورة بنجاح!")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountingInvoices::route('/'),
            'create' => Pages\CreateAccountingInvoice::route('/create'),
            'edit' => Pages\EditAccountingInvoice::route('/{record}/edit'),
        ];
    }
}
