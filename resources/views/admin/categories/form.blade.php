@extends('admin.layouts.layout')
@section('title', 'Catégorie — NutriTrace')
@section('content')
<h1 class="h3 mb-4">{{ $category->exists ? 'Modifier la catégorie' : 'Ajouter une catégorie' }}</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
@csrf @if ($category->exists) @method('PUT') @endif
<div class="mb-3"><label for="name" class="form-label">Nom *</label><input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}" required maxlength="100">@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label for="description" class="form-label">Description</label><textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="4" maxlength="2000">{{ old('description', $category->description) }}</textarea>@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<button class="btn btn-primary">Enregistrer</button><a class="btn btn-light" href="{{ route('admin.categories.index') }}">Annuler</a>
</form></div></div>
@endsection
