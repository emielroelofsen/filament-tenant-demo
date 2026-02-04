<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Organisation;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLineItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrganisationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        $organisations = Organisation::factory(3)->create();

        foreach ($organisations as $organisation) {
            $organisation->users()->attach($user->id);

            $brands = Brand::factory(5)->create();

            foreach ($brands as $brand) {
                Product::factory(3)->create([
                    'organisation_id' => $organisation->id,
                    'brand_id' => $brand->id,
                ]);
            }

            $products = $organisation->products;

            PurchaseOrder::factory(5)->create(['organisation_id' => $organisation->id])
                ->each(function (PurchaseOrder $po) use ($organisation, $products): void {
                    $selected = $products->random(min(3, $products->count()));
                    foreach ($selected as $product) {
                        PurchaseOrderLineItem::factory()->create([
                            'organisation_id' => $organisation->id,
                            'purchase_order_id' => $po->id,
                            'product_id' => $product->id,
                        ]);
                    }
                });
        }
    }
}
