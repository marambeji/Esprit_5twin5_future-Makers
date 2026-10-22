<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProducteurRequest;
use App\Models\Producteur;
use App\Services\AgriculturalDataQualityService;
use App\Services\AgriculturalListingService;
use Illuminate\Http\Request;

class ProducteurController extends Controller
{
    public function index(Request $request, AgriculturalListingService $listing, AgriculturalDataQualityService $dataQuality)
    {
        $filters = $request->only(['q', 'fermes_count', 'sort', 'direction']);
        $producteurs = $listing->producteurs($request)
            ->paginate(10)
            ->withQueryString();

        $producteurs->getCollection()->each(fn (Producteur $producteur) => $producteur->setAttribute('data_quality', $dataQuality->forProducteur($producteur)));

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

    public function show(Producteur $producteur, AgriculturalDataQualityService $dataQuality)
    {
        return view('admin.producteurs.show', [
            'producteur' => $producteur,
            'fermes'     => $producteur->fermes()->orderBy('nom')->paginate(10),
            'dataQuality' => $dataQuality->forProducteur($producteur),
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
