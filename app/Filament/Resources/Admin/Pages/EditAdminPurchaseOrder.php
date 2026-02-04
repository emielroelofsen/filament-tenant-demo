<?php

namespace App\Filament\Resources\Admin\Pages;

use App\Filament\Resources\Admin\AdminPurchaseOrderResource;
use Filament\Resources\Pages\EditRecord;

class EditAdminPurchaseOrder extends EditRecord
{
    protected static string $resource = AdminPurchaseOrderResource::class;
}
