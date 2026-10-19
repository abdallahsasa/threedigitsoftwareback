<?php

namespace App\Filament\Resources\AccountingServiceResource\Pages;

use App\Filament\Resources\AccountingServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountingService extends EditRecord
{
    protected static string $resource = AccountingServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
