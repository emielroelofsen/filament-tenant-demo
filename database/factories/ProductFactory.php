<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organisation_id' => Organisation::factory(),
            'brand_id' => Brand::factory(),
            'ean' => fake()->unique()->numerify('#############'),
            'sku' => strtoupper(fake()->unique()->bothify('???-####')),
            'name' => fake()->words(3, true),
        ];
    }
}
