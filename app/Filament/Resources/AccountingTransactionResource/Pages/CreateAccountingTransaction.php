<?php

namespace App\Filament\Resources\AccountingTransactionResource\Pages;

use App\Filament\Resources\AccountingTransactionResource;
use App\Models\AccountingTransaction;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateAccountingTransaction extends CreateRecord
{
    protected static string $resource = AccountingTransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['transaction_number'] = AccountingTransaction::generateTransactionNumber();
        $data['created_by'] = Auth::id();
        $data['status'] = 'completed';
        $data['review_status'] = 'approved';
        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var AccountingTransaction $record */
        $record = $this->record;
        $record->postToLedger(Auth::id());
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
