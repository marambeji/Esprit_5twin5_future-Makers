<?php

namespace App\Http\Controllers;

use App\Http\Requests\FermeRequest;
use App\Models\Ferme;
use App\Models\Producteur;
use Illuminate\Http\Request;

class FermeController extends Controller
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

        return view('admin.fermes.index', [
            'fermes'      => $fermes,
            'producteurs' => Producteur::orderBy('nom')->get(),
            'filters'     => $filters,
        ]);
    }

    public function create()
    {
        return view('admin.fermes.form', [
            'ferme'       => new Ferme,
            'producteurs' => Producteur::orderBy('nom')->get(),
        ]);
    }

    public function store(FermeRequest $request)
    {
        $ferme = Ferme::create($request->validated());

        return to_route('admin.fermes.show', $ferme)->with('success', 'Ferme créée.');
    }

    public function show(Ferme $ferme)
    {
        return view('admin.fermes.show', ['ferme' => $ferme->load('producteur')]);
    }

    public function edit(Ferme $ferme)
    {
        return view('admin.fermes.form', [
            'ferme'       => $ferme,
            'producteurs' => Producteur::orderBy('nom')->get(),
        ]);
    }

    public function update(FermeRequest $request, Ferme $ferme)
    {
        $ferme->update($request->validated());

        return to_route('admin.fermes.show', $ferme)->with('success', 'Ferme modifiée.');
    }

    public function destroy(Ferme $ferme)
    {
        $ferme->delete();

        return to_route('admin.fermes.index')->with('success', 'Ferme supprimée.');
    }
}
