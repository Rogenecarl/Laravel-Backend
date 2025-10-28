<?php

namespace App\Services;

use App\Models\Provider;
use App\Services\AppointmentService;
use Carbon\Carbon;

class ProviderDashboardServices
{
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    /**
     * Get dashboard overview data for a provider
     */
    public function getDashboardOverview(Provider $provider): array
    {
        $today = Carbon::today()->format('Y-m-d');
        
        // Get today's confirmed appointments
        $todaysAppointments = $this->appointmentService->getAppointmentsForProvider($provider, [
            'date' => $today,
            'status' => 'confirmed',
            'per_page' => 100
        ]);

        // Get pending appointments (all pending, not just today)
        $pendingAppointments = $this->appointmentService->getAppointmentsForProvider($provider, [
            'status' => 'pending',
            'per_page' => 50
        ]);

        // Get appointment counts by status
        $appointmentCounts = $this->appointmentService->getAppointmentCountsForProvider($provider);

        // Get monthly and weekly stats
        $monthlyStats = $this->getMonthlyStats($provider);
        $weeklyStats = $this->getWeeklyStats($provider);

        return [
            'todays_appointments' => [
                'data' => $todaysAppointments->items(),
                'count' => $todaysAppointments->total()
            ],
            'pending_appointments' => [
                'data' => $pendingAppointments->items(),
                'count' => $pendingAppointments->total()
            ],
            'appointment_counts' => $appointmentCounts,
            'monthly_stats' => $monthlyStats,
            'weekly_stats' => $weeklyStats,
            'date' => $today
        ];
    }

    /**
     * Get today's confirmed appointments only
     */
    public function getTodaysAppointments(Provider $provider): array
    {
        $today = Carbon::today()->format('Y-m-d');
        
        $appointments = $this->appointmentService->getAppointmentsForProvider($provider, [
            'date' => $today,
            'status' => 'confirmed',
            'per_page' => 100
        ]);

        return [
            'appointments' => $appointments->items(),
            'count' => $appointments->total(),
            'date' => $today
        ];
    }

    /**
     * Get pending appointments
     */
    public function getPendingAppointments(Provider $provider): array
    {
        $appointments = $this->appointmentService->getAppointmentsForProvider($provider, [
            'status' => 'pending',
            'per_page' => 50
        ]);

        return [
            'appointments' => $appointments->items(),
            'count' => $appointments->total()
        ];
    }

    /**
     * Get monthly statistics for confirmed appointments
     */
    public function getMonthlyStats(Provider $provider): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $monthlyAppointments = $provider->appointments()
            ->where('status', 'confirmed')
            ->whereBetween('start_time', [$startOfMonth, $endOfMonth])
            ->get();

        return [
            'total_confirmed' => $monthlyAppointments->count(),
            'month' => Carbon::now()->format('F Y'),
            'start_date' => $startOfMonth->format('Y-m-d'),
            'end_date' => $endOfMonth->format('Y-m-d')
        ];
    }

    /**
     * Get weekly statistics for confirmed appointments
     */
    public function getWeeklyStats(Provider $provider): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $weeklyAppointments = $provider->appointments()
            ->where('status', 'confirmed')
            ->whereBetween('start_time', [$startOfWeek, $endOfWeek])
            ->get();

        return [
            'total_confirmed' => $weeklyAppointments->count(),
            'week_range' => $startOfWeek->format('M d') . ' - ' . $endOfWeek->format('M d, Y'),
            'start_date' => $startOfWeek->format('Y-m-d'),
            'end_date' => $endOfWeek->format('Y-m-d')
        ];
    }

    /**
     * Get confirmed appointments for a specific date range
     */
    public function getConfirmedAppointmentsForDateRange(Provider $provider, string $startDate, string $endDate): array
    {
        $appointments = $provider->appointments()
            ->with(['user:id,name', 'services:id,name'])
            ->where('status', 'confirmed')
            ->whereBetween('start_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('start_time', 'asc')
            ->get();

        return [
            'appointments' => $appointments,
            'count' => $appointments->count(),
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
    }

    /**
     * Get popular services statistics based on completed appointments
     */
    public function getPopularServicesStats(Provider $provider, string $period = 'month'): array
    {
        // Determine date range based on period
        switch ($period) {
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'month':
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
        }

        // Get completed appointments with services for the specified period
        $appointments = $provider->appointments()
            ->with(['services:id,name'])
            ->where('status', 'completed')
            ->whereBetween('start_time', [$startDate, $endDate])
            ->get();

        // Count services from completed appointments
        $serviceStats = [];
        foreach ($appointments as $appointment) {
            foreach ($appointment->services as $service) {
                $serviceName = $service->name;
                if (!isset($serviceStats[$serviceName])) {
                    $serviceStats[$serviceName] = [
                        'service' => $serviceName,
                        'bookings' => 0
                    ];
                }
                $serviceStats[$serviceName]['bookings']++;
            }
        }

        // Sort by booking count (descending) and take top 10
        $sortedServices = collect($serviceStats)
            ->sortByDesc('bookings')
            ->take(10)
            ->values()
            ->toArray();

        return [
            'services' => $sortedServices,
            'period' => $period,
            'period_label' => $this->getPeriodLabel($period, $startDate, $endDate),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'total_completed_appointments' => $appointments->count()
        ];
    }

    /**
     * Get recent activity for a provider
     */
    public function getRecentActivity(Provider $provider, int $limit = 10): array
    {
        // Get recent appointments (last 7 days) with different statuses
        $recentAppointments = $provider->appointments()
            ->with(['user:id,name', 'services:id,name'])
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit($limit * 2) // Get more to filter and sort
            ->get();

        $activities = [];

        foreach ($recentAppointments as $appointment) {
            $userName = $appointment->user->name ?? 'Unknown User';
            $timeAgo = $this->getTimeAgo($appointment->created_at);
            
            // Different activity types based on appointment status and timing
            if ($appointment->status === 'pending') {
                $activities[] = [
                    'id' => 'appointment_' . $appointment->id,
                    'type' => 'appointment',
                    'message' => "New appointment booked by {$userName}",
                    'time' => $timeAgo,
                    'created_at' => $appointment->created_at,
                ];
            } elseif ($appointment->status === 'confirmed') {
                $activities[] = [
                    'id' => 'confirmed_' . $appointment->id,
                    'type' => 'confirmation',
                    'message' => "Appointment confirmed with {$userName}",
                    'time' => $timeAgo,
                    'created_at' => $appointment->created_at,
                ];
            } elseif ($appointment->status === 'completed') {
                $activities[] = [
                    'id' => 'completed_' . $appointment->id,
                    'type' => 'completion',
                    'message' => "Appointment completed with {$userName}",
                    'time' => $timeAgo,
                    'created_at' => $appointment->created_at,
                ];
            } elseif ($appointment->status === 'cancelled') {
                $activities[] = [
                    'id' => 'cancelled_' . $appointment->id,
                    'type' => 'cancellation',
                    'message' => "Appointment cancelled by {$userName}",
                    'time' => $timeAgo,
                    'created_at' => $appointment->created_at,
                ];
            }
        }

        // Sort by creation time and limit results
        $sortedActivities = collect($activities)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values()
            ->map(function ($activity) {
                // Remove created_at from final output
                unset($activity['created_at']);
                return $activity;
            })
            ->toArray();

        return [
            'activities' => $sortedActivities,
            'count' => count($sortedActivities),
            'period' => 'Last 7 days'
        ];
    }

    /**
     * Get human-readable time ago format
     */
    private function getTimeAgo(Carbon $dateTime): string
    {
        $now = Carbon::now();
        $diffInMinutes = $now->diffInMinutes($dateTime);
        $diffInHours = $now->diffInHours($dateTime);
        $diffInDays = $now->diffInDays($dateTime);

        if ($diffInMinutes < 1) {
            return 'Just now';
        } elseif ($diffInMinutes < 60) {
            return $diffInMinutes . ' minute' . ($diffInMinutes > 1 ? 's' : '') . ' ago';
        } elseif ($diffInHours < 24) {
            return $diffInHours . ' hour' . ($diffInHours > 1 ? 's' : '') . ' ago';
        } elseif ($diffInDays < 7) {
            return $diffInDays . ' day' . ($diffInDays > 1 ? 's' : '') . ' ago';
        } else {
            return $dateTime->format('M j, Y');
        }
    }

    /**
     * Get period label for display
     */
    private function getPeriodLabel(string $period, Carbon $startDate, Carbon $endDate): string
    {
        switch ($period) {
            case 'week':
                return $startDate->format('M d') . ' - ' . $endDate->format('M d, Y');
            case 'year':
                return $startDate->format('Y');
            case 'month':
            default:
                return $startDate->format('F Y');
        }
    }
}