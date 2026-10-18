<?php

namespace App\Filament\Resources\AccountingClientResource\Pages;

use App\Filament\Resources\AccountingClientResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountingClients extends ListRecords
{
    protected static string $resource = AccountingClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Add Client'),
        ];
    }
}
