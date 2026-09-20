<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWeeklyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'week_number' => ['required', 'integer', 'min:1'],
            'tasks_completed' => ['required', 'string'],
            'learnings' => ['nullable', 'string'],
            'blockers' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:50'],
            'feedback' => ['nullable', 'string'],
        ];
    }
}
