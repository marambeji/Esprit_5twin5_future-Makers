@extends('layouts.layout')
@section('title', $producteur->prenom . ' ' . $producteur->nom . ' — NutriTrace')
@section('body_class', 'main-layout inner_page')

@section('content')
<section class="container py-5">

    <a class="text-success d-inline-block mb-4" href="{{ route('producteurs.index') }}">← Retour aux producteurs</a>

    <div class="row align-items-start mb-5">

        {{-- Icône / Identité --}}
        <div class="col-lg-3 text-center mb-4 mb-lg-0">
            <div style="background:#f6f7f1;border-radius:12px;padding:2rem;">
                <i class="fa fa-user-circle-o" aria-hidden="true" style="font-size:90px;color:#7aac2b;"></i>
            </div>
        </div>

        {{-- Informations --}}
        <div class="col-lg-9 pl-lg-4">
            <h1 class="h2 mb-4">{{ $producteur->prenom }} {{ $producteur->nom }}</h1>
            <div class="border-top border-bottom py-3 mb-4">
                <p class="mb-2">
                    <i class="fa fa-envelope" aria-hidden="true" style="color:#7aac2b;width:20px;"></i>
                    <strong>Email :</strong> {{ $producteur->email }}
                </p>
                @if ($producteur->telephone)
                <p class="mb-2">
                    <i class="fa fa-phone" aria-hidden="true" style="color:#7aac2b;width:20px;"></i>
                    <strong>Téléphone :</strong> {{ $producteur->telephone }}
                </p>
                @endif
                @if ($producteur->adresse)
                <p class="mb-0">
                    <i class="fa fa-map-marker" aria-hidden="true" style="color:#7aac2b;width:20px;"></i>
                    <strong>Adresse :</strong> {{ $producteur->adresse }}
                </p>
                @endif
            </div>
        </div>

    </div>

    {{-- Fermes associées --}}
    <div class="titlepage text_align_left mb-4">
        <span>Exploitations agricoles</span>
        <h2>SES FERMES ({{ $fermes->total() }})</h2>
    </div>

    @if ($fermes->isEmpty())
        <p>Ce producteur n'a pas encore de ferme enregistrée.</p>
    @else
        <div class="row">
            @foreach ($fermes as $ferme)
            <div class="col-md-4 mb-4">
                <article class="services_box_main">
                    <div class="services_box text_align_left">
                        <figure style="display:flex;align-items:center;justify-content:center;background:#f6f7f1;min-height:140px;border-radius:8px 8px 0 0;">
                            <i class="fa fa-home" aria-hidden="true" style="font-size:56px;color:#7aac2b;"></i>
                        </figure>
                        <div class="veget">
                            <h3 style="font-size:1rem;">{{ $ferme->nom }}</h3>
                            <p class="mb-1"><i class="fa fa-map-marker" aria-hidden="true"></i> {{ $ferme->localisation }}</p>
                            @if ($ferme->superficie)
                            <p class="mb-0"><i class="fa fa-leaf" aria-hidden="true"></i> {{ $ferme->superficie }} ha</p>
                            @endif
                        </div>
                    </div>
                    <a class="read_more" href="{{ route('fermes.show', $ferme) }}">Voir la ferme</a>
                </article>
            </div>
            @endforeach
        </div>
        <div class="d-flex justify-content-center mt-4">
            {{ $fermes->links('pagination::bootstrap-4') }}
        </div>
    @endif

</section>
@endsection
