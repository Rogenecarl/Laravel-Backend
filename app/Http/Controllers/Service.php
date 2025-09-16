<?php

namespace App\Http\Controllers;

use App\Models\Services;
use Illuminate\Http\Request;
use App\Http\Resources\ServiceResource;

class Service extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Services::paginate(10);

        return response()->json([
            'services' => ServiceResource::collection($services),
            'message' => 'Services retrieved successfully',
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
