@extends('admin.layouts.layout')
@section('title', 'Transformation '.$transformation->reference.' — NutriTrace')
@section('content')
@php($t = $transformation)
<div class="d-flex justify-content-between align-items-center mb-4">
<h1 class="h3">Transformation {{ $t->reference }}</h1>
<div class="d-flex gap-2"><a class="btn btn-outline-primary" href="{{ route('admin.transformations.edit', $t) }}">Modifier</a><form method="POST" action="{{ route('admin.transformations.destroy', $t) }}" onsubmit="return confirm('Supprimer cette transformation ?');">@csrf @method('DELETE')<button class="btn btn-outline-danger">Supprimer</button></form></div>
</div>

{{-- Parcours ferme → transformateur → produit --}}
<div class="card mb-4"><div class="card-body d-flex flex-wrap align-items-center gap-3">
    <div><div class="small text-muted">Ferme d’origine</div>@if ($t->ferme)<a href="{{ route('admin.fermes.show', $t->ferme) }}">{{ $t->ferme->nom }}</a> <span class="text-muted">({{ $t->ferme->localisation }})</span>@else<span class="text-muted">Non renseignée</span>@endif</div>
    <i class="bi bi-arrow-right fs-4 text-muted" aria-hidden="true"></i>
    <div><div class="small text-muted">Transformateur</div><a href="{{ route('admin.transformateurs.show', $t->transformateur) }}">{{ $t->transformateur->nom }}</a> <span class="text-muted">({{ $t->transformateur->ville }})</span></div>
    <i class="bi bi-arrow-right fs-4 text-muted" aria-hidden="true"></i>
    <div><div class="small text-muted">Produit</div><a href="{{ route('admin.products.show', $t->product) }}">{{ $t->product->name }}</a></div>
</div></div>

<div class="row">
<div class="col-lg-7 mb-4"><div class="card h-100"><div class="card-body"><dl class="row mb-0">
<dt class="col-sm-4">Procédé</dt><dd class="col-sm-8">{{ $t->procede_label }}</dd>
<dt class="col-sm-4">Date</dt><dd class="col-sm-8">{{ $t->date_transformation->format('d/m/Y') }}</dd>
<dt class="col-sm-4">Quantité</dt><dd class="col-sm-8">{{ number_format($t->quantite_kg, 2, ',', ' ') }} kg</dd>
<dt class="col-sm-4">Émissions</dt><dd class="col-sm-8">{{ $t->co2_kg !== null ? number_format($t->co2_kg, 2, ',', ' ').' kg CO₂e' : '—' }}</dd>
<dt class="col-sm-4">Eau</dt><dd class="col-sm-8">{{ $t->eau_litres !== null ? number_format($t->eau_litres, 2, ',', ' ').' L' : '—' }}</dd>
<dt class="col-sm-4">Énergie</dt><dd class="col-sm-8">{{ $t->energie_kwh !== null ? number_format($t->energie_kwh, 2, ',', ' ').' kWh' : '—' }}</dd>
<dt class="col-sm-4">Allégation</dt><dd class="col-sm-8">{{ $t->allegation ? '« '.$t->allegation.' »' : '—' }}@if ($t->verdict_allegation) <span class="badge {{ $t->verdict_allegation === 'fiable' ? 'bg-success' : ($t->verdict_allegation === 'non_evaluee' ? 'bg-secondary' : 'bg-danger') }}">{{ \App\Models\Transformation::VERDICTS[$t->verdict_allegation] }}</span>@endif</dd>
</dl></div></div></div>
<div class="col-lg-5 mb-4"><div class="card h-100"><div class="card-body text-center">
<h2 class="h6 text-muted">Éco-score de l’étape</h2>
<x-eco-score :score="$t->eco_score" size="lg" class="my-2" />
<p class="mb-2">{{ $t->intensite() !== null ? number_format($t->intensite(), 3, ',', ' ').' kg CO₂e par kg transformé' : 'Empreinte non renseignée' }}</p>
<p class="small text-muted mb-0">Barème : A ≤ 0,2 · B ≤ 0,5 · C ≤ 1 · D ≤ 2 · E &gt; 2 kg CO₂e/kg</p>
</div></div></div>
</div>

@if ($t->analyse_ia)
<div class="card mb-4"><div class="card-body"><h2 class="h6"><i class="bi bi-stars" aria-hidden="true"></i> Analyse IA</h2><p class="mb-0" style="white-space:pre-line">{{ $t->analyse_ia }}</p></div></div>
@endif
<a href="{{ route('admin.transformations.index') }}" class="btn btn-light">← Retour à la liste</a>
@endsection
