<?php

namespace App\Filament\Resources\PurchaseOrderLineItems\Pages;

use App\Filament\Resources\PurchaseOrderLineItems\PurchaseOrderLineItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePurchaseOrderLineItems extends ManageRecords
{
    protected static string $resource = PurchaseOrderLineItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
