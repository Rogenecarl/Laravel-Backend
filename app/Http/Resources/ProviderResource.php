<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
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
            'user_id' => $this->user_id,
            'category_id' => $this->category_id,
            'verified_by' => $this->verified_by,
            'healthcare_name' => $this->healthcare_name,
            'description' => $this->description,
            'phone_number' => $this->phone_number,
            'cover_photo' => $this->cover_photo,
            'email' => $this->email,
            'status' => $this->status,
            'address' => $this->address,
            'city' => $this->city,
            'province' => $this->province,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'verified_at' => $this->verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'services' => $this->when($this->relationLoaded('services'), function () {
                return ServiceResource::collection($this->services);
            }),


        ];
    }
}
