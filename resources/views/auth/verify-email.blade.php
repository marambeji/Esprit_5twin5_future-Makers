@extends('layouts.layout')
@section('title', 'Vérifier mon adresse e-mail — NutriTrace')
@section('body_class', 'main-layout inner_page')
@section('content')
<section class="container py-5">
    <h1>Vérifiez votre adresse e-mail</h1>
    @if(in_array(config('mail.default'), ['log', 'array']))
        <div class="alert alert-warning">L’envoi des e-mails est en mode test. Aucun message ne sera reçu dans votre boîte mail. L’envoi réel doit être configuré par l’administrateur.</div>
        @if(session('status') === 'verification-link-sent')<p>La demande de vérification a été traitée en mode test.</p>@endif
    @else
        <p>Vérifiez votre boîte mail et vos courriers indésirables à l’adresse {{ auth('web')->user()->email }} pour accéder à votre compte.</p>
        @if(session('status') === 'verification-link-sent')<div class="alert alert-success">Un nouveau lien de vérification a été transmis au service d’envoi.</div>@endif
    @endif
    <form method="POST" action="{{ route('verification.send') }}">@csrf<button class="btn btn-success">Renvoyer le lien</button></form>
    <p class="mt-3"><a href="{{ route('profile.edit') }}">Corriger mon adresse e-mail ou mon profil</a></p>
</section>
@endsection
