@extends('admin.layouts.layout')

@section('title', ($certification->exists ? 'Modifier' : 'Ajouter') . ' une certification — NutriTrace Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">{{ $certification->exists ? 'Modifier la certification' : 'Ajouter une certification' }}</h1>
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.certifications.index') }}">
        <i class="bi bi-arrow-left me-1"></i> Retour à la liste
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ $certification->exists ? route('admin.certifications.update', $certification) : route('admin.certifications.store') }}">
            @csrf
            @if ($certification->exists)
                @method('PUT')
            @endif

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="label_id" class="form-label font-weight-bold">Label associé *</label>
                    <select id="label_id" name="label_id" class="form-select @error('label_id') is-invalid @enderror" required>
                        <option value="">-- Sélectionner un label --</option>
                        @foreach ($labels as $label)
                            <option value="{{ $label->id }}" {{ old('label_id', $certification->label_id) == $label->id ? 'selected' : '' }}>
                                {{ $label->nom }} {{ $label->organisme ? '(' . $label->organisme . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('label_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="nom" class="form-label font-weight-bold">Nom de la certification *</label>
                    <input type="text" id="nom" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom', $certification->nom) }}" placeholder="Ex: Certification Biologique AB" required maxlength="255">
                    @error('nom')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label for="numero_certificat" class="form-label font-weight-bold">Numéro de certificat *</label>
                    <input type="text" id="numero_certificat" name="numero_certificat" class="form-control @error('numero_certificat') is-invalid @enderror" value="{{ old('numero_certificat', $certification->numero_certificat) }}" placeholder="Ex: CERT-2026-8942" required maxlength="255">
                    @error('numero_certificat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="date_obtention" class="form-label font-weight-bold">Date d'obtention *</label>
                    <input type="date" id="date_obtention" name="date_obtention" class="form-control @error('date_obtention') is-invalid @enderror" value="{{ old('date_obtention', $certification->date_obtention ? \Carbon\Carbon::parse($certification->date_obtention)->format('Y-m-d') : '') }}" required>
                    @error('date_obtention')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="date_expiration" class="form-label font-weight-bold">Date d'expiration</label>
                    <input type="date" id="date_expiration" name="date_expiration" class="form-control @error('date_expiration') is-invalid @enderror" value="{{ old('date_expiration', $certification->date_expiration ? \Carbon\Carbon::parse($certification->date_expiration)->format('Y-m-d') : '') }}">
                    @error('date_expiration')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="statut" class="form-label font-weight-bold">Statut *</label>
                    <select id="statut" name="statut" class="form-select @error('statut') is-invalid @enderror" required>
                        <option value="valide" {{ old('statut', $certification->statut) === 'valide' ? 'selected' : '' }}>Valide</option>
                        <option value="expiree" {{ old('statut', $certification->statut) === 'expiree' ? 'selected' : '' }}>Expirée</option>
                        <option value="suspendue" {{ old('statut', $certification->statut) === 'suspendue' ? 'selected' : '' }}>Suspendue</option>
                    </select>
                    @error('statut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="description" class="form-label font-weight-bold">Description / Observations</label>
                <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="4" placeholder="Description détaillée du certificat, portée, etc.">{{ old('description', $certification->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> {{ $certification->exists ? 'Enregistrer les modifications' : 'Créer la certification' }}
                </button>
                <a class="btn btn-light border" href="{{ route('admin.certifications.index') }}">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
