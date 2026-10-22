@extends('admin.layouts.layout')
@section('title', 'Producteur — NutriTrace')
@section('content')
<h1 class="h3 mb-4">{{ $producteur->exists ? 'Modifier le producteur' : 'Ajouter un producteur' }}</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ $producteur->exists ? route('admin.producteurs.update', $producteur) : route('admin.producteurs.store') }}">
@csrf @if ($producteur->exists) @method('PUT') @endif
<div class="mb-3"><label for="nom" class="form-label">Nom *</label><input id="nom" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom', $producteur->nom) }}" required maxlength="100">@error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label for="prenom" class="form-label">Prénom *</label><input id="prenom" name="prenom" class="form-control @error('prenom') is-invalid @enderror" value="{{ old('prenom', $producteur->prenom) }}" required maxlength="100">@error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label for="email" class="form-label">Adresse e-mail *</label><input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $producteur->email) }}" required maxlength="255">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label for="telephone" class="form-label">Téléphone</label><input id="telephone" name="telephone" class="form-control @error('telephone') is-invalid @enderror" value="{{ old('telephone', $producteur->telephone) }}" maxlength="30">@error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label for="adresse" class="form-label">Adresse</label><textarea id="adresse" name="adresse" class="form-control @error('adresse') is-invalid @enderror" rows="3" maxlength="500">{{ old('adresse', $producteur->adresse) }}</textarea>@error('adresse')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<button class="btn btn-primary">Enregistrer</button><a class="btn btn-light" href="{{ route('admin.producteurs.index') }}">Annuler</a>
</form></div></div>
@endsection
