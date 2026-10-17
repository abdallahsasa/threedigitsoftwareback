<?php

namespace App\Filament\Resources\AccountingReviewResource\Pages;

use App\Filament\Resources\AccountingReviewResource;
use Filament\Resources\Pages\ListRecords;

class ListAccountingReviews extends ListRecords
{
    protected static string $resource = AccountingReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
