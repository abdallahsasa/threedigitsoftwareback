<?php

namespace App\Filament\Resources\AccountingServiceResource\Pages;

use App\Filament\Resources\AccountingServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountingServices extends ListRecords
{
    protected static string $resource = AccountingServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('إضافة خدمة جديدة / Add Service'),
        ];
    }
}
