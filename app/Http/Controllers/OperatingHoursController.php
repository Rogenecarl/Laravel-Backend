<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOperatingHourRequest;
use App\Services\OperatingHoursService;
use Illuminate\Http\JsonResponse;

class OperatingHoursController extends Controller
{
    protected $operatingHoursService;

    public function __construct(OperatingHoursService $operatingHoursService)
    {
        $this->operatingHoursService = $operatingHoursService;
    }

    /**
     * Get operating hours for the authenticated provider
     */
    public function getMyOperatingHours(): JsonResponse
    {
        try {
            $user = auth()->user();
            $providerId = $user->provider?->id;

            if (!$providerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Provider not found for authenticated user',
                ], 403);
            }

            $operatingHours = $this->operatingHoursService->getOperatingHours($providerId);

            return response()->json([
                'success' => true,
                'operating_hours' => $operatingHours
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve operating hours',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update operating hours for the authenticated provider
     */
    public function updateMyOperatingHours(UpdateOperatingHourRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $providerId = $user->provider?->id;

            if (!$providerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Provider not found for authenticated user',
                ], 403);
            }

            $operatingHours = $this->operatingHoursService->updateOperatingHours(
                $providerId,
                $request->validated()['operating_hours']
            );

            return response()->json([
                'success' => true,
                'message' => 'Operating hours updated successfully',
                'operating_hours' => $operatingHours
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update operating hours',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}