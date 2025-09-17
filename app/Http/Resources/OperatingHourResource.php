<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperatingHourResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array
    {
        return [
            "provider_id" => $this->provider_id,
            "day_of_week" => $this->day_of_week,
            "start_time" => $this->start_time,
            "end_time" => $this->end_time,
            "is_closed" => $this->is_closed,
        ];
    }
}
