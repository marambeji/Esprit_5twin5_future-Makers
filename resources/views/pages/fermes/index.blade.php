@extends('layouts.layout')
@section('title', 'Fermes — NutriTrace')
@section('body_class', 'main-layout inner_page')

@section('content')
<section class="container py-5 fermes-page">

    <div class="titlepage text_align_left">
        <span>Exploitations agricoles</span>
        <h1>FERMES PARTENAIRES</h1>
        <p>Explorez les fermes et exploitations agricoles qui contribuent à la traçabilité des produits NutriTrace.</p>
    </div>

    <form id="filter-form" method="GET" action="{{ route('fermes.index') }}" class="mb-5">
        <div class="row">
            <div class="col-md-3 mb-2">
                <input type="text" class="form-control" name="q" placeholder="Nom ou localisation..." value="{{ request('q') }}" oninput="clearTimeout(this.timer); this.timer = setTimeout(() => { document.getElementById('filter-form').submit(); }, 500);">
            </div>
            <div class="col-md-3 mb-2">
                <select name="producteur_id" class="form-control" onchange="document.getElementById('filter-form').submit();">
                    <option value="">Tous les producteurs</option>
                    @foreach($producteurs as $prod)
                        <option value="{{ $prod->id }}" @selected(request('producteur_id') == $prod->id)>{{ $prod->nom }} {{ $prod->prenom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <input type="number" step="0.01" class="form-control" name="superficie_min" placeholder="Sup. min (ha)" value="{{ request('superficie_min') }}" oninput="clearTimeout(this.timer); this.timer = setTimeout(() => { document.getElementById('filter-form').submit(); }, 800);">
            </div>
            <div class="col-md-2 mb-2">
                <input type="number" step="0.01" class="form-control" name="superficie_max" placeholder="Sup. max (ha)" value="{{ request('superficie_max') }}" oninput="clearTimeout(this.timer); this.timer = setTimeout(() => { document.getElementById('filter-form').submit(); }, 800);">
            </div>
            <div class="col-md-2 mb-2 d-flex">
                <a href="{{ route('fermes.index') }}" class="btn btn-outline-secondary" style="padding:10px 15px;">X</a>
            </div>
        </div>
    </form>

    <div class="row">
        @forelse ($fermes as $ferme)
        <div class="col-md-4 mb-5">
            <article class="services_box_main">
                <div class="services_box text_align_left">
                    <figure style="display:flex;align-items:center;justify-content:center;background:#f6f7f1;min-height:180px;border-radius:8px 8px 0 0;">
                        <i class="fa fa-home" aria-hidden="true" style="font-size:72px;color:#7aac2b;"></i>
                    </figure>
                    <div class="veget">
                        <h2 style="font-size:1.15rem;">{{ $ferme->nom }}</h2>
                        <p class="mb-1">
                            <i class="fa fa-map-marker" aria-hidden="true"></i> {{ $ferme->localisation }}
                        </p>
                        @if ($ferme->superficie)
                        <p class="mb-1">
                            <i class="fa fa-leaf" aria-hidden="true"></i> {{ $ferme->superficie }} ha
                        </p>
                        @endif
                        @if ($ferme->producteur)
                        <p class="mb-0">
                            <i class="fa fa-user" aria-hidden="true"></i>
                            <a href="{{ route('producteurs.show', $ferme->producteur) }}" class="text-success">
                                {{ $ferme->producteur->nom }} {{ $ferme->producteur->prenom }}
                            </a>
                        </p>
                        @endif
                    </div>
                </div>
                <a class="read_more" href="{{ route('fermes.show', $ferme) }}">Voir la ferme</a>
            </article>
        </div>
        @empty
        <div class="col-12">
            <p>Aucune ferme disponible pour le moment.</p>
        </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $fermes->links('pagination::bootstrap-4') }}
    </div>

</section>
@endsection
