@extends('layouts.layout')
@section('title', $transformateur->nom.' — NutriTrace')
@section('body_class', 'main-layout inner_page')

@section('content')
@php($intensite = $transformateur->intensiteMoyenne())
<section class="container py-5">
    <a class="text-success d-inline-block mb-4" href="{{ route('transformateurs.index') }}">← Retour aux transformateurs</a>

    <div class="row align-items-start mb-5">
        <div class="col-lg-4 mb-4 mb-lg-0">
            <div style="background:#f6f7f1;border-radius:12px;padding:2.5rem;text-align:center;">
                <x-eco-score :score="\App\Models\Transformation::scoreFor($intensite)" size="lg" />
                <p class="mt-3 mb-0"><strong>{{ $intensite !== null ? number_format($intensite, 2, ',', ' ').' kg CO₂e' : 'Non renseignée' }}</strong></p>
                <p class="small text-muted mb-0">empreinte moyenne par kg transformé</p>
            </div>
        </div>
        <div class="col-lg-8 pl-lg-4">
            <h1 class="h2 mb-4">{{ $transformateur->nom }}</h1>
            <div class="border-top border-bottom py-3 mb-4">
                <p class="mb-2"><i class="fa fa-cogs" aria-hidden="true" style="color:#7aac2b;width:20px;"></i> <strong>Activité :</strong> {{ $transformateur->type_label }}</p>
                <p class="mb-2"><i class="fa fa-map-marker" aria-hidden="true" style="color:#7aac2b;width:20px;"></i> <strong>Adresse :</strong> {{ $transformateur->adresse }}, {{ $transformateur->ville }}</p>
                <p class="mb-0"><i class="fa fa-envelope" aria-hidden="true" style="color:#7aac2b;width:20px;"></i> <strong>Contact :</strong> {{ $transformateur->email }} · {{ $transformateur->telephone }}</p>
            </div>
            @if ($transformateur->description)<p style="white-space:pre-line;overflow-wrap:anywhere;">{{ $transformateur->description }}</p>@endif
        </div>
    </div>

    <h2 class="h4 mb-3">Étapes de transformation</h2>
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Date</th><th>Produit</th><th>Origine</th><th>Procédé</th><th>Quantité</th><th>CO₂</th><th>Eau</th><th>Énergie</th><th>Éco-score</th></tr></thead>
            <tbody>
            @forelse ($transformateur->transformations as $t)
            <tr>
                <td>{{ $t->date_transformation->format('d/m/Y') }}</td>
                <td><a class="text-success" href="{{ route('catalog.show', $t->product) }}">{{ $t->product->name }}</a></td>
                <td>{{ $t->ferme?->nom ?? '—' }}</td>
                <td>{{ $t->procede_label }}</td>
                <td>{{ number_format($t->quantite_kg, 0, ',', ' ') }} kg</td>
                <td>{{ $t->co2_kg !== null ? number_format($t->co2_kg, 1, ',', ' ').' kg' : '—' }}</td>
                <td>{{ $t->eau_litres !== null ? number_format($t->eau_litres, 0, ',', ' ').' L' : '—' }}</td>
                <td>{{ $t->energie_kwh !== null ? number_format($t->energie_kwh, 0, ',', ' ').' kWh' : '—' }}</td>
                <td><x-eco-score :score="$t->eco_score" size="sm" />
                    @if (in_array($t->verdict_allegation, ['exageree', 'trompeuse'], true))
                    <div class="small text-danger mt-1" title="« {{ $t->allegation }} »"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> {{ \App\Models\Transformation::VERDICTS[$t->verdict_allegation] }}</div>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="9">Aucune étape de transformation publiée.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
