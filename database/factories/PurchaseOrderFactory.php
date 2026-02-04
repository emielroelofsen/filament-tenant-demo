<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organisation_id' => \App\Models\Organisation::factory(),
            'order_number' => 'PO-' . fake()->unique()->numberBetween(1000, 99999),
            'status' => fake()->randomElement(['draft', 'submitted', 'approved', 'received']),
        ];
    }
}
