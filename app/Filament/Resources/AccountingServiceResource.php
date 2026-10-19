<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountingServiceResource\Pages;
use App\Models\AccountingService;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AccountingServiceResource extends Resource
{
    protected static ?string $model = AccountingService::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Accounting & Finance';

    protected static ?string $navigationLabel = 'Services Catalog';

    protected static ?string $modelLabel = 'Service';

    protected static ?string $pluralModelLabel = 'Services Catalog';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('name_ar')
                        ->label('Service Name (Arabic) / اسم الخدمة بالعربية')
                        ->required(),

                    TextInput::make('name_en')
                        ->label('Service Name (English) / اسم الخدمة بالإنجليزية')
                        ->required(),

                    Select::make('category')
                        ->label('Category / تصنيف الخدمة')
                        ->options(AccountingService::CATEGORIES)
                        ->required(),

                    TextInput::make('unit')
                        ->label('Billing Unit / وحدة القياس (ساعة / مشروع / شهر / خدمة)')
                        ->default('service')
                        ->required(),

                    TextInput::make('default_price')
                        ->label('Default Price (USD) / السعر الافتراضي')
                        ->numeric()
                        ->prefix('$')
                        ->default(0.00)
                        ->required(),

                    Toggle::make('is_active')
                        ->label('Active / متاحة للفوترة وعروض الأسعار')
                        ->default(true),

                    Textarea::make('description_ar')
                        ->label('Description (Arabic) / تفاصيل نطاق الخدمة')
                        ->rows(2),

                    Textarea::make('description_en')
                        ->label('Description (English) / Scope Description')
                        ->rows(2),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_ar')
                    ->label('الاسم بالعربية')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('name_en')
                    ->label('English Name')
                    ->searchable(),

                TextColumn::make('category')
                    ->label('التصنيف')
                    ->formatStateUsing(fn(string $state): string => AccountingService::CATEGORIES[$state] ?? $state)
                    ->badge(),

                TextColumn::make('default_price')
                    ->label('السعر الافتراضي')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('unit')
                    ->label('الوحدة'),

                IconColumn::make('is_active')
                    ->label('مفعّلة')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('التصنيف')
                    ->options(AccountingService::CATEGORIES),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountingServices::route('/'),
            'create' => Pages\CreateAccountingService::route('/create'),
            'edit' => Pages\EditAccountingService::route('/{record}/edit'),
        ];
    }
}
