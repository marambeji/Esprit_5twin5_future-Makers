@extends('layouts.layout')
@section('title', 'Mot de passe oublié — NutriTrace')
@section('body_class', 'main-layout inner_page')
@section('content')
<section class="container py-5"><div class="row justify-content-center"><div class="col-md-8"><div class="card"><div class="card-body p-4">
<h1 class="h2">Mot de passe oublié</h1>
@if ($errors->any())<div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@if(session('status'))<div class="alert alert-success">{{ session('status') === 'profile-updated' ? 'Profil enregistré.' : session('status') }}</div>@endif
<p>Indiquez votre adresse e-mail pour recevoir un lien de réinitialisation.</p><form method="POST" action="{{ route('password.email') }}">@csrf<div class="mb-3"><label for="email">Adresse e-mail</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" required></div><button class="btn btn-success">Envoyer le lien</button></form>
</div></div></div></div></section>
@endsection
