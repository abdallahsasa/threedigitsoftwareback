<?php

namespace App\Filament\Resources\AccountingPaymentResource\Pages;

use App\Filament\Resources\AccountingPaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAccountingPayment extends CreateRecord
{
    protected static string $resource = AccountingPaymentResource::class;

    protected function afterCreate(): void
    {
        $rawAllocations = $this->data['allocations'] ?? [];
        $this->record->postPayment($rawAllocations);
    }
}
