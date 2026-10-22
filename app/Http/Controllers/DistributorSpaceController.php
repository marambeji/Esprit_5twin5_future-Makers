<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use Illuminate\Http\Request;

// Espace distributeur : accès limité aux livraisons qui lui sont affectées.
class DistributorSpaceController extends Controller
{
    public function index(Request $request)
    {
        $deliveries = $request->attributes->get('distributor')->deliveries()->with('commande.product')->latest('delivery_date')->latest('id')->paginate(10);

        return view('espace-distributeur.index', compact('deliveries'));
    }

    public function show(Request $request, Delivery $delivery)
    {
        $this->authorizeOwner($request, $delivery);

        return view('espace-distributeur.show', ['delivery' => $delivery->load(['commande.user', 'commande.product'])]);
    }

    public function updateStatus(Request $request, Delivery $delivery)
    {
        $this->authorizeOwner($request, $delivery);
        $next = $delivery->nextStatus();
        if (! $next) {
            return back()->with('error', 'Cette livraison ne peut plus changer de statut.');
        }
        $delivery->update(['status' => $next]);

        return to_route('distributor-space.show', $delivery)->with('success', 'Statut mis à jour : '.$delivery->status_label.'.');
    }

    private function authorizeOwner(Request $request, Delivery $delivery): void
    {
        abort_unless($delivery->distributor_id === $request->attributes->get('distributor')->id, 404);
    }
}
