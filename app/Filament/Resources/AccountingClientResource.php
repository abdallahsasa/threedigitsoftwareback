<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountingClientResource\Pages;
use App\Models\AccountingClient;
use App\Services\DocumentSharingService;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AccountingClientResource extends Resource
{
    protected static ?string $model = AccountingClient::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Accounting & Finance';

    protected static ?string $navigationLabel = 'Clients & CRM';

    protected static ?string $modelLabel = 'Client';

    protected static ?string $pluralModelLabel = 'Clients & CRM';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->canViewAccounting() || $user->canManageClientsAndProjects());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Client Identity & Classification / هوية وتصنيف العميل')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('type')
                                ->label('Client Type / نوع العميل')
                                ->options([
                                    'company' => 'Company / شركة أو مؤسسة',
                                    'individual' => 'Individual / فرد أو مستقل',
                                ])
                                ->default('company')
                                ->required(),

                            TextInput::make('name')
                                ->label('Full Name / الاسم الكامل أو التجاري')
                                ->required(),

                            TextInput::make('legal_name')
                                ->label('Full Legal Company Name / الاسم القانوني المسجل للشركة')
                                ->placeholder('يستخدم في الفواتير الرسمية للشركات'),

                            TextInput::make('brand_name')
                                ->label('Brand Name / الاسم التجاري أو العلامة'),

                            TextInput::make('business_sector')
                                ->label('Business Sector / قطاع الأعمال')
                                ->placeholder('مثال: تكنولوجيا، تجارة، عقارات، سيارات'),

                            TextInput::make('contact_person')
                                ->label('Contact Person / الشخص المسؤول'),

                            TextInput::make('tax_number')
                                ->label('Client Tax Number / الرقم الضريبي للعميل (اختياري)')
                                ->placeholder('يظهر بالفاتورة إن وُجد'),

                            TextInput::make('registration_number')
                                ->label('Commercial Registration / السجل التجاري'),
                        ]),
                    ]),

                Section::make('Contact & Billing Address / بيانات الاتصال وعنوان الفوترة')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('phone')
                                ->label('Phone / الهاتف')
                                ->tel(),

                            TextInput::make('whatsapp')
                                ->label('WhatsApp / واتساب المراسلات')
                                ->tel(),

                            TextInput::make('email')
                                ->label('Email / البريد الإلكتروني')
                                ->email(),

                            TextInput::make('city')
                                ->label('City / المدينة'),

                            TextInput::make('country')
                                ->label('Country / الدولة'),

                            TextInput::make('postal_code')
                                ->label('Postal Code / الرمز البريدي'),

                            TextInput::make('billing_address')
                                ->label('Billing Address / عنوان الفوترة التفصيلي')
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Notes & Privacy Separation / الملاحظات وتصنيف الخصوصية')
                    ->description('تنبيه أمني صارم: الملاحظات الداخلية الخاصة لن تظهر مطلقاً في كشوف الحساب أو فواتير العميل أو الروابط العامة.')
                    ->schema([
                        Grid::make(2)->schema([
                            Textarea::make('public_notes')
                                ->label('Customer Facing Notes / ملاحظات تظهر للعميل')
                                ->rows(3),

                            Textarea::make('internal_notes')
                                ->label('Strictly Private Internal Notes / ملاحظات داخلية سرية خاصة بالإدارة')
                                ->helperText('محمية تماماً وخاصة بفريق الشركة الداخلي فقط.')
                                ->rows(3),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم العميل / الشركة')
                    ->weight('bold')
                    ->searchable()
                    ->description(fn(AccountingClient $record) => $record->legal_name ?: $record->business_sector),

                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'company' => 'شركة',
                        'individual' => 'فرد',
                        default => $state,
                    })
                    ->color(fn(string $state): string => $state === 'company' ? 'info' : 'gray'),

                TextColumn::make('phone')
                    ->label('الاتصال')
                    ->description(fn(AccountingClient $record) => $record->whatsapp ? "واتساب: {$record->whatsapp}" : null),

                TextColumn::make('confirmed_outstanding_balance')
                    ->label('الرصيد المؤكد المتبقي')
                    ->money('USD')
                    ->weight('bold')
                    ->color(fn(AccountingClient $record) => $record->confirmed_outstanding_balance > 0 ? 'danger' : 'success')
                    ->sortable(),

                TextColumn::make('invoices_count')
                    ->label('الفواتير')
                    ->counts('invoices')
                    ->badge(),

                TextColumn::make('projects_count')
                    ->label('المشاريع')
                    ->counts('projects')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('نوع العميل')
                    ->options([
                        'company' => 'شركة',
                        'individual' => 'فرد',
                    ]),
            ])
            ->actions([
                // 1. Account Statement PDF
                Action::make('statement_pdf')
                    ->label('كشف حساب PDF')
                    ->icon('heroicon-o-document-chart-bar')
                    ->color('info')
                    ->url(fn(AccountingClient $record) => route('admin.pdf.statement', $record->id))
                    ->openUrlInNewTab(),

                // 2. Share Statement via WhatsApp
                Action::make('whatsapp_statement')
                    ->label('إرسال كشف واتساب')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->action(function (AccountingClient $record, DocumentSharingService $shareService) {
                        $share = $shareService->getOrCreateShare('statement', $record->id, $record->id, 'whatsapp');
                        $url = $shareService->getWhatsAppUrl($share);
                        return redirect()->away($url);
                    }),

                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountingClients::route('/'),
            'create' => Pages\CreateAccountingClient::route('/create'),
            'edit' => Pages\EditAccountingClient::route('/{record}/edit'),
        ];
    }
}
