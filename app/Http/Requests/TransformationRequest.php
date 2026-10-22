<?php

namespace App\Http\Requests;

use App\Models\Transformation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransformationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $mesure = ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'];

        return [
            'transformateur_id'   => ['required', 'integer', 'exists:transformateurs,id'],
            'product_id'          => ['required', 'integer', 'exists:products,id'],
            'ferme_id'            => ['nullable', 'integer', 'exists:fermes,id'],
            'procede'             => ['required', Rule::in(array_keys(Transformation::PROCEDES))],
            'date_transformation' => ['required', 'date', 'after_or_equal:2000-01-01', 'before_or_equal:today'],
            'quantite_kg'         => ['required', 'numeric', 'gt:0', 'max:9999999.99', 'decimal:0,2'],
            'co2_kg'              => $mesure,
            'eau_litres'          => $mesure,
            'energie_kwh'         => $mesure,
            'allegation'          => ['nullable', 'string', 'max:255'],
            'verdict_allegation'  => ['nullable', Rule::in(array_keys(Transformation::VERDICTS))],
            'analyse_ia'          => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** Un verdict n'a de sens que s'il y a une allégation. */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        if ($key === null && blank($data['allegation'] ?? null)) {
            $data['verdict_allegation'] = null;
        }

        return $data;
    }

    public function messages(): array
    {
        return [
            'transformateur_id.required'         => 'Le transformateur est obligatoire.',
            'transformateur_id.exists'           => 'Veuillez sélectionner un transformateur existant.',
            'product_id.required'                => 'Le produit est obligatoire.',
            'product_id.exists'                  => 'Veuillez sélectionner un produit existant.',
            'ferme_id.exists'                    => 'Veuillez sélectionner une ferme existante.',
            'procede.required'                   => 'Le procédé est obligatoire.',
            'procede.in'                         => 'Veuillez choisir un procédé de la liste.',
            'date_transformation.required'       => 'La date de transformation est obligatoire.',
            'date_transformation.date'           => 'La date n’est pas valide.',
            'date_transformation.before_or_equal' => 'La date de transformation ne peut pas être dans le futur.',
            'date_transformation.after_or_equal' => 'La date de transformation doit être postérieure au 01/01/2000.',
            'quantite_kg.required'               => 'La quantité transformée est obligatoire.',
            'quantite_kg.gt'                     => 'La quantité doit être strictement positive.',
            'quantite_kg.numeric'                => 'La quantité doit être un nombre.',
            'quantite_kg.decimal'                => 'La quantité accepte au maximum deux décimales.',
            '*.numeric'                          => 'Cette valeur doit être un nombre.',
            '*.min'                              => 'Cette valeur doit être positive ou nulle.',
            '*.decimal'                          => 'Deux décimales au maximum.',
            'allegation.max'                     => 'L’allégation ne peut pas dépasser 255 caractères.',
            'verdict_allegation.in'              => 'Verdict invalide.',
            'analyse_ia.max'                     => 'L’analyse ne peut pas dépasser 2000 caractères.',
        ];
    }
}
