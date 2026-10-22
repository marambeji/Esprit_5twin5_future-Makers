<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Services\DeliveryReviewAnalyzer;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AvisLivraisonController extends Controller
{
    public function store(Request $request, Delivery $delivery, DeliveryReviewAnalyzer $analyzer)
    {
        abort_unless($delivery->commande && $delivery->commande->user_id === $request->user()->id, 404);
        abort_unless($delivery->status === 'livree', 403, 'La livraison doit être terminée pour laisser un avis.');
        $data = $request->validate(['note' => ['required', 'integer', 'between:1,5'], 'commentaire' => ['required', 'string', 'max:1000']]);
        if ($delivery->avis()->exists()) {
            throw ValidationException::withMessages(['avis' => 'Vous avez déjà évalué cette livraison.']);
        }
        try {
            $avis = $delivery->avis()->create($data);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['avis' => 'Vous avez déjà évalué cette livraison.']);
        }
        $avis->update($analyzer->analyze($data['commentaire']));

        return to_route('commandes.show', $delivery->commande)->with('success', 'Merci ! Votre avis a été enregistré.');
    }
}
