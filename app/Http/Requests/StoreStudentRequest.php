<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'internship_id' => ['required', 'exists:internships,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:students,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', 'max:50'],
            'overall_progress' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }
}
