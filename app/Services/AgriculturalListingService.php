<?php

namespace App\Services;

use App\Models\Ferme;
use App\Models\Producteur;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AgriculturalListingService
{
    public function producteurs(Request $request): Builder
    {
        $query = Producteur::query()->withCount('fermes');
        $search = trim($request->string('q')->toString());

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('fermes', fn (Builder $query) => $query
                        ->where('nom', 'like', "%{$search}%")
                        ->orWhere('localisation', 'like', "%{$search}%"));
            });
        }

        match ($request->input('fermes_count')) {
            '0' => $query->doesntHave('fermes'),
            '1-3' => $query->has('fermes', '>=', 1)->has('fermes', '<=', 3),
            '4+' => $query->has('fermes', '>=', 4),
            default => null,
        };

        return $this->sort($query, $request, ['nom', 'prenom', 'email', 'fermes_count'], 'nom');
    }

    public function fermes(Request $request): Builder
    {
        $query = Ferme::query()->with('producteur:id,nom,prenom,email,telephone,adresse');
        $search = trim($request->string('q')->toString());

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('nom', 'like', "%{$search}%")
                    ->orWhere('localisation', 'like', "%{$search}%")
                    ->orWhereHas('producteur', fn (Builder $query) => $query
                        ->where('nom', 'like', "%{$search}%")
                        ->orWhere('prenom', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('producteur_id')) {
            $query->where('producteur_id', $request->integer('producteur_id'));
        }
        if ($request->filled('superficie_min')) {
            $query->where('superficie', '>=', $request->input('superficie_min'));
        }
        if ($request->filled('superficie_max')) {
            $query->where('superficie', '<=', $request->input('superficie_max'));
        }

        return $this->sort($query, $request, ['nom', 'localisation', 'superficie'], 'nom');
    }

    private function sort(Builder $query, Request $request, array $allowedSorts, string $defaultSort): Builder
    {
        $sort = $request->input('sort');
        $sort = in_array($sort, $allowedSorts, true) ? $sort : $defaultSort;
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($sort, $direction);
    }
}
