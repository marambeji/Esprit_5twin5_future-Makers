<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LabelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $labelId = $this->route('label') ? $this->route('label')->id : null;

        return [
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('labels', 'nom')->ignore($labelId),
            ],
            'organisme' => [
                'nullable',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom du label est obligatoire.',
            'nom.unique' => 'Ce nom de label existe déjà.',
            'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'organisme.max' => 'L\'organisme ne peut pas dépasser 255 caractères.',
            'description.max' => 'La description ne peut pas dépasser 2000 caractères.',
        ];
    }
}
