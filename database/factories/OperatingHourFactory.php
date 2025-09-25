<?php

namespace Database\Factories;

use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OperatingHour>
 */
class OperatingHourFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider_id' => Provider::factory(),
            'day_of_week' => $this->faker->numberBetween(0, 6),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_closed' => false,
        ];
    }

    /**
     * Create a closed day
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => null,
            'end_time' => null,
            'is_closed' => true,
        ]);
    }

    /**
     * Create operating hours for a specific day
     */
    public function forDay(int $dayOfWeek): static
    {
        return $this->state(fn (array $attributes) => [
            'day_of_week' => $dayOfWeek,
        ]);
    }

    /**
     * Create operating hours with custom times
     */
    public function withTimes(string $startTime, string $endTime): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_closed' => false,
        ]);
    }
}
