<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommandeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantite' => ['required', 'integer', 'min:1', 'max:1000'],
            'adresse_livraison' => ['required', 'string', 'max:255'],
            'telephone' => ['required', 'string', 'max:30', 'regex:/^[0-9+().\s-]{8,30}$/'],
        ];
        // Seul l'admin crée une commande pour le compte d'un client.
        if ($this->routeIs('admin.commandes.store')) {
            $rules['user_id'] = ['required', 'integer', 'exists:users,id'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return ['product_id' => 'produit', 'quantite' => 'quantité', 'adresse_livraison' => 'adresse de livraison', 'telephone' => 'téléphone', 'user_id' => 'client'];
    }

    public function messages(): array
    {
        return ['telephone.regex' => 'Le numéro de téléphone est invalide.', 'quantite.min' => 'La quantité doit être au moins 1.', 'quantite.max' => 'La quantité ne peut pas dépasser 1000.'];
    }
}
