<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
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
            'provider_id' => 'required|exists:providers,id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'notes' => 'nullable|string|max:1000',
            'services' => 'required|array|min:1',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.price_at_booking' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'start_time.after' => 'Appointment must be scheduled for a future date and time.',
            'end_time.after' => 'End time must be after the start time.',
            'services.required' => 'At least one service must be selected.',
            'services.min' => 'At least one service must be selected.',
            'services.*.service_id.exists' => 'One or more selected services do not exist.',
            'services.*.price_at_booking.min' => 'Service price must be greater than or equal to 0.',
        ];
    }
}
