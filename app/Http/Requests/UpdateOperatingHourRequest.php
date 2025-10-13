<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOperatingHourRequest extends FormRequest
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
            'operating_hours' => 'required|array',
            'operating_hours.*.day_of_week' => 'required|integer|between:0,6',
            'operating_hours.*.start_time' => 'nullable|date_format:H:i',
            'operating_hours.*.end_time' => 'nullable|date_format:H:i',
            'operating_hours.*.is_closed' => 'required|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'operating_hours.required' => 'Operating hours data is required.',
            'operating_hours.array' => 'Operating hours must be an array.',
            'operating_hours.*.day_of_week.required' => 'Day of week is required.',
            'operating_hours.*.day_of_week.integer' => 'Day of week must be an integer.',
            'operating_hours.*.day_of_week.between' => 'Day of week must be between 0 and 6.',
            'operating_hours.*.start_time.date_format' => 'Start time must be in H:i format (e.g., 09:00).',
            'operating_hours.*.end_time.date_format' => 'End time must be in H:i format (e.g., 17:00).',
            'operating_hours.*.is_closed.required' => 'Closed status is required.',
            'operating_hours.*.is_closed.boolean' => 'Closed status must be true or false.',
        ];
    }
}