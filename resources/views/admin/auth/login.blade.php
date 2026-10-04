@extends('admin.layouts.auth')
@section('title', 'Connexion administrateur — NutriTrace')
@section('content')
<a href="{{ route('home') }}" class="nutritrace-logo d-block text-center mb-4">NutriTrace</a>
<div class="card shadow"><div class="card-body p-4 p-lg-5"><h1 class="h3 mb-3">Connexion administrateur</h1><p class="text-muted mb-4">Connectez-vous pour gérer la plateforme.</p>
<form method="POST" action="{{ route('admin.login.store') }}">@include('auth.fields', ['buttonClass' => 'btn-primary'])</form>
<a class="d-block mt-4 text-center" href="{{ route('login') }}">Retour à la connexion du site</a>
</div></div>
@endsection
