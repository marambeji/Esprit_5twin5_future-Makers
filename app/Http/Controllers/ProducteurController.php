<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProducteurRequest;
use App\Models\Producteur;
use Illuminate\Http\Request;

class ProducteurController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['q', 'fermes_count']);

        $producteurs = Producteur::withCount('fermes')
            ->when($filters['q'] ?? null, function ($query, $q) {
                $query->where(function ($query) use ($q) {
                    $query->where('nom', 'like', '%' . $q . '%')
                          ->orWhere('prenom', 'like', '%' . $q . '%')
                          ->orWhere('email', 'like', '%' . $q . '%');
                });
            })
            ->when($filters['fermes_count'] ?? null, function ($query, $filter) {
                if ($filter === '0') {
                    $query->doesntHave('fermes');
                } elseif ($filter === '1-3') {
                    $query->has('fermes', '>=', 1)->has('fermes', '<=', 3);
                } elseif ($filter === '4+') {
                    $query->has('fermes', '>=', 4);
                }
            })
            ->orderBy('nom')
            ->paginate(10)
            ->withQueryString();

        return view('admin.producteurs.index', compact('producteurs', 'filters'));
    }

    public function create()
    {
        return view('admin.producteurs.form', ['producteur' => new Producteur]);
    }

    public function store(ProducteurRequest $request)
    {
        $producteur = Producteur::create($request->validated());

        return to_route('admin.producteurs.show', $producteur)->with('success', 'Producteur créé.');
    }

    public function show(Producteur $producteur)
    {
        return view('admin.producteurs.show', [
            'producteur' => $producteur,
            'fermes'     => $producteur->fermes()->orderBy('nom')->paginate(10),
        ]);
    }

    public function edit(Producteur $producteur)
    {
        return view('admin.producteurs.form', compact('producteur'));
    }

    public function update(ProducteurRequest $request, Producteur $producteur)
    {
        $producteur->update($request->validated());

        return to_route('admin.producteurs.show', $producteur)->with('success', 'Producteur modifié.');
    }

    public function destroy(Producteur $producteur)
    {
        if ($producteur->fermes()->exists()) {
            return back()->with('error', 'Ce producteur possède des fermes. Supprimez ou déplacez les fermes avant de supprimer le producteur.');
        }
        $producteur->delete();

        return to_route('admin.producteurs.index')->with('success', 'Producteur supprimé.');
    }
}
