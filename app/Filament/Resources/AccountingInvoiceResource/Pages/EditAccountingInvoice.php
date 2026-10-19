<?php

namespace App\Filament\Resources\AccountingInvoiceResource\Pages;

use App\Filament\Resources\AccountingInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountingInvoice extends EditRecord
{
    protected static string $resource = AccountingInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->visible(fn($record) => $record->document_status === 'draft'),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->record->document_status === 'draft') {
            $this->record->recalculateTotals();
        }
    }
}
