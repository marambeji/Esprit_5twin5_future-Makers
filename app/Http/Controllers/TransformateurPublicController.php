<?php

namespace App\Http\Controllers;

use App\Models\Transformateur;
use App\Models\Transformation;
use Illuminate\Http\Request;

class TransformateurPublicController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['q', 'type_activite', 'eco_score']);

        $transformateurs = Transformateur::with('transformations')
            ->withCount('transformations')
            ->when($filters['q'] ?? null, function ($query, $q) {
                $query->where(fn ($query) => $query->where('nom', 'like', "%{$q}%")->orWhere('ville', 'like', "%{$q}%"));
            })
            ->when($filters['type_activite'] ?? null, fn ($query, $type) => $query->where('type_activite', $type))
            // Transformateurs ayant au moins une étape avec cet éco-score (jointure via whereHas).
            ->when($filters['eco_score'] ?? null, fn ($query, $s) => $query->whereHas('transformations', fn ($t) => $t->where('eco_score', $s)))
            ->orderBy('nom')
            ->paginate(9)
            ->withQueryString();

        return view('pages.transformateurs.index', ['transformateurs' => $transformateurs, 'filters' => $filters]);
    }

    public function show(Transformateur $transformateur)
    {
        $transformateur->load(['transformations' => fn ($q) => $q->with(['product', 'ferme.producteur'])->latest('date_transformation')]);

        return view('pages.transformateurs.show', ['transformateur' => $transformateur]);
    }
}
