@extends('admin.layouts.layout')
@section('title', 'Utilisateur — NutriTrace')
@section('content')
<h1 class="h3 mb-4">{{ $user->exists ? 'Modifier un utilisateur' : 'Ajouter un utilisateur' }}</h1><div class="card"><div class="card-body">
<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">@csrf @if($user->exists) @method('PUT') @endif
@foreach (['name' => 'Nom complet', 'email' => 'Adresse e-mail'] as $field => $label)<div class="mb-3"><label for="{{ $field }}" class="form-label">{{ $label }}</label><input id="{{ $field }}" name="{{ $field }}" type="{{ $field === 'email' ? 'email' : 'text' }}" class="form-control @error($field) is-invalid @enderror" value="{{ old($field, $user->$field) }}" required maxlength="255">@error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror</div>@endforeach
<div class="mb-3"><label for="role" class="form-label">Rôle</label><select id="role" name="role" class="form-select @error('role') is-invalid @enderror"><option value="user" @selected(old('role', $user->role) === 'user')>Utilisateur</option><option value="admin" @selected(old('role', $user->role) === 'admin')>Administrateur</option></select>@error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label for="password" class="form-label">Mot de passe {{ $user->exists ? '(laisser vide pour conserver)' : '' }}</label><input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" minlength="8" @required(!$user->exists)>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-4"><label for="password_confirmation" class="form-label">Confirmation</label><input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password"></div>
<button class="btn btn-primary">Enregistrer</button> <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">Annuler</a>
</form></div></div>
@endsection
