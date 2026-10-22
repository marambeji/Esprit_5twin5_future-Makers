<?php

namespace App\Http\Controllers;

use App\Models\Ferme;
use App\Models\Producteur;
use App\Services\AgriculturalListingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AgriculturalMapController extends Controller
{
    public function index(Request $request, AgriculturalListingService $listing)
    {
        $matching = $listing->fermes($request);
        $matchingCount = (clone $matching)->count();
        $located = (clone $matching)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [-90, 90])
            ->whereBetween('longitude', [-180, 180]);
        $locatedCount = (clone $located)->count();
        $fermes = $located->get();

        $markers = $fermes->map(fn (Ferme $ferme) => [
            'nom' => $ferme->nom,
            'producteur' => "{$ferme->producteur->prenom} {$ferme->producteur->nom}",
            'localisation' => $ferme->localisation,
            'superficie' => $ferme->superficie,
            'description' => $ferme->description ? Str::limit($ferme->description, 180) : null,
            'latitude' => (float) $ferme->latitude,
            'longitude' => (float) $ferme->longitude,
            'url' => route('admin.fermes.show', $ferme),
        ])->values();

        return view('admin.agriculture.map', [
            'fermes' => $fermes,
            'markers' => $markers,
            'matchingCount' => $matchingCount,
            'locatedCount' => $locatedCount,
            'withoutValidCoordinatesCount' => max(0, $matchingCount - $locatedCount),
            'producteurs' => Producteur::orderBy('nom')->get(['id', 'nom', 'prenom']),
            'tilesUrl' => config('services.openstreetmap.tiles_url'),
        ]);
    }
}
