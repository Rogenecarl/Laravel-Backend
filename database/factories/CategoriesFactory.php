<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Categories>
 */
class CategoriesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        return [
            "name" => $this->faker->name,
            "slug" => $this->faker->slug,
            "description" => $this->faker->sentence,
            "icon" => $this->faker->imageUrl,
            "color" => $this->faker->hexColor,
            "is_active" => $this->faker->boolean,
            "sort_order" => $this->faker->numberBetween(1, 100),
        ];
    }
}
