<?php

namespace App\Http\Requests;

use App\Enums\ExpenseStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $expense = $this->route('expense');
        return in_array($expense->status, [ExpenseStatus::DRAFT, ExpenseStatus::REJECTED]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'amount_total' => ['sometimes', 'required', 'numeric', 'min:0'],
            'tax_rate' => ['sometimes', 'required', 'numeric', 'min:0', 'max:100'],
            'amount_taxe' => ['sometimes', 'required', 'numeric', 'min:0'],
            'expensed_at' => ['sometimes', 'required', 'date', 'before_or_equal:today'],
            'site_reference' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
