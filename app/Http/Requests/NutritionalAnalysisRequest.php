<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Validator;

class NutritionalAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->role === 'admin';
    }

    public function rules(): array
    {
        $rules = [
            'create_product' => ['sometimes', 'boolean'],
            'clear_reference' => ['sometimes', 'boolean'],
            'reference_token' => ['nullable', 'string', 'max:10000'],
            'ai_imported' => ['sometimes', 'boolean'],
            'ai_reviewed' => ['exclude_unless:ai_imported,1', 'required', 'accepted'],
            'product_id' => ['exclude_if:create_product,1', 'required', 'integer', 'exists:products,id'],
            'analysed_at' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'energy_kcal' => ['required', 'numeric', 'between:0,900', 'decimal:0,2'],
            'protein' => ['required', 'numeric', 'between:0,100', 'decimal:0,2'],
            'carbohydrates' => ['required', 'numeric', 'between:0,100', 'decimal:0,2'],
            'sugars' => ['required', 'numeric', 'between:0,100', 'decimal:0,2', 'lte:carbohydrates'],
            'fat' => ['required', 'numeric', 'between:0,100', 'decimal:0,2'],
            'salt' => ['required', 'numeric', 'between:0,100', 'decimal:0,2'],
            'source' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_demo' => ['required', 'boolean'],
        ];
        foreach ((new ProductRequest)->rules() as $field => $fieldRules) {
            if ($field !== 'image') {
                $rules['new_product.'.$field] = array_merge(['exclude_unless:create_product,1'], $fieldRules);
            }
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->boolean('clear_reference') && (! $this->boolean('ai_reviewed') || $this->filled('reference_token'))) {
                $validator->errors()->add('ai_reviewed', 'Vérifiez les valeurs de l’étiquette avant de remplacer la référence.');
            }
            if ($this->filled('reference_token')) {
                try {
                    $reference = json_decode(Crypt::decryptString($this->reference_token), true, flags: JSON_THROW_ON_ERROR);
                    if ((isset($reference['product_id']) && ($this->boolean('create_product') || $reference['product_id'] !== (int) $this->product_id)) || ($reference['admin_id'] ?? null) !== $this->user('admin')->id || ($reference['expires'] ?? 0) < now()->timestamp) {
                        throw new \RuntimeException;
                    }
                    if (! $this->boolean('ai_reviewed')) {
                        $validator->errors()->add('ai_reviewed', 'Confirmez la vérification des valeurs de référence.');
                    }
                } catch (\Exception $e) {
                    $validator->errors()->add('reference_token', 'La référence a expiré ou ne correspond pas au produit. Relancez la recherche.');
                }
            }
            if ($this->route('analysis')?->reference_fdc_id && ($this->boolean('create_product') || $this->route('analysis')->product_id != $this->product_id) && ! $this->filled('reference_token')) {
                $validator->errors()->add('product_id', 'Choisissez une nouvelle référence pour changer de produit.');
            }
            if (! $validator->errors()->any() && (float) $this->protein + (float) $this->carbohydrates + (float) $this->fat + (float) $this->salt > 100.01) {
                $validator->errors()->add('protein', 'La somme protéines, glucides, lipides et sel ne doit pas dépasser 100 g. Les sucres sont déjà compris dans les glucides.');
            }
        });
    }

    public function persistenceData(): array
    {
        $data = $this->safe()->except(['ai_imported', 'ai_reviewed', 'reference_token', 'clear_reference', 'create_product', 'new_product']);
        if ($this->filled('reference_token')) {
            $reference = json_decode(Crypt::decryptString($this->reference_token), true);
            $data['reference_fdc_id'] = $reference['id'];
            $data['reference_description'] = $reference['description'];
            $data['source'] = 'USDA FoodData Central — FDC '.$reference['id'].' (valeurs de référence)';
        }
        if ($this->boolean('clear_reference')) {
            $data['reference_fdc_id'] = null;
            $data['reference_description'] = null;
        }

        return $data;
    }

    public function messages(): array
    {
        return ['ai_reviewed.required' => 'Vérifiez les propositions IA et cochez la confirmation.', 'ai_reviewed.accepted' => 'Confirmez la vérification des valeurs IA.', 'required' => 'Ce champ est obligatoire.', 'numeric' => 'Saisissez une valeur numérique.', 'between' => 'La valeur doit être comprise entre :min et :max.', 'decimal' => 'Deux décimales au maximum sont autorisées.', 'product_id.exists' => 'Sélectionnez un produit existant.', 'analysed_at.date_format' => 'Saisissez une date valide.', 'analysed_at.before_or_equal' => 'La date ne peut pas être dans le futur.', 'sugars.lte' => 'Les sucres ne peuvent pas dépasser les glucides.'];
    }
}
