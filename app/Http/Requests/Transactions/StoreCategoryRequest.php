<?php

namespace App\Http\Requests\Transactions;

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
     * Prepare the data for validation.
     * This ensures boolean fields are properly converted.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_tuition' => $this->boolean('is_tuition'),
            'is_other' => $this->boolean('is_other'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'is_tuition' => ['nullable', 'boolean'],
            'is_other' => ['nullable', 'boolean'],
            'type' => ['required', 'in:income,expense,liability'],
        ];
    }
}
