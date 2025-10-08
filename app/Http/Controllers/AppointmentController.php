<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Provider;
use App\Services\AppointmentService;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{

    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // This could be used for admin to view all appointments
        $appointments = $this->appointmentService->getAllAppointments();

        return AppointmentResource::collection($appointments);
    }

    /**
     * Get appointments for the authenticated user
     */
    public function indexForUser(Request $request)
    {
        $filters = $request->only(['status', 'from_date', 'to_date']);
        $appointments = $this->appointmentService->getUserAppointments(
            $request->user()->id,
            $filters
        );

        return AppointmentResource::collection($appointments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAppointmentRequest $request)
    {
        // Get the validated data from the form request
        $appointmentData = $request->validated();

        // Add the authenticated user's ID
        $appointmentData['user_id'] = $request->user()->id;

        // Check if the time slot is available
        $isAvailable = $this->appointmentService->isTimeSlotAvailable(
            $appointmentData['provider_id'],
            $appointmentData['start_time'],
            $appointmentData['end_time']
        );

        if (!$isAvailable) {
            return response()->json([
                'message' => 'The selected time slot is not available',
                'errors' => [
                    'start_time' => ['This time slot conflicts with an existing appointment']
                ]
            ], 422);
        }

        // Check if the time slot is within operating hours
        $isWithinOperatingHours = $this->appointmentService->isWithinOperatingHours(
            $appointmentData['provider_id'],
            $appointmentData['start_time'],
            $appointmentData['end_time']
        );

        if (!$isWithinOperatingHours) {
            return response()->json([
                'message' => 'The selected time slot is outside operating hours',
                'errors' => [
                    'start_time' => ['This time slot is outside the provider\'s operating hours']
                ]
            ], 422);
        }

        try {
            $appointment = $this->appointmentService->createAppointment($appointmentData);

            return response()->json([
                'appointment' => new AppointmentResource($appointment),
                'message' => 'Appointment created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $appointment = Appointment::with(['user', 'provider', 'services'])->findOrFail($id);

        return new AppointmentResource($appointment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Cancel an appointment
     */
    public function cancel(Request $request, Appointment $appointment)
    {
        // Ensure the user can only cancel their own appointments
        if ($appointment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if appointment can be cancelled
        if ($appointment->status === 'cancelled') {
            return response()->json(['message' => 'Appointment is already cancelled'], 400);
        }

        if ($appointment->status === 'completed') {
            return response()->json(['message' => 'Cannot cancel completed appointment'], 400);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        $cancelledAppointment = $this->appointmentService->cancelAppointment(
            $appointment,
            $request->user()->id,
            $request->input('reason')
        );

        return response()->json([
            'appointment' => new AppointmentResource($cancelledAppointment),
            'message' => 'Appointment cancelled successfully',
        ]);
    }

    /**
     * Get available time slots for a provider on a specific date
     */
    public function getAvailableSlots(Request $request, int $providerId)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        try {
            $slots = $this->appointmentService->getAvailableSlots(
                $providerId,
                $request->input('date')
            );

            return response()->json([
                'provider_id' => $providerId,
                'date' => $request->input('date'),
                'available_slots' => $slots,
                'total_slots' => count($slots),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch available slots',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available slots for a date range (for calendar view)
     */
    public function getAvailableSlotsForRange(Request $request, int $providerId)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $slots = $this->appointmentService->getAvailableSlotsForDateRange(
                $providerId,
                $request->input('start_date'),
                $request->input('end_date')
            );

            return response()->json([
                'provider_id' => $providerId,
                'date_range' => [
                    'start_date' => $request->input('start_date'),
                    'end_date' => $request->input('end_date'),
                ],
                'available_slots_by_date' => $slots,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch available slots',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get provider info with operating hours and slot duration
     */
    public function getProviderScheduleInfo(int $providerId)
    {
        try {
            $provider = Provider::with('operatingHours')
                ->select('id', 'healthcare_name', 'slot_duration_minutes')
                ->findOrFail($providerId);

            return response()->json([
                'provider' => [
                    'id' => $provider->id,
                    'name' => $provider->healthcare_name,
                    'slot_duration_minutes' => $provider->slot_duration_minutes ?? 30,
                ],
                'operating_hours' => $provider->operatingHours->map(function ($hour) {
                    return [
                        'day_of_week' => $hour->day_of_week,
                        'day_name' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$hour->day_of_week],
                        'start_time' => $hour->start_time,
                        'end_time' => $hour->end_time,
                        'is_closed' => $hour->is_closed,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Provider not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Display a listing of the appointments for the authenticated provider.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function indexForProvider(Request $request)
    {
        // Get the authenticated user's provider profile
        $provider = $request->user()->provider;

        if (!$provider) {
            return response()->json(['message' => 'Provider profile not found'], 404);
        }

        // Pass all query parameters from the URL to the service
        $appointments = $this->appointmentService->getAppointmentsForProvider($provider, $request->all());

        return AppointmentResource::collection($appointments);
    }
}
