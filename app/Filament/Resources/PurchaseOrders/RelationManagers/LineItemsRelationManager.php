<?php

namespace App\Filament\Resources\PurchaseOrders\RelationManagers;

use App\Models\PurchaseOrderLineItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LineItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'purchaseOrderLineItems';

    protected static ?string $title = 'Line Items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship(
                        name: 'product',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) => $query->where('organisation_id', $this->getOwnerRecord()->organisation_id),
                    )
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('quantity')->required()->numeric()->minValue(1),
                TextInput::make('unit_price')->required()->numeric()->minValue(0)->prefix('€'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                /**
                 * DEBUG:
                 * The three columns below do not show any value for the records that
                 * do not belong to the current tenant. This is regardless of whether
                 * we disable tenant scoping on the PurchaseOrderLineItemResource or not.
                 */
                TextColumn::make('product.brand.name')->sortable(),
                TextColumn::make('product.name')->sortable(),
                TextColumn::make('sku')
                    ->label(__('SKU'))
                    ->getStateUsing(function (PurchaseOrderLineItem $record): string {
                        return $record->product->sku ?? 'N/A';
                    })
                    ->placeholder('N/A'),
                TextColumn::make('quantity')->sortable(),
                TextColumn::make('unit_price')->money('EUR')->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                     /**
                     * BUG DEMO: Using $record->purchaseOrder->status throws when purchaseOrder is null.
                     * This happens on the Admin (non-tenant-scoped) Purchase Order edit page when editing
                     * a PO that belongs to another tenant: the PurchaseOrder model has a tenancy scope
                     * from the tenant-scoped PurchaseOrderResource, so lazy-loading $record->purchaseOrder
                     * returns null for cross-tenant records.
                     *
                     * This resulted in an error, which resulted in the relation manager not being displayed.
                     *
                     * The line below worked in V3, but not in V4 and V5.
                     *
                     * using $record->purchaseOrder->status is not working.
                     * so we're using $this->getOwnerRecord()->status instead.
                     *
                     */
                    // ->visible(fn (PurchaseOrderLineItem $record): bool => $record->purchaseOrder->status === 'draft')
                    ->visible(fn (PurchaseOrderLineItem $record): bool => $this->getOwnerRecord()->status === 'draft')
                    ->after(function (): void {
                        if (session()->has('purchase_order_deleted_redirect')) {
                            $redirectUrl = session()->pull('purchase_order_deleted_redirect');
                            $this->redirect($redirectUrl);
                        }
                    }),
            ]);
    }
}
