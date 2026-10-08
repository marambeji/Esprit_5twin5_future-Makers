@extends('layouts.layout')
@section('title', $ferme->nom . ' — NutriTrace')
@section('body_class', 'main-layout inner_page')

@section('content')
<section class="container py-5">

    <a class="text-success d-inline-block mb-4" href="{{ route('fermes.index') }}">← Retour aux fermes</a>

    <div class="row align-items-start">

        {{-- Icône / Visuel --}}
        <div class="col-lg-4 mb-4 mb-lg-0">
            <div style="background:#f6f7f1;border-radius:12px;padding:3rem;text-align:center;">
                <i class="fa fa-home" aria-hidden="true" style="font-size:96px;color:#7aac2b;"></i>
            </div>
        </div>

        {{-- Détails de la ferme --}}
        <div class="col-lg-8 pl-lg-4">
            <h1 class="h2 mb-4">{{ $ferme->nom }}</h1>

            <div class="border-top border-bottom py-3 mb-4">
                <p class="mb-2">
                    <i class="fa fa-map-marker" aria-hidden="true" style="color:#7aac2b;width:20px;"></i>
                    <strong>Localisation :</strong> {{ $ferme->localisation }}
                </p>
                @if ($ferme->superficie)
                <p class="mb-2">
                    <i class="fa fa-leaf" aria-hidden="true" style="color:#7aac2b;width:20px;"></i>
                    <strong>Superficie :</strong> {{ $ferme->superficie }} ha
                </p>
                @endif
                @if ($ferme->producteur)
                <p class="mb-0">
                    <i class="fa fa-user-circle-o" aria-hidden="true" style="color:#7aac2b;width:20px;"></i>
                    <strong>Producteur :</strong>
                    <a href="{{ route('producteurs.show', $ferme->producteur) }}" class="text-success">
                        {{ $ferme->producteur->prenom }} {{ $ferme->producteur->nom }}
                    </a>
                </p>
                @endif
            </div>

            @if ($ferme->description)
            <h2 class="h5 mb-2">Description</h2>
            <p style="white-space:pre-line;overflow-wrap:anywhere;">{{ $ferme->description }}</p>
            @endif

            <div class="mt-4">
                @if ($ferme->producteur)
                <a class="btn btn-success mb-2 mr-2" href="{{ route('producteurs.show', $ferme->producteur) }}">
                    <i class="fa fa-user" aria-hidden="true"></i> Voir le producteur
                </a>
                @endif
                <a class="btn btn-outline-success mb-2" href="{{ route('fermes.index') }}">
                    Toutes les fermes
                </a>
            </div>
        </div>

    </div>

</section>
@endsection
