<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountingClientResource\Pages;
use App\Models\AccountingClient;
use App\Models\AccountingTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AccountingClientResource extends Resource
{
    protected static ?string $model = AccountingClient::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'نظام المحاسبة والمالية';

    protected static ?string $navigationLabel = 'العملاء والمستحقات';

    protected static ?string $modelLabel = 'عميل';

    protected static ?string $pluralModelLabel = 'العملاء والمستحقات';

    protected static ?int $navigationSort = 4;

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->canViewAccounting());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم العميل / الشركة')
                    ->required(),
                Forms\Components\TextInput::make('contact_person')
                    ->label('الشخص المسؤول'),
                Forms\Components\TextInput::make('phone')
                    ->label('الهاتف'),
                Forms\Components\TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email(),
                Forms\Components\TextInput::make('potential_receivable_amount')
                    ->label('مستحقات محتملة تحتاج تأكيداً (USD)')
                    ->numeric()
                    ->prefix('$')
                    ->helperText('ملاحظات المتبقي في الإكسل (مثلاً الكيلاني 350$، FRS 315$، Max Pro 150$)؛ تعرض كمستحق محتمل وليس رصيداً نقدياً مؤكداً.'),
                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات العميل والتسويات')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('العميل')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_collected')
                    ->label('إجمالي المقبوض فعلياً')
                    ->state(fn (AccountingClient $record) => (float) AccountingTransaction::where('client_id', $record->id)
                        ->where('transaction_type', 'client_collection')
                        ->where('status', 'completed')
                        ->sum('amount'))
                    ->money('USD')
                    ->color('success')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('potential_receivable_amount')
                    ->label('مستحقات محتملة (تحتاج تأكيد)')
                    ->money('USD')
                    ->badge()
                    ->color(fn ($state) => (float)$state > 0 ? 'warning' : 'gray'),

                Tables\Columns\TextColumn::make('projects_count')
                    ->label('المشاريع')
                    ->counts('projects')
                    ->badge(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات المستحقات')
                    ->limit(40),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountingClients::route('/'),
        ];
    }
}
