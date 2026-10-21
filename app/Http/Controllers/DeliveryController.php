<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeliveryRequest;
use App\Models\Commande;
use App\Models\Delivery;
use App\Models\Distributor;
use App\Services\NutriAI;
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
        return view('admin.deliveries.show', ['delivery' => $delivery->load(['distributor', 'commande.user', 'commande.product', 'avis'])]);
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

    /** Suggère le meilleur distributeur actif pour une commande (IA, ou règle simple si l'IA est indisponible). */
    public function suggest(Request $request)
    {
        $commande = Commande::findOrFail($request->validate(['commande_id' => ['required', 'integer']])['commande_id']);
        $distributors = Distributor::where('actif', true)->withCount(['deliveries as charge' => fn ($q) => $q->whereIn('status', ['planifiee', 'en_cours'])])->get();
        if ($distributors->isEmpty()) {
            return response()->json(['message' => 'Aucun distributeur actif.'], 422);
        }
        $liste = $distributors->map(fn ($d) => "{$d->id}: {$d->name}, ville {$d->city}, {$d->charge} livraison(s) en cours, {$d->description}")->implode("\n");
        $result = NutriAI::ask("Tu aides un administrateur à affecter une livraison. Adresse de livraison : \"{$commande->adresse_livraison}\".\nDistributeurs actifs (id: infos) :\n{$liste}\n\nChoisis le plus adapté : même ville ou zone que l'adresse en priorité, puis la plus faible charge. Donne son id et une raison en une phrase en français.", [
            'type' => 'object', 'required' => ['distributor_id', 'raison'],
            'properties' => ['distributor_id' => ['type' => 'integer'], 'raison' => ['type' => 'string']],
        ]);
        $chosen = is_array($result) ? $distributors->firstWhere('id', $result['distributor_id'] ?? 0) : null;
        if ($chosen) {
            return response()->json(['distributor_id' => $chosen->id, 'raison' => (string) ($result['raison'] ?? ''), 'ia' => true]);
        }
        // Secours sans IA : même ville si possible, sinon la plus faible charge.
        $sameCity = $distributors->filter(fn ($d) => $d->city && stripos($commande->adresse_livraison, $d->city) !== false);
        $chosen = ($sameCity->isNotEmpty() ? $sameCity : $distributors)->sortBy('charge')->first();

        return response()->json(['distributor_id' => $chosen->id, 'raison' => "Règle simple (IA indisponible) : {$chosen->name}, {$chosen->charge} livraison(s) en cours.", 'ia' => false]);
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
