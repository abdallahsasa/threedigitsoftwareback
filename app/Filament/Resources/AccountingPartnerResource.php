<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountingPartnerResource\Pages;
use App\Models\AccountingPartner;
use App\Models\AccountingTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AccountingPartnerResource extends Resource
{
    protected static ?string $model = AccountingPartner::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'نظام المحاسبة والمالية';

    protected static ?string $navigationLabel = 'حسابات الشركاء والديون';

    protected static ?string $modelLabel = 'شريك';

    protected static ?string $pluralModelLabel = 'حسابات الشركاء';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->canViewAccounting());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name_ar')
                    ->label('اسم الشريك (عربي)')
                    ->required(),
                Forms\Components\TextInput::make('name_en')
                    ->label('اسم الشريك (إنجليزي)')
                    ->required(),
                Forms\Components\TextInput::make('initial_debt_balance')
                    ->label('رصيد الدين المؤكد عند الانتقال (USD)')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات التسوية والتأسيس')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('الشريك')
                    ->weight('bold')
                    ->description(fn (AccountingPartner $record) => $record->name_en),

                Tables\Columns\TextColumn::make('initial_debt_balance')
                    ->label('الرصيد الافتتاحي المؤكد')
                    ->money('USD')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('new_funding')
                    ->label('تمويل إضافي')
                    ->state(function (AccountingPartner $record) {
                        return (float) AccountingTransaction::where('partner_id', $record->id)
                            ->where('transaction_type', 'partner_funding')
                            ->where('status', 'completed')
                            ->whereDate('transaction_date', '>', '2026-10-13')
                            ->sum('amount');
                    })
                    ->money('USD')
                    ->color('info'),

                Tables\Columns\TextColumn::make('personal_expenses')
                    ->label('مصاريف شخصية للشركة')
                    ->state(function (AccountingPartner $record) {
                        return (float) AccountingTransaction::where('partner_id', $record->id)
                            ->where('transaction_type', 'partner_personal_expense')
                            ->where('status', 'completed')
                            ->whereDate('transaction_date', '>', '2026-10-13')
                            ->sum('amount');
                    })
                    ->money('USD')
                    ->color('warning'),

                Tables\Columns\TextColumn::make('repayments')
                    ->label('سداد ديون مسترد')
                    ->state(function (AccountingPartner $record) {
                        return (float) AccountingTransaction::where('partner_id', $record->id)
                            ->where('transaction_type', 'partner_debt_repayment')
                            ->where('status', 'completed')
                            ->whereDate('transaction_date', '>', '2026-10-13')
                            ->sum('amount');
                    })
                    ->money('USD')
                    ->color('success'),

                Tables\Columns\TextColumn::make('current_debt')
                    ->label('صافي الدين المتبقي')
                    ->state(fn (AccountingPartner $record) => $record->calculated_debt_balance)
                    ->money('USD')
                    ->weight('extrabold')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('drawings')
                    ->label('السحوبات والتوزيعات (منفصلة)')
                    ->state(fn (AccountingPartner $record) => $record->total_drawings)
                    ->money('USD')
                    ->badge()
                    ->color('purple'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('كشف حساب الشريك')
                    ->modalHeading(fn (AccountingPartner $record) => "كشف حساب الشريك: {$record->name_ar}")
                    ->infolist([
                        Infolists\Components\Section::make('ملخص المركز المالي للشريك')
                            ->schema([
                                Infolists\Components\TextEntry::make('name_ar')->label('الاسم'),
                                Infolists\Components\TextEntry::make('initial_debt_balance')->label('رصيد دين الانتقال المؤكد (13 تشرين الأول 2026)')->money('USD'),
                                Infolists\Components\TextEntry::make('calculated_debt_balance')->label('إجمالي دين الشركة المستحق للشريك حالياً')->money('USD')->color('danger'),
                                Infolists\Components\TextEntry::make('total_drawings')->label('إجمالي السحوبات والتوزيعات (منفصل عن الدين)')->money('USD')->color('purple'),
                                Infolists\Components\TextEntry::make('notes')->label('ملاحظات التسوية والتأسيس')->columnSpanFull(),
                            ])
                            ->columns(2),
                    ]),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountingPartners::route('/'),
        ];
    }
}
