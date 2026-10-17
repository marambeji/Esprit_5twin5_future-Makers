<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeliveryRequest;
use App\Models\Commande;
use App\Models\Delivery;
use App\Models\Distributor;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $deliveries = Delivery::with(['distributor', 'commande'])->search($request->query('q'), $request->query('status'))->latest('delivery_date')->latest('id')->paginate(10)->withQueryString();

        return view('admin.deliveries.index', compact('deliveries'));
    }

    public function create(Request $request)
    {
        $delivery = new Delivery(['status' => 'planifiee', 'commande_id' => $request->query('commande_id'), 'distributor_id' => $request->query('distributor_id')]);

        return view('admin.deliveries.form', $this->formData($delivery));
    }

    public function store(DeliveryRequest $request)
    {
        $data = $request->validated();
        $commande = Commande::findOrFail($data['commande_id']);
        $delivery = Delivery::create($data + ['destination' => $commande->adresse_livraison, 'status' => 'planifiee']);

        return to_route('admin.deliveries.show', $delivery)->with('success', 'Livraison créée : '.$delivery->reference.'.');
    }

    public function show(Delivery $delivery)
    {
        return view('admin.deliveries.show', ['delivery' => $delivery->load(['distributor', 'commande.user', 'commande.product'])]);
    }

    public function edit(Delivery $delivery)
    {
        return view('admin.deliveries.form', $this->formData($delivery));
    }

    public function update(DeliveryRequest $request, Delivery $delivery)
    {
        $delivery->update($request->validated());

        return to_route('admin.deliveries.show', $delivery)->with('success', 'Livraison modifiée.');
    }

    public function destroy(Delivery $delivery)
    {
        $delivery->delete();

        return to_route('admin.deliveries.index')->with('success', 'Livraison supprimée.');
    }

    private function formData(Delivery $delivery): array
    {
        return [
            'delivery' => $delivery,
            'distributors' => Distributor::where('actif', true)->orWhere('id', $delivery->distributor_id)->orderBy('name')->get(),
            'commandes' => Commande::with(['user', 'product'])->where('statut', 'confirmee')->whereDoesntHave('delivery')->latest()->get(),
        ];
    }
}
