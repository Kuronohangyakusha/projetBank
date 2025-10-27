<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // À adapter selon vos besoins d'autorisation
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => 'required|uuid|exists:clients,id',
            'numeroCompte' => 'nullable|string|unique:comptes,numeroCompte|regex:/^CP\d{8}$/',
            'type' => 'required|in:epargne,cheque',
            'solde' => 'numeric|min:0|max:999999999.99',
            'devise' => 'string|in:FCFA,EUR,USD',
            'statut' => 'in:actif,bloque',
            'metadata' => 'nullable|array',
            'metadata.dateOuverture' => 'nullable|date',
            'metadata.agence' => 'nullable|string|max:255',
            'metadata.fraisOuverture' => 'nullable|numeric|min:0',
            'metadata.plafondRetrait' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'numeroCompte.regex' => 'Le numéro de compte doit commencer par CP suivi de 8 chiffres.',
            'numeroCompte.unique' => 'Ce numéro de compte est déjà utilisé.',
            'type.in' => 'Le type doit être soit épargne soit chèque.',
            'devise.in' => 'La devise doit être FCFA, EUR ou USD.',
            'statut.in' => 'Le statut doit être actif ou bloque.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'numeroCompte' => 'numéro de compte',
            'type' => 'type de compte',
            'solde' => 'solde initial',
            'devise' => 'devise',
            'statut' => 'statut du compte',
        ];
    }
}
