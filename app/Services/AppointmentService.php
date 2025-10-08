<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Provider;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AppointmentService
{
    /**
     * Create a new appointment with multiple services
     */
    public function createAppointment(array $appointmentData): Appointment
    {
        return DB::transaction(function () use ($appointmentData) {
            // Extract services data before creating appointment
            $servicesData = $appointmentData['services'];
            unset($appointmentData['services']);

            // Generate unique appointment number
            $appointmentData['appointment_number'] = $this->generateUniqueAppointmentNumber();

            // Calculate total price from services if not provided
            if (!isset($appointmentData['total_price'])) {
                $appointmentData['total_price'] = collect($servicesData)->sum('price_at_booking');
            }

            // Create the appointment
            $appointment = Appointment::create($appointmentData);

            // Attach services to the appointment
            foreach ($servicesData as $serviceData) {
                $appointment->services()->attach($serviceData['service_id'], [
                    'price_at_booking' => $serviceData['price_at_booking'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Load relationships for the response
            $appointment->load(['user', 'provider', 'services']);

            return $appointment;
        });
    }

    /**
     * Get appointments for a specific user
     */
    public function getUserAppointments(int $userId, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = Appointment::with(['provider', 'services'])
            ->where('user_id', $userId);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['from_date'])) {
            $query->where('start_time', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->where('start_time', '<=', $filters['to_date']);
        }

        return $query->orderBy('start_time', 'desc')->get();
    }

    /**
     * Cancel an appointment
     */
    public function cancelAppointment(Appointment $appointment, int $cancelledBy, string $reason = null): Appointment
    {
        $appointment->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $cancelledBy,
            'cancellation_reason' => $reason,
        ]);

        return $appointment->fresh(['user', 'provider', 'services']);
    }

    /**
     * Get all appointments (for admin use)
     */
    public function getAllAppointments(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = Appointment::with(['user', 'provider', 'services']);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }

        if (isset($filters['from_date'])) {
            $query->where('start_time', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->where('start_time', '<=', $filters['to_date']);
        }

        return $query->orderBy('start_time', 'desc')->get();
    }

    /**
     * Check if appointment time slot is available
     */
    public function isTimeSlotAvailable(int $providerId, string $startTime, string $endTime, int $excludeAppointmentId = null): bool
    {
        $query = Appointment::where('provider_id', $providerId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q2) use ($startTime, $endTime) {
                        $q2->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            });

        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        return $query->count() === 0;
    }

    /**
     * Check if appointment time is within provider's operating hours
     */
    public function isWithinOperatingHours(int $providerId, string $startTime, string $endTime): bool
    {
        $provider = Provider::with('operatingHours')->findOrFail($providerId);

        $startDateTime = Carbon::parse($startTime);
        $endDateTime = Carbon::parse($endTime);
        $dayOfWeek = $startDateTime->dayOfWeek;

        // Get operating hours for the specific day
        $operatingHour = $provider->operatingHours->where('day_of_week', $dayOfWeek)->first();

        if (!$operatingHour || $operatingHour->is_closed || !$operatingHour->start_time || !$operatingHour->end_time) {
            return false; // Provider is closed on this day
        }

        $operatingStart = Carbon::parse($startDateTime->format('Y-m-d') . ' ' . $operatingHour->start_time);
        $operatingEnd = Carbon::parse($startDateTime->format('Y-m-d') . ' ' . $operatingHour->end_time);

        // Check if appointment is within operating hours
        return $startDateTime >= $operatingStart && $endDateTime <= $operatingEnd;
    }

    /**
     * Get available time slots for a provider on a specific date
     */
    public function getAvailableSlots(int $providerId, string $date): array
    {
        $provider = Provider::with('operatingHours')->findOrFail($providerId);
        $dayOfWeek = Carbon::parse($date)->dayOfWeek; // 0 = Sunday, 6 = Saturday

        // Get operating hours for the specific day
        $operatingHour = $provider->operatingHours->where('day_of_week', $dayOfWeek)->first();

        if (!$operatingHour || $operatingHour->is_closed || !$operatingHour->start_time || !$operatingHour->end_time) {
            return []; // Provider is closed on this day
        }

        // Get slot duration (default to 30 minutes if not set)
        $slotDuration = $provider->slot_duration_minutes ?? 30;

        // Generate all possible time slots
        $slots = $this->generateTimeSlots(
            $operatingHour->start_time,
            $operatingHour->end_time,
            $slotDuration
        );

        // Get existing appointments for this date
        $existingAppointments = Appointment::where('provider_id', $providerId)
            ->whereDate('start_time', $date)
            ->where('status', '!=', 'cancelled')
            ->get(['start_time', 'end_time']);

        // Filter out unavailable slots
        $availableSlots = [];
        foreach ($slots as $slot) {
            $slotStart = Carbon::parse($date . ' ' . $slot['start_time']);
            $slotEnd = Carbon::parse($date . ' ' . $slot['end_time']);

            // Check if slot conflicts with existing appointments
            $isAvailable = true;
            foreach ($existingAppointments as $appointment) {
                $appointmentStart = Carbon::parse($appointment->start_time);
                $appointmentEnd = Carbon::parse($appointment->end_time);

                // Check for overlap
                if ($slotStart < $appointmentEnd && $slotEnd > $appointmentStart) {
                    $isAvailable = false;
                    break;
                }
            }

            // Only include future slots (can't book in the past)
            if ($isAvailable && $slotStart > now()) {
                $availableSlots[] = [
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                    'formatted_time' => $slotStart->format('g:i A'), // e.g., "9:00 AM"
                    'datetime' => $slotStart->toISOString(),
                ];
            }
        }

        return $availableSlots;
    }

    /**
     * Generate time slots between start and end time
     */
    private function generateTimeSlots(string $startTime, string $endTime, int $slotDuration): array
    {
        $slots = [];
        $current = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        while ($current->addMinutes($slotDuration) <= $end) {
            $slotStart = $current->copy()->subMinutes($slotDuration);
            $slotEnd = $current->copy();

            $slots[] = [
                'start_time' => $slotStart->format('H:i:s'),
                'end_time' => $slotEnd->format('H:i:s'),
            ];
        }

        return $slots;
    }

    /**
     * Get available slots for multiple days (for calendar view)
     */
    public function getAvailableSlotsForDateRange(int $providerId, string $startDate, string $endDate): array
    {
        $result = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current <= $end) {
            $dateString = $current->format('Y-m-d');
            $slots = $this->getAvailableSlots($providerId, $dateString);

            if (!empty($slots)) {
                $result[$dateString] = [
                    'date' => $dateString,
                    'day_name' => $current->format('l'), // e.g., "Monday"
                    'formatted_date' => $current->format('F j, Y'), // e.g., "May 1, 2024"
                    'slots' => $slots,
                    'total_slots' => count($slots),
                ];
            }

            $current->addDay();
        }

        return $result;
    }

    /**
     * Generate a unique appointment number
     */
    private function generateUniqueAppointmentNumber(): string
    {
        do {
            // Generate appointment number format: APT-YYYYMMDD-XXXX
            // Where XXXX is a random 4-digit number
            $date = Carbon::now()->format('Ymd');
            $randomNumber = str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
            $appointmentNumber = "APT-{$date}-{$randomNumber}";

            // Check if this number already exists
            $exists = Appointment::where('appointment_number', $appointmentNumber)->exists();
        } while ($exists);

        return $appointmentNumber;
    }

    /**
     * Get a paginated and filtered list of appointments for a specific provider.
     *
     * @param Provider $provider
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAppointmentsForProvider(Provider $provider, array $filters = []): LengthAwarePaginator
    {
        $query = $provider->appointments()
            ->with(['user', 'services']) // Eager load relationships
            ->latest('start_time'); // Default sort order

        // --- Apply Status Filters ---
        $query->when($filters['status'] ?? null, function ($query, $status) {
            // Special filter for "Today"
            if ($status === 'today') {
                return $query->whereDate('start_time', today());
            }
            // Special filter for "Upcoming"
            if ($status === 'upcoming') {
                return $query->where('start_time', '>=', now());
            }
            // Special filter for "History" (completed or no_show)
            if ($status === 'history') {
                return $query->whereIn('status', ['completed', 'no_show']);
            }
            // Standard status filter (pending, confirmed, cancelled)
            return $query->where('status', $status);
        });

        // --- Apply Date Filter ---
        $query->when($filters['date'] ?? null, function ($query, $date) {
            return $query->whereDate('start_time', $date);
        });

        // --- Apply Search Term Filter ---
        $query->when($filters['search'] ?? null, function ($query, $searchTerm) {
            return $query->where(function ($q) use ($searchTerm) {
                // Search in appointment number
                $q->where('appointment_number', 'like', "%{$searchTerm}%")
                    // Search in the patient's name
                    ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                        $userQuery->where('name', 'like', "%{$searchTerm}%");
                    })
                    // Search in the booked services' names
                    ->orWhereHas('services', function ($serviceQuery) use ($searchTerm) {
                        $serviceQuery->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        });

        // --- Pagination ---
        // Get the requested number of items per page, defaulting to 25
        $perPage = $filters['per_page'] ?? 25;

        return $query->paginate($perPage);
    }
}
