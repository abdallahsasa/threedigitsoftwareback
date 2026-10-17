<?php

namespace App\Filament\Resources\AccountingPartnerResource\Pages;

use App\Filament\Resources\AccountingPartnerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountingPartners extends ListRecords
{
    protected static string $resource = AccountingPartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
