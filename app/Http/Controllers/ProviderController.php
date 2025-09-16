<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Http\Request;
use App\Services\ProviderService;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProviderResource;
use App\Http\Requests\StoreProviderRequest;
use App\Http\Requests\UpdateProviderRequest;

class ProviderController extends Controller
{
    protected $providerService;

    public function __construct(ProviderService $providerService)
    {
        $this->providerService = $providerService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $providers = $this->providerService->getAllProviders();

        return response()->json([
            'providers' => ProviderResource::collection($providers),
            'count' => $providers->count(),
            'message' => 'Providers retrieved successfully',
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProviderRequest $request)
    {

        // 1. Get the validated data from the form request.
        $providerData = $request->validated();

        // 2. Get the authenticated user's ID and add it to the data array.
        $providerData['user_id'] = Auth::id(); // or $request->user()->id

        // 3. Pass the complete data array to the service.
        $provider = $this->providerService->createProvider($providerData);

        return response()->json([
            'provider' => new ProviderResource($provider),
            'message' => 'Provider created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $provider = $this->providerService->getProvider($id);
        return response()->json([
            'provider' => new ProviderResource($provider),
            'message' => 'Provider retrieved successfully',
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProviderRequest $request, Provider $provider)
    {
        $provider->update($request->validated());

        return response()->json([
            'provider' => new ProviderResource($provider),
            'message' => 'Provider updated successfully',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
