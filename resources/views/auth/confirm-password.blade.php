@extends('layouts.layout')
@section('title', 'Confirmer votre mot de passe — NutriTrace')
@section('body_class', 'main-layout inner_page')
@section('content')
<section class="container py-5"><div class="row justify-content-center"><div class="col-md-8"><div class="card"><div class="card-body p-4">
<h1 class="h2">Confirmer votre mot de passe</h1>
@if ($errors->any())<div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@if(session('status'))<div class="alert alert-success">{{ session('status') === 'profile-updated' ? 'Profil enregistré.' : session('status') }}</div>@endif
<form method="POST" action="{{ route('password.confirm') }}">@csrf<div class="mb-3"><label for="password">Mot de passe</label><input class="form-control" id="password" name="password" type="password" value="" required></div><button class="btn btn-success">Confirmer</button></form>
</div></div></div></div></section>
@endsection
