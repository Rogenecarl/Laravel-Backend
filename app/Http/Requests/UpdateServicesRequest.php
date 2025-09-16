<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServicesRequest extends FormRequest
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
            "provider_id" => "required|integer|exists:providers,id",
            "name" => "required|string|max:255",
            "description" => "nullable|string",
            "price_min" => "required|integer|min:0",
            "price_max" => "required|integer|min:0|gte:price_min",
            "is_active" => "boolean",
            "sort_order" => "integer|min:0",
        ];
    }
}
