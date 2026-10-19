<?php

namespace App\Filament\Resources\AccountingQuotationResource\Pages;

use App\Filament\Resources\AccountingQuotationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountingQuotations extends ListRecords
{
    protected static string $resource = AccountingQuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('إنشاء عرض سعر جديد / New Quotation'),
        ];
    }
}
