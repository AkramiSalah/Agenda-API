<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTimeslotRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'agenda' => 'required|string',
            'tag' => 'string|nullable',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ];
    }

    public function messages()
    {
        return [
            'agenda.required' => 'An agenda is required.',
            'end_time.after' => 'End time must be after start time.',
        ];
    }
}
