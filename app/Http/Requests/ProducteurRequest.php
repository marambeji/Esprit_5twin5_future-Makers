<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProducteurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom'       => ['required', 'string', 'max:100'],
            'prenom'    => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('producteurs')->ignore($this->route('producteur'))],
            'telephone' => ['nullable', 'string', 'max:30'],
            'adresse'   => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required'    => 'Le nom est obligatoire.',
            'nom.max'         => 'Le nom ne peut pas dépasser 100 caractères.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'prenom.max'      => 'Le prénom ne peut pas dépasser 100 caractères.',
            'email.required'  => 'L\'adresse e-mail est obligatoire.',
            'email.email'     => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique'    => 'Cette adresse e-mail est déjà utilisée.',
            'telephone.max'   => 'Le téléphone ne peut pas dépasser 30 caractères.',
            'adresse.max'     => 'L\'adresse ne peut pas dépasser 500 caractères.',
        ];
    }
}
