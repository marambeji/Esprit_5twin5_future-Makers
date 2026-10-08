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
        return [
            'distributor_id' => ['required', 'integer', 'exists:distributors,id'],
            'destination' => ['required', 'string', 'max:255'],
            'delivery_date' => ['required', 'date'],
            'status' => ['required', Rule::in(array_keys(Delivery::STATUSES))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return ['distributor_id' => 'distributeur', 'destination' => 'destination', 'delivery_date' => 'date de livraison', 'status' => 'statut'];
    }
}
