@extends('admin.layouts.layout')
@section('title', 'Ferme — NutriTrace')
@section('content')
<h1 class="h3 mb-4">{{ $ferme->exists ? 'Modifier la ferme' : 'Ajouter une ferme' }}</h1>
@if ($producteurs->isEmpty())<div class="alert alert-info">Créez un producteur avant d'ajouter une ferme. <a href="{{ route('admin.producteurs.create') }}">Ajouter un producteur</a></div>@endif
<div class="card"><div class="card-body"><form method="POST" action="{{ $ferme->exists ? route('admin.fermes.update', $ferme) : route('admin.fermes.store') }}">
@csrf @if ($ferme->exists) @method('PUT') @endif
<div class="mb-3"><label for="producteur_id" class="form-label">Producteur *</label><select id="producteur_id" name="producteur_id" class="form-select @error('producteur_id') is-invalid @enderror" required><option value="">Choisir un producteur</option>@foreach ($producteurs as $producteur)<option value="{{ $producteur->id }}" @selected(old('producteur_id', $ferme->producteur_id) == $producteur->id)>{{ $producteur->nom }} {{ $producteur->prenom }}</option>@endforeach</select>@error('producteur_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label for="nom" class="form-label">Nom de la ferme *</label><input id="nom" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom', $ferme->nom) }}" required maxlength="150">@error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label for="localisation" class="form-label">Localisation *</label><input id="localisation" name="localisation" class="form-control @error('localisation') is-invalid @enderror" value="{{ old('localisation', $ferme->localisation) }}" required maxlength="255">@error('localisation')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label for="superficie" class="form-label">Superficie (ha)</label><input id="superficie" name="superficie" type="number" step="0.01" min="0" class="form-control @error('superficie') is-invalid @enderror" value="{{ old('superficie', $ferme->superficie) }}">@error('superficie')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label for="description" class="form-label">Description</label><textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="4" maxlength="2000">{{ old('description', $ferme->description) }}</textarea>@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<button class="btn btn-primary" @disabled($producteurs->isEmpty())>Enregistrer</button><a class="btn btn-light" href="{{ route('admin.fermes.index') }}">Annuler</a>
</form></div></div>
@endsection
