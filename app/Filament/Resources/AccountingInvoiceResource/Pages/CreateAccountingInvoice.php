<?php

namespace App\Filament\Resources\AccountingInvoiceResource\Pages;

use App\Filament\Resources\AccountingInvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAccountingInvoice extends CreateRecord
{
    protected static string $resource = AccountingInvoiceResource::class;

    protected function afterCreate(): void
    {
        $this->record->recalculateTotals();
    }
}
