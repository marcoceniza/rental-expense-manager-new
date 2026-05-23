<?php

namespace App\Http\Requests\Transactions;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare data for validation (fix booleans)
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_tuition' => $this->boolean('is_tuition'),
            'is_other' => $this->boolean('is_other'),
        ]);
    }

    /**
     * Get validation rules
     */
    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'is_tuition' => ['nullable', 'boolean'],
            'is_other' => ['nullable', 'boolean'],
            'type' => [
                'sometimes',
                'required',
                'in:income,expense,liability',
                function ($attribute, $value, $fail) use ($category) {
                    if (
                        $category &&
                        $category->transactions()->exists() &&
                        $value !== $category->type
                    ) {
                        $fail('Cannot change type of category with transactions.');
                    }
                },
            ],
        ];
    }
}
