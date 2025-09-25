<?php

namespace Database\Factories;

use App\Models\BreakTime;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BreakTime>
 */
class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startHour = $this->faker->numberBetween(9, 16);
        $startMinute = $this->faker->randomElement([0, 15, 30, 45]);
        $durationMinutes = $this->faker->randomElement([15, 30, 45, 60]);
        
        $startTime = sprintf('%02d:%02d:00', $startHour, $startMinute);
        $endTime = date('H:i:s', strtotime($startTime) + ($durationMinutes * 60));

        return [
            'provider_id' => Provider::factory(),
            'name' => $this->faker->randomElement([
                'Lunch Break',
                'Morning Break',
                'Afternoon Break',
                'Admin Time',
                'Coffee Break'
            ]),
            'day_of_week' => $this->faker->numberBetween(0, 6), // 0 = Sunday, 6 = Saturday
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];
    }

    /**
     * Create a lunch break.
     */
    public function lunchBreak(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Lunch Break',
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);
    }

    /**
     * Create a morning break.
     */
    public function morningBreak(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Morning Break',
            'start_time' => '10:00:00',
            'end_time' => '10:15:00',
        ]);
    }

    /**
     * Create a break for a specific day.
     */
    public function forDay(int $dayOfWeek): static
    {
        return $this->state(fn (array $attributes) => [
            'day_of_week' => $dayOfWeek,
        ]);
    }
}