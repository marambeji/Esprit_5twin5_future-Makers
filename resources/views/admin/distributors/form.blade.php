@extends('admin.layouts.layout')
@section('title', 'Distributeur — NutriTrace')
@section('content')
<h1 class="h3 mb-4">{{ $distributor->exists ? 'Modifier le distributeur' : 'Ajouter un distributeur' }}</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ $distributor->exists ? route('admin.distributors.update', $distributor) : route('admin.distributors.store') }}">
@csrf @if ($distributor->exists) @method('PUT') @endif
@foreach (['name' => ['Nom *', 'text', 150], 'email' => ['Adresse e-mail *', 'email', 150], 'phone' => ['Téléphone *', 'text', 30], 'city' => ['Ville *', 'text', 100], 'address' => ['Adresse *', 'text', 255]] as $field => [$label, $type, $max])
<div class="mb-3"><label for="{{ $field }}" class="form-label">{{ $label }}</label><input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" class="form-control @error($field) is-invalid @enderror" value="{{ old($field, $distributor->$field) }}" required maxlength="{{ $max }}">@error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
@endforeach
<div class="mb-3"><label for="description" class="form-label">Description</label><textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="4" maxlength="2000">{{ old('description', $distributor->description) }}</textarea>@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<button class="btn btn-primary">Enregistrer</button><a class="btn btn-light" href="{{ route('admin.distributors.index') }}">Annuler</a>
</form></div></div>
@endsection
