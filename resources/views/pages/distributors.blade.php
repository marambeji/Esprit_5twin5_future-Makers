@extends('layouts.layout')
@section('title', 'Distributeurs — NutriTrace')
@section('body_class', 'main-layout inner_page')
@section('content')
<section class="container py-5 catalogue-page"><div class="titlepage text_align_left"><span>Distribution</span><h1>Nos distributeurs</h1><p>Retrouvez les partenaires qui acheminent nos produits.</p></div>
<form method="GET" action="{{ route('distributors.index') }}" class="mb-4"><label for="q">Rechercher</label><div class="d-flex"><input id="q" type="search" name="q" value="{{ request('q') }}" class="form-control mr-2" placeholder="Nom, ville ou e-mail"><button class="btn btn-success">Rechercher</button></div></form>
<div class="row">
@forelse ($distributors as $distributor)
    <div class="col-md-4 mb-5">
        <article class="services_box_main catalogue-product">
            <div class="services_box text_align_left"><div class="veget">
                <span class="catalogue-category">{{ $distributor->city }}</span>
                <h2>{{ $distributor->name }}</h2>
                <p class="mb-2">{{ $distributor->phone }}</p>
                <p class="mb-3">{{ $distributor->deliveries_count }} livraison(s)</p>
            </div></div>
            <a class="read_more" href="{{ route('distributors.show', $distributor) }}">Voir le distributeur</a>
        </article>
    </div>
@empty
    <div class="col-12"><p>Aucun distributeur trouvé.</p></div>
@endforelse
</div>{{ $distributors->links('pagination::bootstrap-4') }}
</section>
@endsection
