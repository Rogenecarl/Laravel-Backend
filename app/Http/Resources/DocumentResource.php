<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=> $this->id,
            "provider_id"=> $this->provider_id,
            "document_type"=> $this->document_type,
            "file_path"=> $this->file_path ? asset('storage/' . $this->file_path) : null,
            "status"=> $this->status,
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at,
        ];
    }
}
