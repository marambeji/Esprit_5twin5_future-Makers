<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommandeRequest;
use App\Models\Commande;
use App\Models\Product;
use App\Services\NutriAI;
use Illuminate\Http\Request;

// Espace client : chaque client ne voit que ses propres commandes.
class CommandeController extends Controller
{
    public function index(Request $request)
    {
        $commandes = Commande::with(['product', 'delivery'])->where('user_id', $request->user()->id)->latest()->paginate(10);

        return view('commandes.index', compact('commandes'));
    }

    public function create(Request $request)
    {
        return view('commandes.create', ['products' => Product::orderBy('name')->get(['id', 'name', 'price']), 'selected' => $request->query('product')]);
    }

    /** Assistant IA : transforme une phrase libre en produit, quantité et adresse (le client valide ensuite). */
    public function assist(Request $request)
    {
        $text = $request->validate(['texte' => ['required', 'string', 'max:500']])['texte'];
        $catalogue = Product::orderBy('name')->get(['id', 'name'])->map(fn ($p) => "{$p->id}: {$p->name}")->implode("\n");
        $result = NutriAI::ask("Tu aides un client à commander. Catalogue (id: nom) :\n{$catalogue}\n\nPhrase du client : \"{$text}\"\nChoisis le produit du catalogue le plus proche (product_id = 0 si aucun ne correspond), la quantité (1 par défaut) et l'adresse de livraison si elle est donnée (sinon chaîne vide).", [
            'type' => 'object', 'required' => ['product_id', 'quantite', 'adresse'],
            'properties' => ['product_id' => ['type' => 'integer'], 'quantite' => ['type' => 'integer'], 'adresse' => ['type' => 'string']],
        ]);
        if (! is_array($result) || ! Product::whereKey($result['product_id'] ?? 0)->exists()) {
            return response()->json(['message' => 'Je n’ai pas trouvé de produit correspondant. Reformulez ou choisissez dans la liste.'], 422);
        }

        return response()->json(['product_id' => $result['product_id'], 'quantite' => max(1, min(1000, (int) $result['quantite'])), 'adresse' => (string) ($result['adresse'] ?? '')]);
    }

    public function store(CommandeRequest $request)
    {
        $commande = Commande::create($request->validated() + ['user_id' => $request->user()->id, 'statut' => 'en_attente']);

        return to_route('commandes.show', $commande)->with('success', 'Commande '.$commande->reference.' enregistrée. Elle est en attente de confirmation.');
    }

    public function show(Request $request, Commande $commande)
    {
        $this->authorizeOwner($request, $commande);

        return view('commandes.show', ['commande' => $commande->load(['product', 'delivery.distributor'])]);
    }

    public function cancel(Request $request, Commande $commande)
    {
        $this->authorizeOwner($request, $commande);
        if (! $commande->isPending()) {
            return back()->with('error', 'Seule une commande en attente peut être annulée.');
        }
        $commande->update(['statut' => 'annulee']);

        return to_route('commandes.show', $commande)->with('success', 'Commande annulée.');
    }

    private function authorizeOwner(Request $request, Commande $commande): void
    {
        abort_unless($commande->user_id === $request->user()->id, 404);
    }
}
