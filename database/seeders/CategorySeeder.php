<?php

namespace Database\Seeders;

use App\Models\Categories;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Hospitals',
                'slug' => 'hospitals',
                'description' => 'Full-service medical facilities with emergency care',
                'icon' => '🏥',
                'color' => '#EF4444',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Health Centers',
                'slug' => 'health-centers',
                'description' => 'Primary healthcare and wellness centers',
                'icon' => '🏢',
                'color' => '#10B981',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Dental Clinics',
                'slug' => 'dental-clinics',
                'description' => 'Dental care and oral health services',
                'icon' => '🦷',
                'color' => '#3B82F6',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Veterinary',
                'slug' => 'veterinary',
                'description' => 'Animal healthcare and veterinary services',
                'icon' => '🐾',
                'color' => '#F59E0B',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Dermatology',
                'slug' => 'dermatology',
                'description' => 'Skin care and dermatological treatments',
                'icon' => '✨',
                'color' => '#8B5CF6',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            Categories::create($category);
        }
    }
}
