@extends('layouts.layout')
@section('title', 'Mon compte — NutriTrace')
@section('body_class', 'main-layout inner_page')
@section('content')
<section class="container py-5">
    <h1>Mon compte</h1>
    <p>Bienvenue, {{ auth('web')->user()->name }}. Votre adresse e-mail est vérifiée.</p>
    <a class="btn btn-success" href="{{ route('profile.edit') }}">Modifier mon profil</a>
    <a class="btn btn-outline-success" href="{{ route('catalog.index') }}">Consulter le catalogue</a>
</section>
@endsection
