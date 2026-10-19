<?php

namespace App\Filament\Resources\AccountingPaymentResource\Pages;

use App\Filament\Resources\AccountingPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountingPayment extends EditRecord
{
    protected static string $resource = AccountingPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->visible(fn($record) => $record->journal_entry_id === null),
        ];
    }
}
