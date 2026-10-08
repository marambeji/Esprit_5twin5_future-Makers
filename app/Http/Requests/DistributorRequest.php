<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DistributorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $ignore = $this->route('distributor');

        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('distributors')->ignore($ignore)],
            'email' => ['required', 'email', 'max:150', Rule::unique('distributors')->ignore($ignore)],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+().\s-]{8,30}$/'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nom', 'email' => 'adresse e-mail', 'phone' => 'téléphone', 'city' => 'ville', 'address' => 'adresse'];
    }

    public function messages(): array
    {
        return ['name.unique' => 'Ce distributeur existe déjà.', 'email.unique' => 'Cette adresse e-mail est déjà utilisée.', 'phone.regex' => 'Le numéro de téléphone est invalide.'];
    }
}
