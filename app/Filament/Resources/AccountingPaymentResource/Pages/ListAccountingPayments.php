<?php

namespace App\Filament\Resources\AccountingPaymentResource\Pages;

use App\Filament\Resources\AccountingPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountingPayments extends ListRecords
{
    protected static string $resource = AccountingPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('تسجيل دفعة / سند قبض جديد / New Payment'),
        ];
    }
}
