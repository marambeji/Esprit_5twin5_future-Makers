<?php

namespace App\Http\Controllers;

use App\Http\Requests\EcoScoreRequest;
use App\Http\Requests\TransformationRequest;
use App\Models\Ferme;
use App\Models\Product;
use App\Models\Transformateur;
use App\Models\Transformation;
use App\Services\EcoScoreAI;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransformationController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['q', 'transformateur_id', 'product_id', 'procede', 'eco_score']);

        $transformations = Transformation::with(['transformateur', 'product', 'ferme'])
            ->when($filters['q'] ?? null, fn ($query, $q) => $query->where('reference', 'like', "%{$q}%"))
            ->when($filters['transformateur_id'] ?? null, fn ($query, $id) => $query->where('transformateur_id', $id))
            ->when($filters['product_id'] ?? null, fn ($query, $id) => $query->where('product_id', $id))
            ->when($filters['procede'] ?? null, fn ($query, $p) => $query->where('procede', $p))
            ->when($filters['eco_score'] ?? null, fn ($query, $s) => $s === 'aucun' ? $query->whereNull('eco_score') : $query->where('eco_score', $s))
            ->latest('date_transformation')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.transformations.index', [
            'transformations' => $transformations,
            'filters'         => $filters,
        ] + $this->options());
    }

    public function create(Request $request)
    {
        // Pré-sélection possible depuis la fiche d'un transformateur.
        $transformation = new Transformation([
            'transformateur_id' => $request->integer('transformateur_id') ?: null,
            'date_transformation' => now(),
        ]);

        return view('admin.transformations.form', ['transformation' => $transformation] + $this->options());
    }

    public function store(TransformationRequest $request)
    {
        $transformation = Transformation::create($request->validated());

        return to_route('admin.transformations.show', $transformation)->with('success', 'Transformation '.$transformation->reference.' enregistrée.');
    }

    public function show(Transformation $transformation)
    {
        return view('admin.transformations.show', ['transformation' => $transformation->load(['transformateur', 'product.category', 'ferme.producteur'])]);
    }

    public function edit(Transformation $transformation)
    {
        return view('admin.transformations.form', ['transformation' => $transformation] + $this->options());
    }

    public function update(TransformationRequest $request, Transformation $transformation)
    {
        $transformation->update($request->validated());

        return to_route('admin.transformations.show', $transformation)->with('success', 'Transformation modifiée.');
    }

    public function destroy(Transformation $transformation)
    {
        $transformation->delete();

        return to_route('admin.transformations.index')->with('success', 'Transformation supprimée.');
    }

    /** Estimation IA de l'empreinte et vérification anti-greenwashing (appel AJAX depuis le formulaire). */
    public function estimate(EcoScoreRequest $request, EcoScoreAI $ai): JsonResponse
    {
        $data = $request->validated();
        $transformateur = isset($data['transformateur_id']) ? Transformateur::find($data['transformateur_id']) : null;
        $ferme = isset($data['ferme_id']) ? Ferme::find($data['ferme_id']) : null;

        return response()->json($ai->analyser([
            'produit'        => Product::findOrFail($data['product_id'])->name,
            'procede'        => $data['procede'],
            'quantite_kg'    => (float) $data['quantite_kg'],
            'transformateur' => $transformateur?->nom,
            'ville'          => $transformateur?->ville,
            'ferme'          => $ferme ? $ferme->nom.' ('.$ferme->localisation.')' : null,
            'allegation'     => $data['allegation'] ?? null,
        ]));
    }

    private function options(): array
    {
        return [
            'transformateurs' => Transformateur::orderBy('nom')->get(['id', 'nom', 'ville']),
            'products'        => Product::orderBy('name')->get(['id', 'name']),
            'fermes'          => Ferme::orderBy('nom')->get(['id', 'nom', 'localisation']),
        ];
    }
}
