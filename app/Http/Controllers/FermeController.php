<?php

namespace App\Http\Controllers;

use App\Http\Requests\FermeRequest;
use App\Models\Ferme;
use App\Models\Producteur;
use App\Services\AgriculturalDataQualityService;
use App\Services\AgriculturalListingService;
use App\Services\FarmBoundaryGeometry;
use Illuminate\Http\Request;

class FermeController extends Controller
{
    public function index(Request $request, AgriculturalListingService $listing, AgriculturalDataQualityService $dataQuality)
    {
        $filters = $request->only(['q', 'producteur_id', 'superficie_min', 'superficie_max', 'sort', 'direction']);
        $fermes = $listing->fermes($request)
            ->paginate(10)
            ->withQueryString();

        $fermes->getCollection()->each(fn (Ferme $ferme) => $ferme->setAttribute('data_quality', $dataQuality->forFerme($ferme)));

        return view('admin.fermes.index', [
            'fermes' => $fermes,
            'producteurs' => Producteur::orderBy('nom')->get(),
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        return view('admin.fermes.form', [
            'ferme' => new Ferme,
            'producteurs' => Producteur::orderBy('nom')->get(),
        ]);
    }

    public function store(FermeRequest $request, FarmBoundaryGeometry $geometry)
    {
        $data = $request->validated();
        $data = $this->applyBoundary($request, $data, $geometry, false);
        $ferme = Ferme::create($data);

        return to_route('admin.fermes.show', $ferme)->with('success', 'Ferme créée.');
    }

    public function show(Ferme $ferme, AgriculturalDataQualityService $dataQuality)
    {
        return view('admin.fermes.show', [
            'ferme' => $ferme->load('producteur'),
            'dataQuality' => $dataQuality->forFerme($ferme),
        ]);
    }

    public function edit(Ferme $ferme)
    {
        return view('admin.fermes.form', [
            'ferme' => $ferme,
            'producteurs' => Producteur::orderBy('nom')->get(),
        ]);
    }

    public function update(FermeRequest $request, Ferme $ferme, FarmBoundaryGeometry $geometry)
    {
        $data = $request->validated();
        $data = $this->applyBoundary($request, $data, $geometry, true);
        $ferme->update($data);

        return to_route('admin.fermes.show', $ferme)->with('success', 'Ferme modifiée.');
    }

    public function destroy(Ferme $ferme)
    {
        $ferme->delete();

        return to_route('admin.fermes.index')->with('success', 'Ferme supprimée.');
    }

    private function applyBoundary(FermeRequest $request, array $data, FarmBoundaryGeometry $geometry, bool $updating): array
    {
        unset($data['remove_boundary']);
        $raw = $request->input('boundary_geojson');
        if (is_string($raw) && trim($raw) !== '') {
            $measured = $geometry->measure($raw);

            return [...$data, 'boundary_geojson' => $measured['geojson'], 'boundary_area_m2' => $measured['area_m2'], 'boundary_perimeter_m' => $measured['perimeter_m']];
        }
        if ($request->boolean('remove_boundary')) {
            return [...$data, 'boundary_geojson' => null, 'boundary_area_m2' => null, 'boundary_perimeter_m' => null];
        }
        if ($updating) {
            unset($data['boundary_geojson']);
        }

        return $data;
    }
}
