<?php

namespace App\Http\Controllers;

use App\Models\Services;
use Illuminate\Http\Request;
use App\Http\Resources\ServiceResource;
use App\Http\Requests\StoreServicesRequest;
use App\Http\Requests\UpdateServicesRequest;
use App\Services\ServiceService;

class ServiceController extends Controller
{
    protected $serviceService;

    public function __construct(ServiceService $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // If provider_id is provided, filter by provider
        $services = $request->has('provider_id')
            ? Services::where('provider_id', $request->provider_id)->paginate(10)
            : Services::paginate(10);

        return response()->json([
            'services' => ServiceResource::collection($services),
            'message' => 'Services retrieved successfully',
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreServicesRequest $request)
    {
        try {
            $validatedData = $request->validated();

            // Get provider ID from authenticated user
            $user = auth()->user();
            $providerId = $user->provider?->id;

            if (!$providerId) {
                return response()->json([
                    'message' => 'Provider not found for authenticated user',
                ], 403);
            }

            $validatedData['provider_id'] = $providerId;
            $service = $this->serviceService->createService($validatedData);

            return response()->json([
                'service' => new ServiceResource($service),
                'message' => 'Service created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create service',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $service = $this->serviceService->getService($id);

            return response()->json([
                'service' => new ServiceResource($service),
                'message' => 'Service retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Service not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServicesRequest $request, string $id)
    {
        try {
            // Get provider ID from authenticated user
            $user = auth()->user();
            $providerId = $user->provider?->id;

            if (!$providerId) {
                return response()->json([
                    'message' => 'Provider not found for authenticated user',
                ], 403);
            }

            // Check if service belongs to the authenticated provider
            $service = Services::findOrFail($id);
            if ($service->provider_id !== $providerId) {
                return response()->json([
                    'message' => 'Unauthorized to update this service',
                ], 403);
            }

            $validatedData = $request->validated();
            $validatedData['provider_id'] = $providerId; // Ensure provider_id stays the same

            $service = $this->serviceService->updateService($id, $validatedData);

            return response()->json([
                'service' => new ServiceResource($service),
                'message' => 'Service updated successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update service',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Get provider ID from authenticated user
            $user = auth()->user();
            $providerId = $user->provider?->id;

            if (!$providerId) {
                return response()->json([
                    'message' => 'Provider not found for authenticated user',
                ], 403);
            }

            // Check if service belongs to the authenticated provider
            $service = Services::findOrFail($id);
            if ($service->provider_id !== $providerId) {
                return response()->json([
                    'message' => 'Unauthorized to delete this service',
                ], 403);
            }

            $this->serviceService->deleteService($id);

            return response()->json([
                'message' => 'Service deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete service',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get services by provider ID
     */
    public function getServicesByProvider(string $providerId, Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $services = Services::where('provider_id', $providerId)->paginate(10, ['*'], 'page', $page);

            return response()->json([
                'services' => [
                    'data' => ServiceResource::collection($services->items()),
                    'current_page' => $services->currentPage(),
                    'last_page' => $services->lastPage(),
                    'per_page' => $services->perPage(),
                    'total' => $services->total(),
                ],
                'message' => 'Provider services retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve provider services',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get services for authenticated provider
     */
    public function getMyServices(Request $request)
    {
        try {
            $user = auth()->user();
            $providerId = $user->provider?->id;

            if (!$providerId) {
                return response()->json([
                    'message' => 'Provider not found for authenticated user',
                ], 403);
            }

            $page = $request->get('page', 1);
            $services = Services::where('provider_id', $providerId)->paginate(10, ['*'], 'page', $page);

            return response()->json([
                'services' => [
                    'data' => ServiceResource::collection($services->items()),
                    'current_page' => $services->currentPage(),
                    'last_page' => $services->lastPage(),
                    'per_page' => $services->perPage(),
                    'total' => $services->total(),
                ],
                'message' => 'Your services retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve your services',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
