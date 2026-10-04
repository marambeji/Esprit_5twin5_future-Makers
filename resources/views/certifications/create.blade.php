@extends('layouts.layout')

@section('title', 'Ajouter une Certification — Nutritrace')
@section('body_class', 'main-layout inner_page')

@section('content')
<div class="container py-5 mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 font-weight-bold text-success mb-0">Nouvelle Certification</h1>
                <a href="{{ route('certifications.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Retour à la liste
                </a>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger shadow-sm">
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card border-0 shadow-sm rounded-lg p-4">
                <form method="POST" action="{{ route('certifications.store') }}">
                    @csrf

                    <div class="form-group mb-3">
                        <label for="label_id" class="font-weight-bold">Label associé <span class="text-danger">*</span></label>
                        <select name="label_id" id="label_id" class="form-control @error('label_id') is-invalid @enderror" required>
                            <option value="">-- Sélectionner un label --</option>
                            @foreach ($labels as $label)
                                <option value="{{ $label->id }}" {{ old('label_id') == $label->id ? 'selected' : '' }}>
                                    {{ $label->nom }} {{ $label->organisme ? '('.$label->organisme.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('label_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="nom" class="font-weight-bold">Nom de la certification <span class="text-danger">*</span></label>
                        <input type="text" name="nom" id="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom') }}" placeholder="Ex: Certification Bio Ferme A" required>
                        @error('nom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="numero_certificat" class="font-weight-bold">Numéro de certificat <span class="text-danger">*</span></label>
                            <input type="text" name="numero_certificat" id="numero_certificat" class="form-control @error('numero_certificat') is-invalid @enderror" value="{{ old('numero_certificat') }}" placeholder="Ex: CERT-2026-001" required>
                            @error('numero_certificat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 form-group mb-3">
                            <label for="statut" class="font-weight-bold">Statut <span class="text-danger">*</span></label>
                            <select name="statut" id="statut" class="form-control @error('statut') is-invalid @enderror" required>
                                <option value="valide" {{ old('statut', 'valide') === 'valide' ? 'selected' : '' }}>Valide</option>
                                <option value="expiree" {{ old('statut') === 'expiree' ? 'selected' : '' }}>Expirée</option>
                                <option value="suspendue" {{ old('statut') === 'suspendue' ? 'selected' : '' }}>Suspendue</option>
                            </select>
                            @error('statut')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="date_obtention" class="font-weight-bold">Date d'obtention <span class="text-danger">*</span></label>
                            <input type="date" name="date_obtention" id="date_obtention" class="form-control @error('date_obtention') is-invalid @enderror" value="{{ old('date_obtention') }}" required>
                            @error('date_obtention')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 form-group mb-3">
                            <label for="date_expiration" class="font-weight-bold">Date d'expiration</label>
                            <input type="date" name="date_expiration" id="date_expiration" class="form-control @error('date_expiration') is-invalid @enderror" value="{{ old('date_expiration') }}">
                            @error('date_expiration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="description" class="font-weight-bold">Description</label>
                        <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Informations complémentaires...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('certifications.index') }}" class="btn btn-light mr-2">Annuler</a>
                        <button type="submit" class="btn btn-success px-4 font-weight-bold">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
