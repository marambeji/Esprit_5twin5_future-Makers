@extends('layouts.layout')
@section('title', 'Réinitialiser le mot de passe — NutriTrace')
@section('body_class', 'main-layout inner_page')
@section('content')
<section class="container py-5"><div class="row justify-content-center"><div class="col-md-8"><div class="card"><div class="card-body p-4">
<h1 class="h2">Réinitialiser le mot de passe</h1>
@if ($errors->any())<div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@if(session('status'))<div class="alert alert-success">{{ session('status') === 'profile-updated' ? 'Profil enregistré.' : session('status') }}</div>@endif
<form method="POST" action="{{ route('password.store') }}">@csrf<input type="hidden" name="token" value="{{ $request->route('token') }}"><div class="mb-3"><label for="email">Adresse e-mail</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required></div><div class="mb-3"><label for="password">Mot de passe</label><input class="form-control" id="password" name="password" type="password" value="" required></div><div class="mb-3"><label for="password_confirmation">Confirmer le mot de passe</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" value="" required></div><button class="btn btn-success">Enregistrer</button></form>
</div></div></div></div></section>
@endsection
