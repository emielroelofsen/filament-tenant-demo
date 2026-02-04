<?php

namespace App\Filament\Resources\Admin\Pages;

use App\Filament\Resources\Admin\AdminPurchaseOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAdminPurchaseOrders extends ManageRecords
{
    protected static string $resource = AdminPurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
