<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string|ValidationRule>> */
    public function rules(): array
    {
        $service = $this->route('service');
        $serviceId = is_object($service) ? $service->id : $service;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('services', 'name')->ignore($serviceId)],
            'type' => ['required', 'string', 'in:client,internship,both'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
