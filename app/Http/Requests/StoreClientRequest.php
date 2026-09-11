<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:clients,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'services' => ['nullable', 'array'],
            'services.*.id' => ['required', 'exists:services,id'],
            'services.*.requirements' => ['nullable', 'string'],
            'services.*.estimated_budget' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
