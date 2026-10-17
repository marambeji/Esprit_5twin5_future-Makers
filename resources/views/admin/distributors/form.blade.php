@extends('admin.layouts.layout')
@section('title', 'Distributeur — NutriTrace')
@section('content')
<h1 class="h3 mb-4">{{ $distributor->exists ? 'Modifier le distributeur (livreur)' : 'Ajouter un distributeur (livreur)' }}</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ $distributor->exists ? route('admin.distributors.update', $distributor) : route('admin.distributors.store') }}">
@csrf @if ($distributor->exists) @method('PUT') @endif
<div class="mb-3"><label for="user_id" class="form-label">Compte utilisateur du distributeur (livreur) {{ $distributor->exists && ! $distributor->user_id ? '' : '*' }}</label><select id="user_id" name="user_id" class="form-select @error('user_id') is-invalid @enderror" @required(! $distributor->exists || $distributor->user_id)><option value="">Choisir…</option>@foreach ($users as $user)<option value="{{ $user->id }}" @selected(old('user_id', $distributor->user_id) == $user->id)>{{ $user->name }} ({{ $user->email }})</option>@endforeach</select><div class="form-text">Le nom et l’e-mail du distributeur sont repris de ce compte. Le livreur se connecte avec ce compte et accède à « Mes livraisons ».</div>@error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
@if ($distributor->exists && ! $distributor->user_id)<p class="text-muted">Distributeur sans compte : {{ $distributor->name }} ({{ $distributor->email }}). Choisissez un compte pour le relier.</p>@endif
@foreach (['phone' => ['Téléphone (facultatif si le compte en a un)', 'text', 30], 'city' => ['Ville *', 'text', 100], 'address' => ['Adresse *', 'text', 255]] as $field => [$label, $type, $max])
<div class="mb-3"><label for="{{ $field }}" class="form-label">{{ $label }}</label><input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" class="form-control @error($field) is-invalid @enderror" value="{{ old($field, $distributor->$field) }}" maxlength="{{ $max }}" @required($field !== 'phone')>@error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
@endforeach
<div class="form-check mb-3"><input type="checkbox" id="actif" name="actif" value="1" class="form-check-input" @checked(old('actif', $distributor->actif))><label for="actif" class="form-check-label">Distributeur actif</label></div>
<div class="mb-3"><label for="description" class="form-label">Description</label><textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="4" maxlength="2000">{{ old('description', $distributor->description) }}</textarea>@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<button class="btn btn-primary">Enregistrer</button><a class="btn btn-light" href="{{ route('admin.distributors.index') }}">Annuler</a>
</form></div></div>
@endsection
