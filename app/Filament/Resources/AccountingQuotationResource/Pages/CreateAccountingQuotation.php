<?php

namespace App\Filament\Resources\AccountingQuotationResource\Pages;

use App\Filament\Resources\AccountingQuotationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAccountingQuotation extends CreateRecord
{
    protected static string $resource = AccountingQuotationResource::class;

    protected function afterCreate(): void
    {
        $this->record->recalculateTotals();
    }
}
