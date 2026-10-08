<?php

namespace App\Http\Controllers;

use App\Models\Producteur;
use Illuminate\Http\Request;

class ProducteurPublicController extends Controller
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

        return view('pages.producteurs.index', compact('producteurs', 'filters'));
    }

    public function show(Producteur $producteur)
    {
        return view('pages.producteurs.show', [
            'producteur' => $producteur,
            'fermes'     => $producteur->fermes()->orderBy('nom')->paginate(10),
        ]);
    }
}
