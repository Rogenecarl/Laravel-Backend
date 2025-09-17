<?php

namespace App\Http\Requests;

use App\Enums\DocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProviderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Decode JSON strings from frontend to arrays and set default values before validation.
     */
    protected function prepareForValidation()
    {
        // Convert string values to appropriate types
        if ($this->has('category_id') && is_string($this->category_id)) {
            $this->merge([
                'category_id' => (int) $this->category_id
            ]);
        }

        if ($this->has('latitude') && is_string($this->latitude)) {
            $this->merge([
                'latitude' => (float) $this->latitude
            ]);
        }

        if ($this->has('longitude') && is_string($this->longitude)) {
            $this->merge([
                'longitude' => (float) $this->longitude
            ]);
        }

        // Decode JSON strings for services, operating_hours, and documents
        if ($this->has('services') && is_string($this->services)) {
            $this->merge([
                'services' => json_decode($this->services, true)
            ]);
        }

        if ($this->has('operating_hours') && is_string($this->operating_hours)) {
            $this->merge([
                'operating_hours' => json_decode($this->operating_hours, true)
            ]);
        }

        if ($this->has('documents') && is_string($this->documents)) {
            $this->merge([
                'documents' => json_decode($this->documents, true)
            ]);
        }

        // Set default status if not provided
        if (!$this->has('status')) {
            $this->merge([
                'status' => 'pending'
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "user_id" => "nullable|integer|exists:users,id",
            "category_id" => "required|integer|exists:categories,id",
            "verified_by" => "nullable|integer|exists:users,id",
            "healthcare_name" => "required|string|max:255",
            "description" => "required|string|max:1000",
            "phone_number" => "required|string|max:20",
            "email" => "required|string|email|max:255|unique:providers,email",
            "status" => "in:verified,pending",
            "cover_photo" => "nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048",
            "address" => "required|string|max:255",
            "city" => "required|string|max:100",
            "province" => "required|string|max:100",
            "latitude" => "required|numeric",
            "longitude" => "required|numeric",
            "verified_at" => "nullable|date",
            // Services validation
            "services" => "array",
            "services.*.name" => "required|string|max:255",
            "services.*.description" => "nullable|string",
            "services.*.price_min" => "required|integer|min:0",
            "services.*.price_max" => "required|integer|min:0|gte:services.*.price_min",
            "services.*.is_active" => "nullable|boolean",
            "services.*.sort_order" => "nullable|integer|min:0",
            // Operating hours validation
            "operating_hours" => "nullable|array",
            "operating_hours.*.day_of_week" => "required|integer|between:0,6",
            "operating_hours.*.start_time" => "nullable|date_format:H:i",
            "operating_hours.*.end_time" => "nullable|date_format:H:i",
            "operating_hours.*.is_closed" => "nullable|boolean",
            // Documents validation (files will be handled separately in the frontend)
            "documents" => "nullable|array",
            "documents.*.document_type" => ["required", Rule::enum(DocumentType::class)],
            "documents.*.file_path" => "required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048", // Only accepting image formats
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($this->input('operating_hours', []) as $index => $hours) {
                $start = $hours['start_time'] ?? null;
                $end = $hours['end_time'] ?? null;
                $isClosed = $hours['is_closed'] ?? false;

                if (!$isClosed && $start && $end && $end <= $start) {
                    $validator->errors()->add(
                        "operating_hours.$index.end_time",
                        "End time must be after start time."
                    );
                }
            }
        });
    }
}
