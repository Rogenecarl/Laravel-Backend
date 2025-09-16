<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\Services;
use App\Models\Categories;
use Illuminate\Support\Facades\DB;

class CategoryService
{

    // Get category with their providers.
    public function getProvidersByCategory(int $categoryId, string $status = 'verified')
    {
        return Provider::with('services')
            ->where('category_id', $categoryId)
            ->where('status', $status)
            ->get();
    }

    // Get all categories

    public function getAllCategories()
    {
        return Categories::all();
    }
}
