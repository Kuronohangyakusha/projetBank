<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Admin peut modifier les informations client
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $compteId = $this->route('compteId');
        $compte = \App\Models\Compte::find($compteId);
        $client = $compte ? $compte->client : null;
        $user = $client ? $client->user : null;

        return [
            'informationsClient' => ['required', 'array'],
            'informationsClient.telephone' => [
                'nullable',
                'string',
                'regex:/^[7][0-9]{8}$/',
                Rule::unique('clients', 'telephone')->ignore($client?->id)
            ],
            'informationsClient.email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id)
            ],
            'informationsClient.password' => [
                'nullable',
                'string',
                'min:10',
                'regex:/^(?=.*[A-Z])(?=.*[a-z].*[a-z])(?=.*[!@#$%^&*(),.?":{}|<>]).{10,}$/'
            ],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->input('informationsClient', []);

            // Vérifier qu'au moins un champ est fourni
            $filledFields = array_filter($data, function ($value) {
                return !is_null($value) && $value !== '';
            });

            if (empty($filledFields)) {
                $validator->errors()->add('informationsClient', 'Au moins un champ d\'information client doit être fourni pour la modification.');
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'informationsClient.required' => 'Les informations du client sont obligatoires',
            'informationsClient.array' => 'Les informations du client doivent être un objet',
            'informationsClient.telephone.regex' => 'Le numéro de téléphone doit être un numéro sénégalais valide (771234567)',
            'informationsClient.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé par un autre client',
            'informationsClient.email.email' => 'L\'email doit être valide',
            'informationsClient.email.unique' => 'Cet email est déjà utilisé par un autre utilisateur',
            'informationsClient.email.max' => 'L\'email ne peut pas dépasser 255 caractères',
            'informationsClient.password.min' => 'Le mot de passe doit contenir au moins 10 caractères',
            'informationsClient.password.regex' => 'Le mot de passe doit commencer par une majuscule, contenir au moins 2 minuscules et 2 caractères spéciaux',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'informationsClient.telephone' => 'téléphone',
            'informationsClient.email' => 'email',
            'informationsClient.password' => 'mot de passe',
        ];
    }
}
