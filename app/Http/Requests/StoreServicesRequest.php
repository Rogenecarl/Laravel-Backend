<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServicesRequest extends FormRequest
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
            "name" => "required|string|max:255",
            "description" => "nullable|string",
            "price_min" => "required|integer|min:0",
            "price_max" => "required|integer|min:0|gte:price_min",
            "is_active" => "boolean",
            "sort_order" => "integer|min:0",
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Service name is required.',
            'name.max' => 'Service name cannot exceed 255 characters.',
            'price_min.required' => 'Minimum price is required.',
            'price_min.min' => 'Minimum price cannot be negative.',
            'price_max.required' => 'Maximum price is required.',
            'price_max.min' => 'Maximum price cannot be negative.',
            'price_max.gte' => 'Maximum price must be greater than or equal to minimum price.',
        ];
    }
}
