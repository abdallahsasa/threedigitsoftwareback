<?php

namespace App\Filament\Resources\AccountingProjectResource\Pages;

use App\Filament\Resources\AccountingProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountingProject extends EditRecord
{
    protected static string $resource = AccountingProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
