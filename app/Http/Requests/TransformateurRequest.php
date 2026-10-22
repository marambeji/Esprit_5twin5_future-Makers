<?php

namespace App\Http\Requests;

use App\Models\Transformateur;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransformateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nom'       => is_string($this->nom) ? trim($this->nom) : $this->nom,
            'email'     => is_string($this->email) ? mb_strtolower(trim($this->email)) : $this->email,
            'telephone' => is_string($this->telephone) ? preg_replace('/\s+/', ' ', trim($this->telephone)) : $this->telephone,
        ]);
    }

    public function rules(): array
    {
        return [
            'nom'           => ['required', 'string', 'min:2', 'max:150'],
            'type_activite' => ['required', Rule::in(array_keys(Transformateur::TYPES))],
            'email'         => ['required', 'email', 'max:255', Rule::unique('transformateurs', 'email')->ignore($this->route('transformateur'))],
            'telephone'     => ['required', 'string', 'regex:/^\+?[0-9 ]{8,20}$/'],
            'ville'         => ['required', 'string', 'max:100'],
            'adresse'       => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required'           => 'Le nom du transformateur est obligatoire.',
            'nom.min'                => 'Le nom doit contenir au moins 2 caractères.',
            'nom.max'                => 'Le nom ne peut pas dépasser 150 caractères.',
            'type_activite.required' => 'Le type d’activité est obligatoire.',
            'type_activite.in'       => 'Veuillez choisir un type d’activité de la liste.',
            'email.required'         => 'L’adresse e-mail est obligatoire.',
            'email.email'            => 'L’adresse e-mail n’est pas valide.',
            'email.unique'           => 'Un transformateur utilise déjà cette adresse e-mail.',
            'telephone.required'     => 'Le téléphone est obligatoire.',
            'telephone.regex'        => 'Le téléphone doit contenir 8 à 20 chiffres (indicatif + autorisé).',
            'ville.required'         => 'La ville est obligatoire.',
            'adresse.required'       => 'L’adresse est obligatoire.',
            'adresse.max'            => 'L’adresse ne peut pas dépasser 255 caractères.',
            'description.max'        => 'La description ne peut pas dépasser 2000 caractères.',
        ];
    }
}
