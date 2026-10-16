@extends('layouts.layout')
@section('title', 'Mon profil — NutriTrace')
@section('body_class', 'main-layout inner_page')
@section('content')
<section class="container py-5"><div class="row justify-content-center"><div class="col-md-8"><div class="card"><div class="card-body p-4">
<h1 class="h2">Mon profil</h1>
@if ($errors->any())<div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@if(session('status'))<div class="alert alert-success">{{ session('status') === 'profile-updated' ? 'Profil enregistré.' : session('status') }}</div>@endif
<form method="POST" action="{{ route('profile.update') }}">@csrf @method('PATCH')<div class="mb-3"><label for="name">Nom complet</label><input class="form-control" id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required></div><div class="mb-3"><label for="username">Nom d’utilisateur</label><input class="form-control" id="username" name="username" type="text" value="{{ old('username', $user->username) }}" required></div><div class="mb-3"><label for="phone">Téléphone</label><input class="form-control" id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" required></div><div class="mb-3"><label for="email">Adresse e-mail</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required></div><button class="btn btn-success">Enregistrer le profil</button></form>
@if(!$user->hasVerifiedEmail())<p class="mt-3">Votre adresse e-mail n’est pas vérifiée.</p><form method="POST" action="{{ route('verification.send') }}">@csrf<button class="btn btn-outline-success">Envoyer le lien de vérification</button></form>@endif
<hr><h2 class="h4">Modifier le mot de passe</h2>
@if(session('status') === 'password-updated')<p>Mot de passe modifié.</p>@endif
@foreach($errors->updatePassword->all() as $error)<p class="text-danger">{{ $error }}</p>@endforeach
<form method="POST" action="{{ route('password.update') }}">@csrf @method('PUT')<div class="mb-3"><label for="current_password">Mot de passe actuel</label><input class="form-control" id="current_password" name="current_password" type="password" value="" required></div><div class="mb-3"><label for="password">Mot de passe</label><input class="form-control" id="password" name="password" type="password" value="" required></div><div class="mb-3"><label for="password_confirmation">Confirmation</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" value="" required></div><button class="btn btn-success">Modifier le mot de passe</button></form>
<hr><h2 class="h4">Supprimer mon compte</h2><p>Cette suppression est définitive. Confirmez avec votre mot de passe.</p>
@foreach($errors->userDeletion->all() as $error)<p class="text-danger">{{ $error }}</p>@endforeach
<form method="POST" action="{{ route('profile.destroy') }}">@csrf @method('DELETE')<label for="delete_password">Mot de passe</label><input class="form-control mb-3" id="delete_password" name="password" type="password" required><button class="btn btn-danger">Supprimer mon compte</button></form>
</div></div></div></div></section>
@endsection
