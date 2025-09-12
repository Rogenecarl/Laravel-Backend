<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProviderRequest;
use App\Models\Provider;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $provider = Provider::all();

        return response()->json([
            'provider' => $provider,
            'message' => 'Provider list retrieved successfully',
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProviderRequest $request)
    {
        $provider = Provider::create($request->validated());

        return response()->json([
            'provider' => $provider,
            'message' => 'Provider created successfully',
        ], 201);
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
