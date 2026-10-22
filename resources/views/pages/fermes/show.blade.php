@extends('layouts.layout')
@php
    $rawSuperficie = $ferme->getAttributes()['superficie'] ?? null;
    $displaySuperficie = is_numeric($rawSuperficie) && is_finite((float) $rawSuperficie)
        ? number_format((float) $rawSuperficie, 2, ',', ' ').' ha'
        : ($rawSuperficie === null || $rawSuperficie === '' ? 'Non renseignée' : 'Valeur à vérifier');
@endphp
@section('title', $ferme->nom . ' — NutriTrace')
@section('body_class', 'main-layout inner_page')

@php
    $hasValidCoordinates = is_numeric($ferme->latitude) && is_numeric($ferme->longitude)
        && $ferme->latitude >= -90 && $ferme->latitude <= 90
        && $ferme->longitude >= -180 && $ferme->longitude <= 180;
    $hasMapContent = $hasValidCoordinates;
@endphp

@section('content')
<section class="container py-5">

    <a class="text-success d-inline-block mb-4" href="{{ route('fermes.index') }}">← Retour aux fermes</a>
    @include('partials.flash')

    <div class="row align-items-start">

        {{-- Icône / Visuel --}}
        <div class="col-lg-4 mb-4 mb-lg-0">
            <div style="background:#f6f7f1;border-radius:12px;padding:3rem;text-align:center;">
                <i class="fa fa-home" aria-hidden="true" style="font-size:96px;color:#7aac2b;"></i>
            </div>
        </div>

        {{-- Détails de la ferme --}}
        <div class="col-lg-8 pl-lg-4">
            <h1 class="h2 mb-4">{{ $ferme->nom }}</h1>

            <div class="border-top border-bottom py-3 mb-4">
                <p class="mb-2">
                    <i class="fa fa-map-marker" aria-hidden="true" style="color:#7aac2b;width:20px;"></i>
                    <strong>Localisation :</strong> {{ $ferme->localisation }}
                </p>
                <p class="mb-2">
                    <i class="fa fa-leaf" aria-hidden="true" style="color:#7aac2b;width:20px;"></i>
                    <strong>Superficie :</strong> {{ $displaySuperficie }}
                </p>
                @if ($ferme->producteur)
                <p class="mb-0">
                    <i class="fa fa-user-circle-o" aria-hidden="true" style="color:#7aac2b;width:20px;"></i>
                    <strong>Producteur :</strong>
                    <a href="{{ route('producteurs.show', $ferme->producteur) }}" class="text-success">
                        {{ $ferme->producteur->prenom }} {{ $ferme->producteur->nom }}
                    </a>
                </p>
                @endif
            </div>

            <div class="border rounded p-3 mb-4" aria-label="Qualité de la fiche">
                <div class="d-flex justify-content-between align-items-center mb-2"><strong>Qualité de la fiche</strong><span class="badge bg-{{ $dataQuality['score'] === 100 ? 'success' : 'warning' }}">{{ $dataQuality['score'] }} % complétée</span></div>
                @if ($dataQuality['missing'])<p class="mb-1"><strong>À compléter :</strong> {{ implode(', ', $dataQuality['missing']) }}.</p>@endif
                @if ($dataQuality['invalid'])<p class="mb-1 text-danger"><strong>Valeurs à vérifier :</strong> {{ implode(', ', $dataQuality['invalid']) }}.</p>@endif
                @foreach ($dataQuality['warnings'] as $warning)<p class="small text-warning-emphasis mb-1">{{ $warning }}</p>@endforeach
                @foreach ($dataQuality['recommendations'] as $recommendation)<p class="small text-muted mb-1">{{ $recommendation }}</p>@endforeach
                @if (! $dataQuality['missing'] && ! $dataQuality['invalid'] && ! $dataQuality['warnings'])<p class="small text-success mb-0">Les informations disponibles sont complètes.</p>@endif
            </div>

            @if ($ferme->description)
            <h2 class="h5 mb-2">Description</h2>
            <p style="white-space:pre-line;overflow-wrap:anywhere;">{{ $ferme->description }}</p>
            @endif

            <div class="mt-4">
                @if ($ferme->producteur)
                <a class="btn btn-success mb-2 mr-2" href="{{ route('producteurs.show', $ferme->producteur) }}">
                    <i class="fa fa-user" aria-hidden="true"></i> Voir le producteur
                </a>
                @endif
                <a class="btn btn-outline-success mb-2" href="{{ route('fermes.index') }}">
                    Toutes les fermes
                </a>
                @if ($canManage)
                    <a class="btn btn-success mb-2" href="{{ route('fermes.edit', $ferme) }}">Modifier ma ferme</a>
                    <form class="d-inline" method="POST" action="{{ route('fermes.destroy', $ferme) }}" onsubmit="return confirm('Supprimer définitivement cette ferme ?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger mb-2">Supprimer ma ferme</button>
                    </form>
                @endif
            </div>
        </div>

    </div>

    @if ($hasMapContent)
        <div class="mt-5">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h2 class="h4 mb-0">Emplacement enregistré</h2>
                @if ($hasValidCoordinates)
                    <a class="btn btn-sm btn-outline-success" target="_blank" rel="noopener noreferrer" href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($ferme->latitude.','.$ferme->longitude) }}">Ouvrir dans Google Maps</a>
                @endif
            </div>
            @include('pages.fermes._plain-map', ['mapMode' => 'show'])
        </div>
    @endif

</section>
@endsection
