<?php

namespace App\Http\Controllers;

use App\Models\Ferme;
use App\Models\Producteur;
use App\Services\AgriculturalDataQualityService;
use Illuminate\Http\Request;

class FermePublicController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['q', 'producteur_id', 'superficie_min', 'superficie_max']);

        $query = Ferme::with('producteur')
            ->when($filters['q'] ?? null, function ($query, $q) {
                $query->where(function ($query) use ($q) {
                    $query->where('nom', 'like', '%'.$q.'%')
                        ->orWhere('localisation', 'like', '%'.$q.'%');
                });
            })
            ->when($filters['producteur_id'] ?? null, fn ($query, $id) => $query->where('producteur_id', $id))
            ->when($filters['superficie_min'] ?? null, fn ($query, $min) => $query->where('superficie', '>=', $min))
            ->when($filters['superficie_max'] ?? null, fn ($query, $max) => $query->where('superficie', '<=', $max))
            ->orderBy('nom');
        $fermes = (clone $query)->paginate(10)->withQueryString();
        $markers = (clone $query)->whereNotNull('latitude')->whereNotNull('longitude')
            ->whereBetween('latitude', [-90, 90])->whereBetween('longitude', [-180, 180])
            ->get(['id', 'producteur_id', 'nom', 'localisation', 'latitude', 'longitude']);
        $producer = $this->ownedProducer($request);

        return view('pages.fermes.index', [
            'fermes' => $fermes,
            'producteurs' => Producteur::orderBy('nom')->get(),
            'filters' => $filters,
            'markers' => $markers,
            'managedProducer' => $producer,
        ]);
    }

    public function show(Request $request, Ferme $ferme, AgriculturalDataQualityService $dataQuality)
    {
        return view('pages.fermes.show', [
            'ferme' => $ferme->load('producteur'),
            'canManage' => $this->ownedProducer($request)?->is($ferme->producteur) ?? false,
            'dataQuality' => $dataQuality->forFerme($ferme),
        ]);
    }

    public function create(Request $request)
    {
        $producer = $this->requireOwnedProducer($request);

        return view('pages.fermes.form', ['ferme' => new Ferme(['producteur_id' => $producer->id]), 'producer' => $producer]);
    }

    public function store(Request $request)
    {
        $producer = $this->requireOwnedProducer($request);
        $data = $this->validatedFarmInput($request);
        $data['producteur_id'] = $producer->id;
        $ferme = Ferme::create($data);

        return to_route('fermes.show', $ferme)->with('success', 'Farm created successfully.');
    }

    public function edit(Request $request, Ferme $ferme)
    {
        $this->requireFarmOwner($request, $ferme);

        return view('pages.fermes.form', ['ferme' => $ferme, 'producer' => $ferme->producteur]);
    }

    public function update(Request $request, Ferme $ferme)
    {
        $this->requireFarmOwner($request, $ferme);
        $data = $this->validatedFarmInput($request);
        $data['producteur_id'] = $ferme->producteur_id;
        $ferme->update($data);

        return to_route('fermes.show', $ferme)->with('success', 'Farm updated successfully.');
    }

    public function destroy(Request $request, Ferme $ferme)
    {
        $this->requireFarmOwner($request, $ferme);
        $ferme->delete();

        return to_route('fermes.index')->with('success', 'Farm deleted successfully.');
    }

    private function ownedProducer(Request $request): ?Producteur
    {
        $user = $request->user('web');
        if (! $user || $user->role !== 'user' || ! $user->hasVerifiedEmail()) {
            return null;
        }

        return Producteur::query()->whereRaw('LOWER(email) = ?', [mb_strtolower($user->email)])->first();
    }

    private function requireOwnedProducer(Request $request): Producteur
    {
        $producer = $this->ownedProducer($request);
        abort_unless($producer, 403, 'A verified account linked to a producer record is required to manage farms.');

        return $producer;
    }

    private function requireFarmOwner(Request $request, Ferme $ferme): Producteur
    {
        $producer = $this->requireOwnedProducer($request);
        abort_unless($ferme->producteur_id === $producer->id, 404);

        return $producer;
    }

    private function validatedFarmInput(Request $request): array
    {
        return $request->validate([
            'nom' => ['required', 'string', 'max:150'],
            'localisation' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90', 'decimal:0,7'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180', 'decimal:0,7'],
            'superficie' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'description' => ['nullable', 'string', 'max:2000'],
        ], [
            'nom.required' => 'Le nom de la ferme est obligatoire.',
            'nom.max' => 'Le nom ne peut pas dépasser 150 caractères.',
            'localisation.required' => 'La localisation est obligatoire.',
            'localisation.max' => 'La localisation ne peut pas dépasser 255 caractères.',
            'latitude.required_with' => 'La latitude est obligatoire lorsque la longitude est renseignée.',
            'latitude.numeric' => 'La latitude doit être un nombre.',
            'latitude.between' => 'La latitude doit être comprise entre -90 et 90.',
            'latitude.decimal' => 'La latitude accepte au maximum sept décimales.',
            'longitude.required_with' => 'La longitude est obligatoire lorsque la latitude est renseignée.',
            'longitude.numeric' => 'La longitude doit être un nombre.',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180.',
            'longitude.decimal' => 'La longitude accepte au maximum sept décimales.',
            'superficie.numeric' => 'La superficie doit être un nombre.',
            'superficie.min' => 'La superficie doit être positive ou nulle.',
            'superficie.max' => 'La superficie dépasse la valeur maximale autorisée.',
            'superficie.decimal' => 'La superficie accepte au maximum deux décimales.',
            'description.string' => 'La description doit être un texte.',
            'description.max' => 'La description ne peut pas dépasser 2000 caractères.',
        ]);
    }
}
