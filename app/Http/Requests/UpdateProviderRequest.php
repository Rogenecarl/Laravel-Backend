<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
            "category_id" => "nullable|integer|exists:categories,id",
            "verified_by" => "nullable|integer|exists:users,id",
            "healthcare_name" => "required|string|max:255",
            "description" => "required|string|max:1000",
            "phone_number" => "required|string|max:20",
            "email" => ["sometimes", "required", "string", "email", "max:255", Rule::unique("providers", "email")->ignore($this->route('provider'))],
            "status" => "required|in:verified,pending",
            "cover_photo" => "nullable|string|max:255",
            "address" => "required|string|max:255",
            "city" => "required|string|max:100",
            "province" => "required|string|max:100",
            "latitude" => "required|numeric",
            "longitude" => "required|numeric",
            "verified_at" => "nullable|date",
        ];
    }
}
