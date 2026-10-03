@extends('layouts.layout')
@section('title', 'Inscription — NutriTrace')
@section('body_class', 'main-layout inner_page')
@section('content')
<section class="container py-5"><div class="row justify-content-center"><div class="col-md-6"><div class="card shadow-sm"><div class="card-body p-4"><h1 class="h2 mb-4">Créer un compte</h1>
<form method="POST" action="{{ route('register.store') }}">@csrf
@if ($errors->any())<div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="mb-3"><label for="name">Nom complet</label><input id="name" name="name" class="form-control" value="{{ old('name') }}" required maxlength="255" autocomplete="name"></div>
<div class="mb-3"><label for="email">Adresse e-mail</label><input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}" required autocomplete="email"></div>
<div class="mb-3"><label for="password">Mot de passe (8 caractères minimum)</label><input id="password" name="password" type="password" class="form-control" required minlength="8" autocomplete="new-password"></div>
<div class="mb-4"><label for="password_confirmation">Confirmer le mot de passe</label><input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required autocomplete="new-password"></div>
<button class="btn btn-success w-100">Créer mon compte</button></form><p class="mt-4"><a class="text-success" href="{{ route('login') }}">Déjà inscrit ? Se connecter</a></p>
</div></div></div></div></section>
@endsection
