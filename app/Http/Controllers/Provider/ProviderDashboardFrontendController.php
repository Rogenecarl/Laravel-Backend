<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Services\ProviderDashboardServices;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProviderDashboardFrontendController extends Controller
{
    protected $dashboardService;

    public function __construct(ProviderDashboardServices $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get dashboard overview data
     */
    public function getDashboardOverview(Request $request): JsonResponse
    {
        try {
            // Get the authenticated provider
            $provider = $request->user()->provider;

            if (!$provider) {
                return response()->json([
                    'success' => false,
                    'message' => 'Provider not found'
                ], 404);
            }

            $dashboardData = $this->dashboardService->getDashboardOverview($provider);

            return response()->json([
                'success' => true,
                'data' => $dashboardData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get today's confirmed appointments
     */
    public function getTodaysAppointments(Request $request): JsonResponse
    {
        try {
            $provider = $request->user()->provider;

            if (!$provider) {
                return response()->json([
                    'success' => false,
                    'message' => 'Provider not found'
                ], 404);
            }

            $todaysAppointments = $this->dashboardService->getTodaysAppointments($provider);

            return response()->json([
                'success' => true,
                'data' => $todaysAppointments
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch today\'s appointments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending appointments
     */
    public function getPendingAppointments(Request $request): JsonResponse
    {
        try {
            $provider = $request->user()->provider;

            if (!$provider) {
                return response()->json([
                    'success' => false,
                    'message' => 'Provider not found'
                ], 404);
            }

            $pendingAppointments = $this->dashboardService->getPendingAppointments($provider);

            return response()->json([
                'success' => true,
                'data' => $pendingAppointments
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending appointments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get monthly statistics
     */
    public function getMonthlyStats(Request $request): JsonResponse
    {
        try {
            $provider = $request->user()->provider;

            if (!$provider) {
                return response()->json([
                    'success' => false,
                    'message' => 'Provider not found'
                ], 404);
            }

            $monthlyStats = $this->dashboardService->getMonthlyStats($provider);

            return response()->json([
                'success' => true,
                'data' => $monthlyStats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch monthly statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get weekly statistics
     */
    public function getWeeklyStats(Request $request): JsonResponse
    {
        try {
            $provider = $request->user()->provider;

            if (!$provider) {
                return response()->json([
                    'success' => false,
                    'message' => 'Provider not found'
                ], 404);
            }

            $weeklyStats = $this->dashboardService->getWeeklyStats($provider);

            return response()->json([
                'success' => true,
                'data' => $weeklyStats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch weekly statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get confirmed appointments for a date range
     */
    public function getConfirmedAppointmentsForDateRange(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date'
            ]);

            $provider = $request->user()->provider;

            if (!$provider) {
                return response()->json([
                    'success' => false,
                    'message' => 'Provider not found'
                ], 404);
            }

            $appointments = $this->dashboardService->getConfirmedAppointmentsForDateRange(
                $provider,
                $request->start_date,
                $request->end_date
            );

            return response()->json([
                'success' => true,
                'data' => $appointments
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch appointments for date range',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get popular services statistics
     */
    public function getPopularServicesStats(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'period' => 'sometimes|in:week,month,year'
            ]);

            $provider = $request->user()->provider;

            if (!$provider) {
                return response()->json([
                    'success' => false,
                    'message' => 'Provider not found'
                ], 404);
            }

            $period = $request->get('period', 'month'); // Default to month
            $servicesStats = $this->dashboardService->getPopularServicesStats($provider, $period);

            return response()->json([
                'success' => true,
                'data' => $servicesStats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch popular services statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent activity
     */
    public function getRecentActivity(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'limit' => 'sometimes|integer|min:1|max:50'
            ]);

            $provider = $request->user()->provider;

            if (!$provider) {
                return response()->json([
                    'success' => false,
                    'message' => 'Provider not found'
                ], 404);
            }

            $limit = $request->get('limit', 10); // Default to 10 activities
            $recentActivity = $this->dashboardService->getRecentActivity($provider, $limit);

            return response()->json([
                'success' => true,
                'data' => $recentActivity
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent activity',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}