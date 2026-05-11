<?php

namespace App\Http\Requests\Transactions;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get validation rules
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:income,expense,liability',
            'amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'description' => 'required|string',
            'remarks' => 'nullable|string',
        ];
    }
}
