<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;
use App\Services\CategoryService;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProviderResource;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;

class CategoryController extends Controller
{

    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }


    /**
     * Category index Get all the categories with providers count
     */

    public function indexByCategory(int $categoryId)
    {
        // check if the category exists
        Categories::findOrFail($categoryId);

        // if exists, get the providers from the service
        $providers = $this->categoryService->getProvidersByCategory($categoryId);

        // no providers found
        if ($providers->isEmpty()) {
            return response()->json([
                'providers' => [],
                'count' => 0,
                'message' => 'No providers found in this category.',
            ], 200);
        }

        // return if providers found
        return response()->json([
            'providers' => ProviderResource::collection($providers),
            'count' => $providers->count(),
            'message' => 'Providers retrieved successfully',
        ], 200);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = $this->categoryService->getAllCategories();

        if ($categories->isEmpty()) {
            return response()->json([
                'categories' => [],
                'message' => 'No categories found.',
            ], 200);
        }

        return response()->json([
            'categories' => CategoryResource::collection($categories),
            'message' => 'Categories retrieved successfully',
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        $category = Categories::create($request->validated());

        return response()->json([
            'category' => new CategoryResource($category),
            'message' => 'Category created successfully',
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Categories $category)
    {
        return response()->json([
            'category' => new CategoryResource($category),
            'message' => 'Category retrieved successfully',
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Categories $category)
    {

        $category->update($request->validated());

        return response()->json([
            'category' => new CategoryResource($category),
            'message' => 'Category updated successfully',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categories $category)
    {
        $category->delete();

        return response()->json([
            "message" => "Category deleted successfully"
        ]);
    }
}
