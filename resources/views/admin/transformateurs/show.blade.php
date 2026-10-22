@extends('admin.layouts.layout')
@section('title', $transformateur->nom.' — NutriTrace')
@section('content')
@php($intensite = $transformateur->intensiteMoyenne())
<div class="d-flex justify-content-between align-items-center mb-4">
<h1 class="h3">{{ $transformateur->nom }}</h1>
<div class="d-flex gap-2"><a class="btn btn-outline-primary" href="{{ route('admin.transformateurs.edit', $transformateur) }}">Modifier</a><form method="POST" action="{{ route('admin.transformateurs.destroy', $transformateur) }}" onsubmit="return confirm('Supprimer ce transformateur et ses transformations ?');">@csrf @method('DELETE')<button class="btn btn-outline-danger">Supprimer</button></form></div>
</div>
<div class="row">
<div class="col-lg-8 mb-4"><div class="card h-100"><div class="card-body"><dl class="row mb-0">
<dt class="col-sm-3">Activité</dt><dd class="col-sm-9">{{ $transformateur->type_label }}</dd>
<dt class="col-sm-3">E-mail</dt><dd class="col-sm-9"><a href="mailto:{{ $transformateur->email }}">{{ $transformateur->email }}</a></dd>
<dt class="col-sm-3">Téléphone</dt><dd class="col-sm-9">{{ $transformateur->telephone }}</dd>
<dt class="col-sm-3">Adresse</dt><dd class="col-sm-9">{{ $transformateur->adresse }}, {{ $transformateur->ville }}</dd>
<dt class="col-sm-3">Description</dt><dd class="col-sm-9" style="white-space:pre-line">{{ $transformateur->description ?? '—' }}</dd>
</dl></div></div></div>
<div class="col-lg-4 mb-4"><div class="card h-100"><div class="card-body text-center">
<h2 class="h6 text-muted">Empreinte moyenne</h2>
<x-eco-score :score="\App\Models\Transformation::scoreFor($intensite)" size="lg" class="my-2" />
<p class="mb-1">{{ $intensite !== null ? number_format($intensite, 3, ',', ' ').' kg CO₂e / kg transformé' : 'Aucune empreinte renseignée' }}</p>
<p class="small text-muted mb-0">{{ $transformateur->transformations->count() }} transformation(s) · {{ number_format($transformateur->transformations->sum('quantite_kg'), 0, ',', ' ') }} kg traités</p>
</div></div></div>
</div>

<div class="card"><div class="card-body">
<div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">Transformations</h2><a class="btn btn-sm btn-primary" href="{{ route('admin.transformations.create', ['transformateur_id' => $transformateur->id]) }}">Ajouter une transformation</a></div>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Référence</th><th>Date</th><th>Produit</th><th>Procédé</th><th>Quantité</th><th>CO₂</th><th>Éco-score</th></tr></thead><tbody>
@forelse ($transformateur->transformations as $t)
<tr><td><a href="{{ route('admin.transformations.show', $t) }}">{{ $t->reference }}</a></td><td>{{ $t->date_transformation->format('d/m/Y') }}</td><td>{{ $t->product->name }}</td><td>{{ $t->procede_label }}</td><td>{{ number_format($t->quantite_kg, 0, ',', ' ') }} kg</td><td>{{ $t->co2_kg !== null ? number_format($t->co2_kg, 1, ',', ' ').' kg' : '—' }}</td><td><x-eco-score :score="$t->eco_score" size="sm" /></td></tr>
@empty <tr><td colspan="7">Aucune transformation enregistrée pour ce transformateur.</td></tr> @endforelse
</tbody></table></div>
</div></div>
<div class="mt-3"><a href="{{ route('admin.transformateurs.index') }}" class="btn btn-light">← Retour à la liste</a></div>
@endsection
