<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return ['name' => $name, 'slug' => str($name)->slug().'-'.fake()->unique()->numberBetween(1, 99999), 'description' => fake()->sentence(), 'is_active' => true];
    }
}
