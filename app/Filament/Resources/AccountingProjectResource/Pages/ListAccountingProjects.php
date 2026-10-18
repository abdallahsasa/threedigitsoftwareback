<?php

namespace App\Filament\Resources\AccountingProjectResource\Pages;

use App\Filament\Resources\AccountingProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountingProjects extends ListRecords
{
    protected static string $resource = AccountingProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Project'),
        ];
    }
}
