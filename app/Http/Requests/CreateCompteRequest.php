<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Admin peut créer des comptes
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(['cheque', 'epargne'])],
            'soldeInitial' => ['required', 'numeric', 'min:10000'],
            'devise' => ['required', 'string', 'in:FCFA,XOF,EUR,USD'],
            'client' => ['required', 'array'],
            'client.id' => ['nullable', 'uuid', 'exists:clients,id'],
            'client.titulaire' => ['required_if:client.id,null', 'string', 'max:255'],
            'client.email' => ['required_if:client.id,null', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->input('client.id'))],
            'client.telephone' => ['required_if:client.id,null', 'string', 'regex:/^[7][0-9]{8}$/', 'unique:clients,telephone'],
            'client.adresse' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Le type de compte est obligatoire',
            'type.in' => 'Le type doit être cheque ou epargne',
            'soldeInitial.required' => 'Le solde initial est obligatoire',
            'soldeInitial.numeric' => 'Le solde initial doit être un nombre',
            'soldeInitial.min' => 'Le solde initial doit être d\'au moins 10 000 FCFA',
            'devise.required' => 'La devise est obligatoire',
            'devise.in' => 'La devise doit être FCFA, XOF, EUR ou USD',
            'client.required' => 'Les informations du client sont obligatoires',
            'client.id.uuid' => 'L\'ID du client doit être un UUID valide',
            'client.id.exists' => 'Le client spécifié n\'existe pas',
            'client.titulaire.required_if' => 'Le nom du titulaire est requis pour un nouveau client',
            'client.email.required_if' => 'L\'email est requis pour un nouveau client',
            'client.email.email' => 'L\'email doit être valide',
            'client.email.unique' => 'Cet email est déjà utilisé',
            'client.telephone.required_if' => 'Le téléphone est requis pour un nouveau client',
            'client.telephone.regex' => 'Le numéro de téléphone doit être un numéro sénégalais valide (+221XXXXXXXXX)',
            'client.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé',
            'client.adresse.max' => 'L\'adresse ne peut pas dépasser 500 caractères',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'type' => 'type de compte',
            'soldeInitial' => 'solde initial',
            'devise' => 'devise',
            'client.id' => 'ID du client',
            'client.titulaire' => 'nom du titulaire',
            'client.email' => 'email',
            'client.telephone' => 'téléphone',
            'client.adresse' => 'adresse',
        ];
    }
}
