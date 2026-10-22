@extends('admin.layouts.layout')
@section('title', 'Transformateur — NutriTrace')
@section('content')
<h1 class="h3 mb-4">{{ $transformateur->exists ? 'Modifier le transformateur' : 'Ajouter un transformateur' }}</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ $transformateur->exists ? route('admin.transformateurs.update', $transformateur) : route('admin.transformateurs.store') }}" novalidate>
@csrf @if ($transformateur->exists) @method('PUT') @endif
<div class="row">
<div class="col-md-8 mb-3"><label for="nom" class="form-label">Nom *</label><input id="nom" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom', $transformateur->nom) }}" required minlength="2" maxlength="150">@error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="col-md-4 mb-3"><label for="type_activite" class="form-label">Type d’activité *</label><select id="type_activite" name="type_activite" class="form-select @error('type_activite') is-invalid @enderror" required><option value="">Choisir</option>@foreach (\App\Models\Transformateur::TYPES as $key => $label)<option value="{{ $key }}" @selected(old('type_activite', $transformateur->type_activite) === $key)>{{ $label }}</option>@endforeach</select>@error('type_activite')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="col-md-6 mb-3"><label for="email" class="form-label">E-mail *</label><input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $transformateur->email) }}" required maxlength="255">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="col-md-6 mb-3"><label for="telephone" class="form-label">Téléphone *</label><input id="telephone" name="telephone" class="form-control @error('telephone') is-invalid @enderror" value="{{ old('telephone', $transformateur->telephone) }}" required placeholder="+216 71 000 000" pattern="\+?[0-9 ]{8,20}">@error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="col-md-4 mb-3"><label for="ville" class="form-label">Ville *</label><input id="ville" name="ville" class="form-control @error('ville') is-invalid @enderror" value="{{ old('ville', $transformateur->ville) }}" required maxlength="100">@error('ville')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="col-md-8 mb-3"><label for="adresse" class="form-label">Adresse *</label><input id="adresse" name="adresse" class="form-control @error('adresse') is-invalid @enderror" value="{{ old('adresse', $transformateur->adresse) }}" required maxlength="255">@error('adresse')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="col-12 mb-3"><label for="description" class="form-label">Description</label><textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="4" maxlength="2000">{{ old('description', $transformateur->description) }}</textarea>@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
</div>
<button class="btn btn-primary">Enregistrer</button> <a class="btn btn-light" href="{{ $transformateur->exists ? route('admin.transformateurs.show', $transformateur) : route('admin.transformateurs.index') }}">Annuler</a>
</form></div></div>
@endsection
