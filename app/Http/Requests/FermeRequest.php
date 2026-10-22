<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FermeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'producteur_id' => ['required', 'integer', 'exists:producteurs,id'],
            'nom'           => ['required', 'string', 'max:150'],
            'localisation'  => ['required', 'string', 'max:255'],
            'superficie'    => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'description'   => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'producteur_id.required' => 'Le producteur est obligatoire.',
            'producteur_id.exists'   => 'Veuillez sélectionner un producteur existant.',
            'nom.required'           => 'Le nom de la ferme est obligatoire.',
            'nom.max'                => 'Le nom ne peut pas dépasser 150 caractères.',
            'localisation.required'  => 'La localisation est obligatoire.',
            'localisation.max'       => 'La localisation ne peut pas dépasser 255 caractères.',
            'superficie.numeric'     => 'La superficie doit être un nombre.',
            'superficie.min'         => 'La superficie doit être positive ou nulle.',
            'superficie.decimal'     => 'La superficie accepte au maximum deux décimales.',
            'description.max'        => 'La description ne peut pas dépasser 2000 caractères.',
        ];
    }
}
