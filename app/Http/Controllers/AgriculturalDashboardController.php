<?php

namespace App\Http\Controllers;

use App\Models\Ferme;
use App\Models\Producteur;

class AgriculturalDashboardController extends Controller
{
    public function index()
    {
        $farmStatistics = Ferme::query()
            ->selectRaw('COUNT(*) as total_fermes, COALESCE(SUM(superficie), 0) as superficie_totale, COALESCE(AVG(superficie), 0) as superficie_moyenne')
            ->firstOrFail();

        $statistics = [
            'total_producteurs' => Producteur::count(),
            'total_fermes' => (int) $farmStatistics->total_fermes,
            'superficie_totale' => (float) $farmStatistics->superficie_totale,
            'superficie_moyenne' => round((float) $farmStatistics->superficie_moyenne, 2),
            'producteurs_sans_ferme' => Producteur::doesntHave('fermes')->count(),
        ];

        $fermesParRegion = Ferme::query()
            ->selectRaw('localisation, COUNT(*) as total_fermes')
            ->groupBy('localisation')
            ->orderByDesc('total_fermes')
            ->orderBy('localisation')
            ->get()
            ->groupBy(fn (Ferme $ferme) => trim(explode(',', $ferme->localisation)[0]))
            ->map(fn ($fermes, string $region) => [
                'region' => $region,
                'total_fermes' => $fermes->sum('total_fermes'),
            ])
            ->sortByDesc('total_fermes')
            ->values();

        $fermesParProducteur = Producteur::query()
            ->select(['id', 'nom', 'prenom'])
            ->withCount('fermes')
            ->orderByDesc('fermes_count')
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        return view('admin.agriculture.dashboard', compact('statistics', 'fermesParRegion', 'fermesParProducteur'));
    }
}
