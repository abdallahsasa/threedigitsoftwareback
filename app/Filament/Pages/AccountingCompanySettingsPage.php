<?php

namespace App\Filament\Pages;

use App\Models\AccountingCompanySetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class AccountingCompanySettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static string $view = 'filament.pages.accounting-company-settings';

    protected static ?string $navigationGroup = 'Accounting & Finance';

    protected static ?string $navigationLabel = 'Company & Invoice Settings';

    protected static ?string $title = 'Company Identity & Invoice Settings';

    protected static ?int $navigationSort = 10;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->canManageCompanySettings());
    }

    public function mount(): void
    {
        $settings = AccountingCompanySetting::getSettings();
        $this->form->fill($settings->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Company Identity & Legal Details / الهوية القانونية للشركة')
                    ->description('يجب تعيين الاسم القانوني الكامل والرقم الضريبي لتتمكن من إصدار فواتير قانونية معتمدة.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('brand_name')
                                ->label('Brand Name / الاسم التجاري المعروف')
                                ->required()
                                ->default('Three Digit Software'),

                            TextInput::make('legal_company_name')
                                ->label('Full Registered Legal Company Name / الاسم القانوني المسجل للشركة')
                                ->helperText('مطلوب لإصدار الفواتير. يظهر بارزاً في ترويسة الفاتورة الرسمية.')
                                ->placeholder('مثال: شركة ثري ديجيت للبرمجيات المحدودة المسؤولية'),

                            TextInput::make('tax_number')
                                ->label('Company Tax Number / الرقم الضريبي للشركة (Tax ID / TRN)')
                                ->helperText('مطلوب لإصدار الفواتير. لا يفعّل ضريبة القيمة المضافة تلقائياً.')
                                ->placeholder('مثال: 100-234-567'),

                            TextInput::make('commercial_registration_number')
                                ->label('Commercial Registration / رقم السجل التجاري')
                                ->placeholder('مثال: 12345/دمشق'),
                        ]),
                    ]),

                Section::make('Contact & Address / العنوان ووسائل التواصل')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('phone')
                                ->label('Phone Number / رقم الهاتف')
                                ->tel(),

                            TextInput::make('whatsapp')
                                ->label('WhatsApp / واتساب المراسلات')
                                ->tel(),

                            TextInput::make('email')
                                ->label('Official Email / البريد الرسمي للمراسلات')
                                ->email(),

                            TextInput::make('website')
                                ->label('Website / الموقع الإلكتروني')
                                ->url(),

                            TextInput::make('city')
                                ->label('City / المدينة'),

                            TextInput::make('country')
                                ->label('Country / الدولة'),

                            TextInput::make('registered_address')
                                ->label('Registered Address / العنوان المسجل التفصيلي')
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Banking & Payment Instructions / الحسابات البنكية وطرق الدفع')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('bank_name')
                                ->label('Bank Name / اسم المصرف'),

                            TextInput::make('bank_account_number')
                                ->label('Account Number / رقم الحساب'),

                            TextInput::make('bank_iban')
                                ->label('IBAN / رقم الآيبان الدولي'),

                            TextInput::make('bank_swift')
                                ->label('SWIFT / BIC Code'),

                            Textarea::make('payment_methods_text')
                                ->label('Payment Methods Description / تفاصيل طرق الدفع والمحافظ (تظهر بالفاتورة)')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Document Formatting & Terms / سياسات ومصطلحات الوثائق')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('invoice_prefix')
                                ->label('Invoice Number Prefix / بادئة ترقيم الفواتير')
                                ->default('TDS-INV-'),

                            TextInput::make('quotation_prefix')
                                ->label('Quotation Prefix / بادئة ترقيم عروض الأسعار')
                                ->default('TDS-QT-'),

                            TextInput::make('receipt_prefix')
                                ->label('Receipt Prefix / بادئة ترقيم سندات القبض')
                                ->default('TDS-REC-'),

                            TextInput::make('credit_note_prefix')
                                ->label('Credit Note Prefix / بادئة ترقيم الإشعارات الدائنة')
                                ->default('TDS-CN-'),

                            Textarea::make('default_payment_terms')
                                ->label('Default Payment Terms / شروط السداد الافتراضية')
                                ->rows(2)
                                ->columnSpanFull(),

                            Textarea::make('document_footer')
                                ->label('Document Footer / نص تذييل المستندات')
                                ->rows(2)
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Tax Policy / سياسة الضرائب')
                    ->description('الضرائب معطلة افتراضياً، وتحديد الرقم الضريبي لا يعني فرض ضريبة تلقائية.')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('default_tax_enabled')
                                ->label('Enable Taxes by Default / تفعيل الضرائب افتراضياً')
                                ->default(false),

                            TextInput::make('default_tax_rate')
                                ->label('Default Tax Rate (%) / نسبة الضريبة الافتراضية')
                                ->numeric()
                                ->default(0.00)
                                ->suffix('%'),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $settings = AccountingCompanySetting::getSettings();
        $settings->update($this->form->getState());

        Notification::make()
            ->title('تم حفظ إعدادات وهوية الشركة بنجاح')
            ->success()
            ->send();
    }
}
