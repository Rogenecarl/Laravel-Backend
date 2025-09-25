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
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
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
                    // Add other provider fields as needed
                ];
            }),
            'services' => ServiceResource::collection($this->whenLoaded('services')),
        ];
    }
}
