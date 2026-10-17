<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommandeRequest;
use App\Models\Commande;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class CommandeAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $commandes = Commande::with(['user', 'product', 'delivery'])
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('reference', 'like', "%{$q}%")->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"))->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$q}%"))))
            ->when($request->query('statut'), fn ($query, $s) => $query->where('statut', $s))
            ->latest()->paginate(10)->withQueryString();

        return view('admin.commandes.index', compact('commandes'));
    }

    public function create()
    {
        return view('admin.commandes.form', $this->formData(new Commande));
    }

    public function store(CommandeRequest $request)
    {
        $commande = Commande::create($request->validated() + ['statut' => 'en_attente']);

        return to_route('admin.commandes.show', $commande)->with('success', 'Commande créée : '.$commande->reference.'.');
    }

    public function show(Commande $commande)
    {
        return view('admin.commandes.show', ['commande' => $commande->load(['user', 'product', 'delivery.distributor'])]);
    }

    public function edit(Commande $commande)
    {
        if (! $commande->isPending()) {
            return to_route('admin.commandes.show', $commande)->with('error', 'Seule une commande en attente peut être modifiée.');
        }

        return view('admin.commandes.form', $this->formData($commande));
    }

    public function update(CommandeRequest $request, Commande $commande)
    {
        if (! $commande->isPending()) {
            return to_route('admin.commandes.show', $commande)->with('error', 'Seule une commande en attente peut être modifiée.');
        }
        $commande->update($request->validated());

        return to_route('admin.commandes.show', $commande)->with('success', 'Commande modifiée.');
    }

    public function destroy(Commande $commande)
    {
        if ($commande->delivery()->exists()) {
            return back()->with('error', 'Cette commande a une livraison : supprimez d’abord la livraison.');
        }
        $commande->delete();

        return to_route('admin.commandes.index')->with('success', 'Commande supprimée.');
    }

    public function confirm(Commande $commande)
    {
        if (! $commande->isPending()) {
            return back()->with('error', 'Seule une commande en attente peut être confirmée.');
        }
        $commande->update(['statut' => 'confirmee', 'motif_refus' => null]);

        return to_route('admin.commandes.show', $commande)->with('success', 'Commande confirmée. Vous pouvez maintenant créer la livraison.');
    }

    public function refuse(Request $request, Commande $commande)
    {
        if (! $commande->isPending()) {
            return back()->with('error', 'Seule une commande en attente peut être refusée.');
        }
        $data = $request->validate(['motif_refus' => ['required', 'string', 'max:500']], ['motif_refus.required' => 'Le motif du refus est obligatoire.'], ['motif_refus' => 'motif du refus']);
        $commande->update(['statut' => 'refusee'] + $data);

        return to_route('admin.commandes.show', $commande)->with('success', 'Commande refusée.');
    }

    private function formData(Commande $commande): array
    {
        return ['commande' => $commande, 'products' => Product::orderBy('name')->get(['id', 'name']), 'users' => User::where('role', 'user')->orderBy('username')->get(['id', 'name', 'username'])];
    }
}
