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
        <form method="POST" action="{{ $certification->exists ? route('admin.certifications.update', $certification) : route('admin.certifications.store') }}" enctype="multipart/form-data">
            @csrf
            @if ($certification->exists)
                @method('PUT')
            @endif

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label for="label_id" class="form-label font-weight-bold mb-0">Label associé *</label>
                        <a href="{{ route('admin.labels.create') }}" class="small text-decoration-none" target="_blank" title="Créer un nouveau label">
                            <i class="bi bi-plus-circle me-1"></i> Nouveau label
                        </a>
                    </div>
                    <select id="label_id" name="label_id" class="form-select @error('label_id') is-invalid @enderror" required>
                        <option value="">-- Sélectionner un label --</option>
                        @foreach ($labels as $label)
                            <option value="{{ $label->id }}" {{ (string) old('label_id', $certification->label_id) === (string) $label->id ? 'selected' : '' }}>
                                {{ $label->nom }} {{ $label->organisme ? '(' . $label->organisme . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @if ($labels->isEmpty())
                        <div class="alert alert-warning small py-2 px-3 mt-2 mb-0">
                            <i class="bi bi-exclamation-triangle me-1"></i> Aucun label n'est disponible.
                            <a href="{{ route('admin.labels.create') }}" class="fw-bold alert-link">Créez un premier label</a> pour pouvoir enregistrer une certification.
                        </div>
                    @endif
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

                <div class="col-md-6">
                    <label for="document" class="form-label font-weight-bold">Fichier du certificat (Photo ou PDF)</label>
                    <input type="file" id="document" name="document" class="form-control @error('document') is-invalid @enderror" accept=".pdf,image/png,image/jpeg,image/webp">
                    <div class="form-text">Formats acceptés : PDF, JPG, PNG, WEBP (Max 15 Mo). Affiché lors du scan du QR Code.</div>
                    @error('document')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    @if ($certification->has_document)
                        <div class="mt-2 p-2 bg-light border rounded d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                @if ($certification->is_pdf)
                                    <i class="bi bi-file-earmark-pdf-fill text-danger fs-4 me-2"></i>
                                @else
                                    <i class="bi bi-file-earmark-image-fill text-primary fs-4 me-2"></i>
                                @endif
                                <div>
                                    <a href="{{ $certification->document_url }}" target="_blank" class="fw-bold text-decoration-none small">
                                        Voir le document actuel <i class="bi bi-box-arrow-up-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="form-check ms-3">
                                <input class="form-check-input" type="checkbox" name="remove_document" value="1" id="remove_document">
                                <label class="form-check-label small text-danger" for="remove_document">
                                    Supprimer
                                </label>
                            </div>
                        </div>
                    @endif
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
