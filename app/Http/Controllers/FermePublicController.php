<?php

namespace App\Http\Controllers;

use App\Models\Ferme;
use App\Models\Producteur;
use Illuminate\Http\Request;

class FermePublicController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['q', 'producteur_id', 'superficie_min', 'superficie_max']);

        $fermes = Ferme::with('producteur')
            ->when($filters['q'] ?? null, function ($query, $q) {
                $query->where(function ($query) use ($q) {
                    $query->where('nom', 'like', '%' . $q . '%')
                          ->orWhere('localisation', 'like', '%' . $q . '%');
                });
            })
            ->when($filters['producteur_id'] ?? null, fn ($query, $id) => $query->where('producteur_id', $id))
            ->when($filters['superficie_min'] ?? null, fn ($query, $min) => $query->where('superficie', '>=', $min))
            ->when($filters['superficie_max'] ?? null, fn ($query, $max) => $query->where('superficie', '<=', $max))
            ->orderBy('nom')
            ->paginate(10)
            ->withQueryString();

        return view('pages.fermes.index', [
            'fermes'      => $fermes,
            'producteurs' => Producteur::orderBy('nom')->get(),
            'filters'     => $filters,
        ]);
    }

    public function show(Ferme $ferme)
    {
        return view('pages.fermes.show', [
            'ferme' => $ferme->load('producteur'),
        ]);
    }
}
