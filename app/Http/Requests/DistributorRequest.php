<?php

namespace App\Http\Requests;

use App\Models\Distributor;
use App\Models\User;
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
        /** @var Distributor|null $current */
        $current = $this->route('distributor');
        $user = User::find($this->input('user_id'));
        // Nom, e-mail et téléphone viennent du compte utilisateur ; seul un ancien distributeur sans compte peut rester sans lien.
        $legacy = $current && ! $current->user_id;
        $needsPhone = ! $user?->phone && ! $current?->phone;

        return [
            'user_id' => [$legacy ? 'nullable' : 'required', 'integer', Rule::exists('users', 'id')->where('role', 'user'), Rule::unique('distributors')->ignore($current), function ($attribute, $value, $fail) use ($user, $current) {
                if ($user && Distributor::where('id', '!=', $current?->id ?? 0)->where(fn ($q) => $q->where('name', $user->name)->orWhere('email', $user->email))->exists()) {
                    $fail('Un distributeur avec le même nom ou la même adresse e-mail existe déjà.');
                }
            }],
            'phone' => [Rule::requiredIf($user && $needsPhone), 'nullable', 'string', 'max:30', 'regex:/^[0-9+().\s-]{8,30}$/'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'actif' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['actif' => $this->boolean('actif'), 'user_id' => $this->input('user_id') ?: null]);
    }

    public function attributes(): array
    {
        return ['phone' => 'téléphone', 'city' => 'ville', 'address' => 'adresse', 'user_id' => 'compte utilisateur'];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Choisissez le compte utilisateur du distributeur.',
            'user_id.unique' => 'Ce compte est déjà lié à un autre distributeur.',
            'user_id.exists' => 'Compte utilisateur invalide.',
            'phone.required' => 'Ce compte n’a pas de téléphone : saisissez-en un.',
            'phone.regex' => 'Le numéro de téléphone est invalide.',
        ];
    }

    /** Données à enregistrer : identité reprise du compte utilisateur choisi. */
    public function distributorData(): array
    {
        $data = $this->validated();
        if ($user = User::find($data['user_id'] ?? null)) {
            $data['name'] = $user->name;
            $data['email'] = $user->email;
            $data['phone'] = ($data['phone'] ?? null) ?: $user->phone;
        } else {
            unset($data['phone']); // ancien distributeur non lié : identité inchangée
        }

        return $data;
    }
}
