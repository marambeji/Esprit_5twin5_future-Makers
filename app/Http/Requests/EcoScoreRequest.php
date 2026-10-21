<?php

namespace App\Http\Requests;

use App\Models\Transformation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Données minimales pour demander une estimation IA depuis le formulaire (requête AJAX). */
class EcoScoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'        => ['required', 'integer', 'exists:products,id'],
            'procede'           => ['required', Rule::in(array_keys(Transformation::PROCEDES))],
            'quantite_kg'       => ['required', 'numeric', 'gt:0', 'max:9999999.99'],
            'transformateur_id' => ['nullable', 'integer', 'exists:transformateurs,id'],
            'ferme_id'          => ['nullable', 'integer', 'exists:fermes,id'],
            'allegation'        => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required'  => 'Choisissez d’abord le produit.',
            'procede.required'     => 'Choisissez d’abord le procédé.',
            'quantite_kg.required' => 'Indiquez d’abord la quantité transformée.',
            'quantite_kg.gt'       => 'La quantité doit être strictement positive.',
        ];
    }
}
