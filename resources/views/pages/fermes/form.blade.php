@extends('layouts.layout')
@section('title', $ferme->exists ? 'Modifier une ferme — NutriTrace' : 'Ajouter une ferme — NutriTrace')
@section('body_class', 'main-layout inner_page')
@php($rawSuperficie = $ferme->getAttributes()['superficie'] ?? null)

@section('content')
<section class="container py-5 fermes-page">
    <div class="titlepage text_align_left mb-4">
        <span>Votre exploitation</span>
        <h1>{{ $ferme->exists ? 'MODIFIER MA FERME' : 'AJOUTER UNE FERME' }}</h1>
        <p>La ferme sera rattachée à votre compte producteur vérifié.</p>
    </div>
    @include('partials.flash')
    <div class="card border-0 shadow-sm" style="border-radius:12px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ $ferme->exists ? route('fermes.update', $ferme) : route('fermes.store') }}">
                @csrf
                @if ($ferme->exists) @method('PUT') @endif
                <input type="hidden" name="producteur_id" value="{{ $producer->id }}">
                <div class="mb-3"><label for="nom" class="form-label">Nom de la ferme *</label><input id="nom" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom', $ferme->nom) }}" required maxlength="150">@error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="mb-3"><label for="localisation" class="form-label">Localisation *</label><input id="localisation" name="localisation" class="form-control @error('localisation') is-invalid @enderror" value="{{ old('localisation', $ferme->localisation) }}" required maxlength="255">@error('localisation')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label for="latitude" class="form-label">Latitude</label><input id="latitude" name="latitude" type="number" step="0.0000001" min="-90" max="90" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude', $ferme->latitude) }}">@error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6 mb-3"><label for="longitude" class="form-label">Longitude</label><input id="longitude" name="longitude" type="number" step="0.0000001" min="-180" max="180" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude', $ferme->longitude) }}">@error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                </div>
                @include('pages.fermes._plain-map', ['mapMode' => 'form'])
                <div class="mb-3 mt-4"><label for="superficie" class="form-label">Superficie déclarée (ha)</label><input id="superficie" name="superficie" type="number" step="0.01" min="0" class="form-control @error('superficie') is-invalid @enderror" value="{{ old('superficie', $rawSuperficie) }}">@error('superficie')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="mb-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2"><div class="d-flex flex-wrap align-items-center gap-2"><label for="description" class="form-label mb-0">Description</label><button type="button" class="btn btn-sm btn-outline-primary" data-ai-generate>Générer automatiquement</button></div><span id="description-count" class="small text-muted" aria-live="polite">0 / 2000 caractères · 0 mot</span></div>
                    <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="4" maxlength="2000" aria-describedby="description-count">{{ old('description', $ferme->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                @include('pages.fermes._ai-description', ['endpoint' => route('fermes.generate-description')])
                <button class="btn btn-success">Enregistrer</button>
                <a class="btn btn-outline-secondary" href="{{ $ferme->exists ? route('fermes.show', $ferme) : route('fermes.index') }}">Annuler</a>
            </form>
        </div>
    </div>
</section>
@endsection
