@extends('layouts.layout')
@section('title', 'Producteurs — NutriTrace')
@section('body_class', 'main-layout inner_page')

@section('content')
<section class="container py-5 producteurs-page">

    <div class="titlepage text_align_left">
        <span>Nos partenaires</span>
        <h1>PRODUCTEURS</h1>
        <p>Découvrez les agriculteurs et éleveurs partenaires de NutriTrace, engagés pour une alimentation transparente et de qualité.</p>
    </div>

    <form id="filter-form" method="GET" action="{{ route('producteurs.index') }}" class="mb-5">
        <div class="row">
            <div class="col-md-5 mb-2">
                <input type="text" class="form-control" name="q" placeholder="Recherche par nom, prénom, email..." value="{{ request('q') }}" oninput="clearTimeout(this.timer); this.timer = setTimeout(() => { document.getElementById('filter-form').submit(); }, 500);">
            </div>
            <div class="col-md-5 mb-2">
                <select name="fermes_count" class="form-control" onchange="document.getElementById('filter-form').submit();">
                    <option value="">Tous (nb de fermes)</option>
                    <option value="0" @selected(request('fermes_count') === '0')>0 ferme</option>
                    <option value="1-3" @selected(request('fermes_count') === '1-3')>1 à 3 fermes</option>
                    <option value="4+" @selected(request('fermes_count') === '4+')>4 fermes et plus</option>
                </select>
            </div>
            <div class="col-md-2 mb-2 d-flex">
                <a href="{{ route('producteurs.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
            </div>
        </div>
    </form>

    <div class="row">
        @forelse ($producteurs as $producteur)
        <div class="col-md-4 mb-5">
            <article class="services_box_main">
                <div class="services_box text_align_left">
                    <figure style="display:flex;align-items:center;justify-content:center;background:#f6f7f1;min-height:180px;border-radius:8px 8px 0 0;">
                        <i class="fa fa-user-circle-o" aria-hidden="true" style="font-size:72px;color:#7aac2b;"></i>
                    </figure>
                    <div class="veget">
                        <h2 style="font-size:1.15rem;">{{ $producteur->nom }} {{ $producteur->prenom }}</h2>
                        @if ($producteur->adresse)
                        <p class="mb-1"><i class="fa fa-map-marker" aria-hidden="true"></i> {{ $producteur->adresse }}</p>
                        @endif
                        <p class="mb-0">
                            <i class="fa fa-home" aria-hidden="true"></i>
                            <strong>{{ $producteur->fermes_count }}</strong>
                            {{ $producteur->fermes_count > 1 ? 'fermes' : 'ferme' }}
                        </p>
                    </div>
                </div>
                <a class="read_more" href="{{ route('producteurs.show', $producteur) }}">Voir les détails</a>
            </article>
        </div>
        @empty
        <div class="col-12">
            <p>Aucun producteur disponible pour le moment.</p>
        </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $producteurs->links('pagination::bootstrap-4') }}
    </div>

</section>
@endsection
