<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->route('user'))],
            'password' => [$this->isMethod('POST') ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'user'])],
        ];
    }

    public function messages(): array
    {
        return ['required' => 'Ce champ est obligatoire.', 'email.email' => 'Adresse e-mail invalide.', 'email.unique' => 'Cette adresse e-mail est déjà utilisée.', 'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.', 'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.', 'role.in' => 'Rôle invalide.'];
    }
}
