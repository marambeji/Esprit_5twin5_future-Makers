<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransformateurRequest;
use App\Models\Transformateur;
use Illuminate\Http\Request;

class TransformateurController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['q', 'type_activite', 'ville']);

        $transformateurs = Transformateur::withCount('transformations')
            ->withSum('transformations', 'co2_kg')
            ->when($filters['q'] ?? null, function ($query, $q) {
                $query->where(fn ($query) => $query->where('nom', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
            })
            ->when($filters['type_activite'] ?? null, fn ($query, $type) => $query->where('type_activite', $type))
            ->when($filters['ville'] ?? null, fn ($query, $ville) => $query->where('ville', $ville))
            ->orderBy('nom')
            ->paginate(10)
            ->withQueryString();

        return view('admin.transformateurs.index', [
            'transformateurs' => $transformateurs,
            'villes'          => Transformateur::distinct()->orderBy('ville')->pluck('ville'),
            'filters'         => $filters,
        ]);
    }

    public function create()
    {
        return view('admin.transformateurs.form', ['transformateur' => new Transformateur]);
    }

    public function store(TransformateurRequest $request)
    {
        $transformateur = Transformateur::create($request->validated());

        return to_route('admin.transformateurs.show', $transformateur)->with('success', 'Transformateur créé.');
    }

    public function show(Transformateur $transformateur)
    {
        $transformateur->load(['transformations' => fn ($q) => $q->with(['product', 'ferme'])->latest('date_transformation')]);

        return view('admin.transformateurs.show', ['transformateur' => $transformateur]);
    }

    public function edit(Transformateur $transformateur)
    {
        return view('admin.transformateurs.form', ['transformateur' => $transformateur]);
    }

    public function update(TransformateurRequest $request, Transformateur $transformateur)
    {
        $transformateur->update($request->validated());

        return to_route('admin.transformateurs.show', $transformateur)->with('success', 'Transformateur modifié.');
    }

    public function destroy(Transformateur $transformateur)
    {
        $n = $transformateur->transformations()->count();
        $transformateur->delete();

        return to_route('admin.transformateurs.index')->with('success', 'Transformateur supprimé'.($n ? " avec ses {$n} transformation(s)." : '.'));
    }
}
