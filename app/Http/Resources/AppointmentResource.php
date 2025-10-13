<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'appointment_number' => $this->appointment_number,
            'start_time' => $this->start_time?->toISOString(),
            'end_time' => $this->end_time?->toISOString(),
            'formatted_start_time' => $this->start_time?->format('g:i A'), // 2:30 PM
            'formatted_end_time' => $this->end_time?->format('g:i A'), // 3:30 PM
            'formatted_date' => $this->start_time?->format('M j, Y'), // Jan 15, 2024
            'status' => $this->status,
            'notes' => $this->notes,
            'total_price' => $this->total_price,
            'cancelled_at' => $this->cancelled_at,
            'cancellation_reason' => $this->cancellation_reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Include related data
            'user' => new UserResource($this->whenLoaded('user')),
            'provider' => $this->whenLoaded('provider', function () {
                return [
                    'id' => $this->provider->id,
                    'name' => $this->provider->healthcare_name,
                    'email' => $this->provider->email,
                    'cover_photo' => $this->provider->cover_photo ? asset('storage/' . $this->provider->cover_photo) : null,
                    // Add other provider fields as needed
                ];
            }),
            'services' => ServiceResource::collection($this->whenLoaded('services')),
        ];
    }
}
