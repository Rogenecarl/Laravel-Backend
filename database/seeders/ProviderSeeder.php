<?php

namespace Database\Seeders;

use App\Models\Categories;
use App\Models\OperatingHour;
use App\Models\Provider;
use App\Models\Services;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Categories::all();

        // Sample service names per category
        $servicesByCategory = [
            'Hospitals' => [
                ['name' => 'Emergency Consultation', 'price' => 2500],
                ['name' => 'General Check-up', 'price' => 3000],
                ['name' => 'Laboratory Services', 'price' => 2000],
            ],
            'Health Centers' => [
                ['name' => 'Primary Care Consultation', 'price' => 1500],
                ['name' => 'Vaccination', 'price' => 1000],
                ['name' => 'Health Screening', 'price' => 2000],
            ],
            'Dental Clinics' => [
                ['name' => 'Dental Check-up', 'price' => 1500],
                ['name' => 'Teeth Cleaning', 'price' => 2500],
                ['name' => 'Dental Filling', 'price' => 2000],
            ],
            'Veterinary' => [
                ['name' => 'Pet Check-up', 'price' => 1000],
                ['name' => 'Pet Vaccination', 'price' => 1500],
                ['name' => 'Pet Surgery Consultation', 'price' => 2000],
            ],
            'Dermatology' => [
                ['name' => 'Skin Consultation', 'price' => 2000],
                ['name' => 'Acne Treatment', 'price' => 2500],
                ['name' => 'Skin Care Treatment', 'price' => 3000],
            ],
        ];

        // Provider names
        $providerNames = [
            'HealthFirst Medical Center',
            'Care Plus Clinic',
            'Wellness Hub Healthcare',
            'Metro Medical Center',
            'Prime Care Hospital',
            'Healing Hands Clinic',
            'City Health Center',
            'Advanced Medical Facility',
            'Guardian Health Services',
            'Elite Medical Center',
            'Modern Care Clinic',
            'Unity Healthcare Center',
            'Sunshine Medical Clinic',
            'Hope Health Hub',
            'Life Care Medical Center',
        ];

        // Digos City areas
        $areas = [
            'Poblacion',
            'Tres de Mayo',
            'San Jose',
            'Zone 1',
            'Zone 2',
            'Zone 3',
            'Cogon',
            'Colorado',
            'Matti',
            'Binaton'
        ];

        foreach ($categories as $category) {
            // Create 3 providers per category
            for ($i = 0; $i < 3; $i++) {
                // Create user for provider
                $providerName = array_shift($providerNames);
                $name = fake()->name();

                $user = User::create([
                    'name' => $name,
                    'email' => strtolower(str_replace(' ', '.', $name) . '@' . str_replace(' ', '', strtolower($providerName)) . '.com'),
                    'password' => Hash::make('provider123'),
                    'role' => UserRole::Provider->value,
                ]);

                // Create provider
                $provider = Provider::create([
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                    'healthcare_name' => $providerName,
                    'description' => fake()->paragraph(),
                    'phone_number' => fake()->phoneNumber(),
                    'email' => $user->email,
                    'status' => 'verified',
                    'address' => fake()->streetAddress() . ', ' . fake()->randomElement($areas),
                    'city' => 'Digos City',
                    'province' => 'Davao del Sur',
                    // Random coordinates within Digos City bounds (approximately 2km radius from center)
                    'latitude' => fake()->randomFloat(6, 6.7346, 6.7546),  // Digos City coordinates
                    'longitude' => fake()->randomFloat(6, 125.3558, 125.3758),
                    'verified_at' => now(),
                ]);

                // Create operating hours for each day
                for ($day = 0; $day <= 6; $day++) {
                    OperatingHour::create([
                        'provider_id' => $provider->id,
                        'day_of_week' => $day,
                        'start_time' => $day != 0 ? '09:00' : null,  // Sunday (0) is closed
                        'end_time' => $day != 0 ? '17:00' : null,    // Sunday (0) is closed
                        'is_closed' => $day == 0,  // Sunday is closed
                    ]);
                }

                // Create 3 services for the provider
                $categoryServices = $servicesByCategory[$category->name] ?? $servicesByCategory['Hospitals'];
                foreach ($categoryServices as $service) {
                    Services::create([
                        'provider_id' => $provider->id,
                        'name' => $service['name'],
                        'description' => fake()->sentence(),
                        'price_min' => $service['price'],
                        'price_max' => $service['price'] + fake()->numberBetween(100, 500),
                        'is_active' => true,
                        'sort_order' => 0,
                    ]);
                }
            }
        }
    }
}
