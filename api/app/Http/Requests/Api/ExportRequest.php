<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'format' => ['required', Rule::in(['csv', 'xlsx'])],
            'columns' => 'nullable|array',
            'columns.*' => 'string',
            'filters' => 'nullable|array',
            'filters.*.field' => 'required|string',
            'filters.*.operator' => 'required|string',
            'filters.*.value' => 'present',
        ];
    }

    public function messages(): array
    {
        return [
            'format.required' => 'Export format is required (csv or xlsx).',
            'format.in' => 'Only CSV and XLSX formats are supported.',
        ];
    }
}
