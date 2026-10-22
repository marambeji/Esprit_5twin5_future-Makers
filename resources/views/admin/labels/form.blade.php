@extends('admin.layouts.layout')

@section('title', ($label->exists ? 'Modifier' : 'Ajouter') . ' un label — NutriTrace Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">{{ $label->exists ? 'Modifier le label' : 'Ajouter un label' }}</h1>
        <p class="text-muted mb-0 small">Les labels créés apparaîtront dans la liste déroulante lors de l'ajout d'une certification.</p>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.labels.index') }}">
        <i class="bi bi-arrow-left me-1"></i> Retour à la liste
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ $label->exists ? route('admin.labels.update', $label) : route('admin.labels.store') }}">
            @csrf
            @if ($label->exists)
                @method('PUT')
            @endif

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="nom" class="form-label font-weight-bold">Nom du label *</label>
                    <input type="text"
                           id="nom"
                           name="nom"
                           class="form-control @error('nom') is-invalid @enderror"
                           value="{{ old('nom', $label->nom) }}"
                           placeholder="Ex: Agriculture Biologique (AB), Fairtrade, ISO 22000..."
                           required
                           maxlength="255">
                    <div class="form-text">Nom officiel du label ou de la norme de traçabilité.</div>
                    @error('nom')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="organisme" class="form-label font-weight-bold">Organisme certificateur</label>
                    <input type="text"
                           id="organisme"
                           name="organisme"
                           class="form-control @error('organisme') is-invalid @enderror"
                           value="{{ old('organisme', $label->organisme) }}"
                           placeholder="Ex: Ecocert, Ministère de l'Agriculture, Bureau Veritas..."
                           maxlength="255">
                    <div class="form-text">Organisme responsable de l'attribution et du contrôle.</div>
                    @error('organisme')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="description" class="form-label font-weight-bold">Description du label</label>
                <textarea id="description"
                          name="description"
                          class="form-control @error('description') is-invalid @enderror"
                          rows="4"
                          placeholder="Ex: Cahier des charges, critères écologiques ou éthiques requis pour l'obtention de ce label...">{{ old('description', $label->description) }}</textarea>
                <div class="form-text">Détails sur les exigences et la portée du label.</div>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> {{ $label->exists ? 'Enregistrer les modifications' : 'Créer le label' }}
                </button>
                <a class="btn btn-light border" href="{{ route('admin.labels.index') }}">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
