<?php

namespace App\Http\Requests;

use App\Models\Delivery;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Delivery|null $delivery */
        $delivery = $this->route('delivery');
        $rules = [
            'distributor_id' => ['required', 'integer', Rule::exists('distributors', 'id')->where(fn ($q) => $q->where('actif', true)->orWhere('id', $delivery?->distributor_id))],
            'delivery_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
        if (! $delivery) {
            // Création : la commande doit être confirmée et ne pas avoir déjà de livraison.
            $rules['commande_id'] = ['required', 'integer', Rule::exists('commandes', 'id')->where('statut', 'confirmee'), 'unique:deliveries,commande_id'];
            $rules['delivery_date'][] = 'after_or_equal:today';
        } else {
            // Édition : le statut avance d'une étape ou passe à Annulée ; une livraison terminée est figée.
            $allowed = in_array($delivery->status, ['livree', 'annulee'], true) ? [$delivery->status] : array_unique([$delivery->status, ...(Delivery::TRANSITIONS[$delivery->status] ?? []), 'annulee']);
            $rules['status'] = ['required', Rule::in($allowed)];
            if (! $delivery->commande_id) {
                $rules['destination'] = ['required', 'string', 'max:255'];
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        return ['commande_id' => 'commande', 'distributor_id' => 'distributeur', 'destination' => 'destination', 'delivery_date' => 'date de livraison', 'status' => 'statut'];
    }

    public function messages(): array
    {
        return [
            'commande_id.exists' => 'Seule une commande confirmée peut être livrée.',
            'commande_id.unique' => 'Cette commande a déjà une livraison.',
            'distributor_id.exists' => 'Ce distributeur est introuvable ou désactivé.',
            'delivery_date.after_or_equal' => 'La date de livraison ne peut pas être dans le passé.',
            'status.in' => 'Changement de statut non autorisé (Planifiée → En cours → Livrée).',
        ];
    }
}
