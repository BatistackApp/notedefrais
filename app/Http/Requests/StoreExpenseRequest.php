<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole(['admin', 'salary']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],

            // Montants (on accepte le format décimal de l'UI)
            'amount_total' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'amount_taxe' => ['required', 'numeric', 'min:0'],

            // Métadonnées
            'expensed_at' => ['required', 'date', 'before_or_equal:today'],
            'site_reference' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],

            // Justificatif (requis pour passer en PENDING, mais optionnel en DRAFT)
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    /**
     * Messages personnalisés.
     */
    public function messages(): array
    {
        return [
            'expensed_at.before_or_equal' => 'La date de la dépense ne peut pas être dans le futur.',
            'category_id.exists' => 'La catégorie sélectionnée est invalide.',
            'amount_total.min' => 'Le montant total doit être positif.',
        ];
    }
}
