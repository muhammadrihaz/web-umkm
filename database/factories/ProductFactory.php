<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return ['category_id' => Category::factory(), 'sku' => fake()->unique()->bothify('SKU-####??'), 'name' => $name, 'slug' => str($name)->slug().'-'.fake()->unique()->numberBetween(1, 99999), 'description' => fake()->sentence(), 'unit' => 'item', 'price' => fake()->numberBetween(1000, 500000), 'stock_on_hand' => 10, 'stock_reserved' => 0, 'is_active' => true];
    }
}
