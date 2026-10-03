<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('categories')->ignore($this->route('category'))],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return ['name.required' => 'Le nom de la catégorie est obligatoire.', 'name.unique' => 'Cette catégorie existe déjà.', 'name.max' => 'Le nom ne peut pas dépasser 100 caractères.', 'description.max' => 'La description ne peut pas dépasser 2000 caractères.'];
    }
}
