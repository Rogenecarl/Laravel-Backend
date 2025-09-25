<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Provider>
 */
class ProviderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => null,
            'verified_by' => null,
            'healthcare_name' => $this->faker->company(),
            'description' => $this->faker->paragraph(),
            'phone_number' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'cover_photo' => null,
            'status' => $this->faker->randomElement(['pending', 'verified', 'suspended', 'rejected']),
            'address' => $this->faker->address(),
            'city' => 'Digos',
            'province' => 'Davao del Sur',
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'verified_at' => null,
        ];
    }
}
