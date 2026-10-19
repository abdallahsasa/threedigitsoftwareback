<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountingPaymentResource\Pages;
use App\Models\AccountingAccount;
use App\Models\AccountingClient;
use App\Models\AccountingInvoice;
use App\Models\AccountingPayment;
use App\Services\DocumentSharingService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AccountingPaymentResource extends Resource
{
    protected static ?string $model = AccountingPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Accounting & Finance';

    protected static ?string $navigationLabel = 'Payments & Receipts';

    protected static ?string $modelLabel = 'Payment / Receipt';

    protected static ?string $pluralModelLabel = 'Payments & Receipts';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Payment Information / بيانات سند القبض والدفع')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('payment_number')
                                ->label('Payment Number / رقم السند')
                                ->default(fn() => AccountingPayment::generatePaymentNumber())
                                ->disabled()
                                ->dehydrated()
                                ->required(),

                            Select::make('client_id')
                                ->label('Client / العميل')
                                ->relationship('client', 'name')
                                ->getOptionLabelFromRecordUsing(fn($record) => $record->display_name)
                                ->searchable()
                                ->preload()
                                ->reactive()
                                ->required(),

                            DatePicker::make('payment_date')
                                ->label('Payment Date / تاريخ القبض')
                                ->default(now())
                                ->required(),

                            TextInput::make('amount')
                                ->label('Total Amount / المبلغ المقبوض')
                                ->numeric()
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
                                    'bank_transfer' => 'Bank Transfer / تحويل مصرفي',
                                    'crypto' => 'Crypto / USDT',
                                    'other' => 'Other / أخرى',
                                ])
                                ->default('cash')
                                ->required(),

                            TextInput::make('handled_by')
                                ->label('Handled By / الشخص المستلم')
                                ->default(fn() => auth()->user()?->name),

                            TextInput::make('reference')
                                ->label('Reference / رقم الإيصال أو الحوالة'),

                            Select::make('type')
                                ->label('Payment Nature / طبيعة الدفعة')
                                ->options([
                                    'invoice_payment' => 'Invoice Payment / سداد فاتورة أو فواتير محددة',
                                    'customer_advance' => 'Customer Advance / دفعة مقدمة غير مخصصة (إيراد مؤجل)',
                                    'refund' => 'Refund / استرداد للعميل',
                                ])
                                ->default('invoice_payment')
                                ->required(),

                            Textarea::make('notes')
                                ->label('Notes / ملاحظات وبيان الحركة')
                                ->rows(2)
                                ->columnSpanFull(),

                            FileUpload::make('attachment_path')
                                ->label('Payment Receipt Attachment / إشعار أو وصل التحويل')
                                ->directory('attachments/payments')
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Invoice Allocations / تخصيص السداد على الفواتير المستحقة')
                    ->description('يمكن تخصيص المبلغ على فاتورة واحدة أو عدة فواتير جزئياً. المبلغ غير المخصص يُحفظ كدفعة مقدمة / رصيد دائن للعميل.')
                    ->schema([
                        Repeater::make('allocations')
                            ->relationship()
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('invoice_id')
                                        ->label('Unpaid Invoice / الفاتورة المستحقة')
                                        ->options(function (callable $get) {
                                            $clientId = $get('../../client_id');
                                            if (!$clientId) {
                                                return [];
                                            }
                                            return AccountingInvoice::where('client_id', $clientId)
                                                ->where('document_status', 'issued')
                                                ->where('payment_status', '!=', 'paid')
                                                ->get()
                                                ->mapWithKeys(fn($inv) => [
                                                    $inv->id => "{$inv->invoice_number} (المتبقي: \${$inv->balance_due})",
                                                ]);
                                        })
                                        ->searchable()
                                        ->required(),

                                    TextInput::make('amount')
                                        ->label('Allocated Amount / المبلغ المخصص للفاتورة')
                                        ->numeric()
                                        ->prefix('$')
                                        ->required(),
                                ]),
                            ])
                            ->defaultItems(0)
                            ->columns(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_number')
                    ->label('رقم السند')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('receipt.receipt_number')
                    ->label('رقم إيصال القبض')
                    ->badge()
                    ->color('success'),

                TextColumn::make('client.name')
                    ->label('العميل')
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('USD')
                    ->weight('bold')
                    ->color('success')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->badge(),

                TextColumn::make('receivingAccount.name_ar')
                    ->label('الحساب المستلم'),

                TextColumn::make('payment_date')
                    ->label('التاريخ')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options([
                        'cash' => 'Cash',
                        'bank_transfer' => 'Bank Transfer',
                        'crypto' => 'Crypto',
                    ]),
            ])
            ->actions([
                // 1. Official PDF Receipt
                Action::make('pdf_receipt')
                    ->label('إيصال PDF')
                    ->icon('heroicon-o-document-check')
                    ->color('success')
                    ->visible(fn(AccountingPayment $record) => $record->receipt !== null)
                    ->url(fn(AccountingPayment $record) => route('admin.pdf.receipt', $record->receipt->id))
                    ->openUrlInNewTab(),

                // 2. WhatsApp Receipt Share
                Action::make('whatsapp_receipt')
                    ->label('واتساب')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('info')
                    ->visible(fn(AccountingPayment $record) => $record->receipt !== null)
                    ->action(function (AccountingPayment $record, DocumentSharingService $shareService) {
                        $receipt = $record->receipt;
                        $share = $shareService->getOrCreateShare('receipt', $receipt->id, $record->client_id, 'whatsapp');
                        $url = $shareService->getWhatsAppUrl($share);
                        return redirect()->away($url);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountingPayments::route('/'),
            'create' => Pages\CreateAccountingPayment::route('/create'),
            'edit' => Pages\EditAccountingPayment::route('/{record}/edit'),
        ];
    }
}
