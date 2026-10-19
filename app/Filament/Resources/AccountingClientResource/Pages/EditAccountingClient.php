<?php

namespace App\Filament\Resources\AccountingClientResource\Pages;

use App\Filament\Resources\AccountingClientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountingClient extends EditRecord
{
    protected static string $resource = AccountingClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
