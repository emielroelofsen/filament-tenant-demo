<?php

namespace App\Filament\Resources\PurchaseOrderLineItems;

use App\Filament\Resources\PurchaseOrderLineItems\Pages\ManagePurchaseOrderLineItems;
use App\Models\PurchaseOrderLineItem;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PurchaseOrderLineItemResource extends Resource
{
    protected static ?string $model = PurchaseOrderLineItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        $tenant = Filament::getTenant();

        return $schema
            ->components([
                Select::make('purchase_order_id')
                    ->relationship(
                        name: 'purchaseOrder',
                        titleAttribute: 'order_number',
                        modifyQueryUsing: $tenant
                            ? fn ($query) => $query->where('organisation_id', $tenant->getKey())
                            : fn ($query) => $query,
                    )
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('product_id')
                    ->relationship(
                        name: 'product',
                        titleAttribute: 'name',
                        modifyQueryUsing: $tenant
                            ? fn ($query) => $query->where('organisation_id', $tenant->getKey())
                            : fn ($query) => $query,
                    )
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('quantity')->required()->numeric()->minValue(1),
                TextInput::make('unit_price')->required()->numeric()->minValue(0)->prefix('€'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('purchaseOrder.order_number')->sortable()->searchable(),
                TextColumn::make('product.brand.name')->sortable(),
                TextColumn::make('product.name')->sortable(),
                TextColumn::make('sku')
                    ->label(__('SKU'))
                    ->getStateUsing(fn (PurchaseOrderLineItem $record): string => $record->product?->sku ?? 'N/A')
                    ->placeholder('N/A'),
                TextColumn::make('quantity')->sortable(),
                TextColumn::make('unit_price')->money('EUR')->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePurchaseOrderLineItems::route('/'),
        ];
    }
}
