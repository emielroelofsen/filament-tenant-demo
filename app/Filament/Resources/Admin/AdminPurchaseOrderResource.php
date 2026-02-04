<?php

namespace App\Filament\Resources\Admin;

use App\Filament\Resources\Admin\Pages\EditAdminPurchaseOrder;
use App\Filament\Resources\Admin\Pages\ManageAdminPurchaseOrders;
use App\Filament\Resources\PurchaseOrders\RelationManagers\LineItemsRelationManager;
use App\Models\PurchaseOrder;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdminPurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static bool $isScopedToTenant = false;

    protected static ?string $recordTitleAttribute = 'order_number';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    protected static ?string $navigationLabel = 'Purchase Orders';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('organisation_id')
                    ->relationship('organisation', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('order_number')->required()->maxLength(255),
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'approved' => 'Approved',
                        'received' => 'Received',
                    ])
                    ->required()
                    ->default('draft'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('organisation.name')->sortable()->searchable(),
                TextColumn::make('order_number')->searchable()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('purchaseOrderLineItems')
                    ->label(__('# Line Items'))
                    ->getStateUsing(function ($record) {
                    return $record->purchaseOrderLineItems->count();
                    })
                    ->alignCenter(),
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

    public static function getRelations(): array
    {
        return [
            LineItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAdminPurchaseOrders::route('/'),
            'edit' => EditAdminPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
