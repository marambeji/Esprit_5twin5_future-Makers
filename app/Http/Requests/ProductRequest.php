<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'origin' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.image' => 'Le fichier doit être une image.',
            'image.mimes' => 'Formats acceptés : JPG, PNG et WebP.',
            'image.max' => 'L’image ne doit pas dépasser 2 Mo.',
            '*.required' => 'Ce champ est obligatoire.',
            '*.max' => 'La valeur dépasse la limite autorisée.',
            'price.numeric' => 'Le prix doit être un nombre.',
            'price.min' => 'Le prix doit être positif ou nul.',
            'price.decimal' => 'Le prix accepte au maximum deux décimales.',
            'category_id.exists' => 'Veuillez sélectionner une catégorie existante.',
            'category_id.integer' => 'La catégorie sélectionnée est invalide.',
        ];
    }
}
