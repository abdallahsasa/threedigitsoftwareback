<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountingProjectResource\Pages;
use App\Models\AccountingClient;
use App\Models\AccountingProject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AccountingProjectResource extends Resource
{
    protected static ?string $model = AccountingProject::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'نظام المحاسبة والمالية';

    protected static ?string $navigationLabel = 'المشاريع والربحية';

    protected static ?string $modelLabel = 'مشروع محاسبي';

    protected static ?string $pluralModelLabel = 'المشاريع والربحية';

    protected static ?int $navigationSort = 5;

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
                    ->label('اسم المشروع')
                    ->required(),
                Forms\Components\Select::make('client_id')
                    ->label('العميل')
                    ->options(AccountingClient::pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\TextInput::make('total_price')
                    ->label('قيمة العقد الإجمالية (USD)')
                    ->numeric()
                    ->prefix('$')
                    ->helperText('قيمة العقد التعاقدية؛ لا تُعتبر إيراداً مقبوضاً تلقائياً حتى يتم التحصيل الفعلي.'),
                Forms\Components\TextInput::make('direct_costs')
                    ->label('التكاليف المباشرة (USD)')
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\Select::make('status')
                    ->label('حالة المشروع')
                    ->options([
                        'in_progress' => 'قيد التنفيذ (In Progress)',
                        'done' => 'مكتمل (Done)',
                        'cancelled' => 'ملغى (Cancelled - لا يُعتبر مستحقاً تلقائياً)',
                    ])
                    ->default('in_progress')
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات المشروع')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('المشروع')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('client.name')
                    ->label('العميل')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('قيمة العقد')
                    ->money('USD')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('total_collected')
                    ->label('المقبوض فعلياً')
                    ->money('USD')
                    ->color('success')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('remaining_balance')
                    ->label('المتبقي على العقد')
                    ->money('USD')
                    ->badge()
                    ->color(fn ($state) => (float)$state > 0 ? 'warning' : 'gray'),

                Tables\Columns\TextColumn::make('direct_costs')
                    ->label('التكاليف المباشرة')
                    ->money('USD')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('net_profit')
                    ->label('هامش الربح المتوقع')
                    ->money('USD')
                    ->color('info')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'done' => 'مكتمل',
                        'cancelled' => 'ملغى',
                        default => 'قيد التنفيذ',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'done' => 'success',
                        'cancelled' => 'danger',
                        default => 'warning',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'done' => 'مكتمل',
                        'in_progress' => 'قيد التنفيذ',
                        'cancelled' => 'ملغى',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountingProjects::route('/'),
        ];
    }
}
