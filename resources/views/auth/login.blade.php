@extends('layouts.layout')
@section('title', 'Connexion — NutriTrace')
@section('body_class', 'main-layout inner_page')
@section('content')
<section class="container py-5"><div class="row justify-content-center"><div class="col-md-6 col-lg-5"><div class="card shadow-sm"><div class="card-body p-4">
<h1 class="h2 mb-3">Connexion</h1><p class="text-muted mb-4">Bienvenue sur NutriTrace.</p>
<form method="POST" action="{{ route('login.store') }}">@include('auth.fields', ['buttonClass' => 'btn-success'])</form>
<p class="mt-4">Pas encore de compte ? <a class="text-success" href="{{ route('register') }}">Créer un compte</a></p>
<a class="small text-muted" href="{{ route('admin.login') }}">Connexion administrateur</a>
</div></div></div></div></section>
@endsection
