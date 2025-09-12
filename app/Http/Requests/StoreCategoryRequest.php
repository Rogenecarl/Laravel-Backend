<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
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
            "slug" => "required|string|max:255|unique:categories,slug",
            "description" => "nullable|string|max:1000",
            "icon" => "nullable|string|max:255",
            "color" => "nullable|string|max:7",
            "is_active" => "required|boolean",
            "sort_order" => "nullable|integer",
        ];
    }
}
