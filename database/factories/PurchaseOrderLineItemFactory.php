<?php

namespace Database\Factories;

use App\Models\Organisation;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLineItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseOrderLineItem>
 */
class PurchaseOrderLineItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quantity' => fake()->numberBetween(1, 100),
            'unit_price' => fake()->randomFloat(2, 1, 500),
        ];
    }

    /**
     * Configure the factory so that organisation, purchase_order and product are set when not provided.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (PurchaseOrderLineItem $item): void {
            if ($item->organisation_id !== null) {
                return;
            }
            $org = Organisation::factory()->create();
            $po = PurchaseOrder::factory()->create(['organisation_id' => $org->id]);
            $product = Product::factory()->create(['organisation_id' => $org->id]);
            $item->organisation_id = $org->id;
            $item->purchase_order_id = $po->id;
            $item->product_id = $product->id;
        });
    }
}
