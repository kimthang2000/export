<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:csv,xlsx|max:102400',
            'options' => 'nullable|json',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please upload a file to import.',
            'file.mimes' => 'Only CSV and XLSX files are supported.',
            'file.max' => 'File size must not exceed 100MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('options') && is_string($this->input('options'))) {
            $this->merge([
                'options' => json_decode($this->input('options'), true),
            ]);
        }
    }
}
