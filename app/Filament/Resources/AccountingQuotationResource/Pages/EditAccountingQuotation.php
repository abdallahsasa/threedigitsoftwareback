<?php

namespace App\Filament\Resources\AccountingQuotationResource\Pages;

use App\Filament\Resources\AccountingQuotationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountingQuotation extends EditRecord
{
    protected static string $resource = AccountingQuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->record->recalculateTotals();
    }
}
