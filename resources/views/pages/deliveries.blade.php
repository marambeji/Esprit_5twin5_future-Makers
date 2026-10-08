@extends('layouts.layout')
@section('title', 'Livraisons — NutriTrace')
@section('body_class', 'main-layout inner_page')
@section('content')
<section class="container py-5 catalogue-page"><div class="titlepage text_align_left"><span>Distribution</span><h1>Suivi des livraisons</h1><p>Consultez les livraisons et leur statut.</p></div>
<form method="GET" action="{{ route('deliveries.index') }}" class="mb-4"><label for="q">Rechercher</label><div class="d-flex"><input id="q" type="search" name="q" value="{{ request('q') }}" class="form-control mr-2" placeholder="Référence, destination ou distributeur"><select name="status" class="form-control mr-2" aria-label="Statut"><option value="">Tous les statuts</option>@foreach (\App\Models\Delivery::STATUSES as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select><button class="btn btn-success">Rechercher</button></div></form>
<div class="row">
@forelse ($deliveries as $delivery)
    <div class="col-md-4 mb-5">
        <article class="services_box_main catalogue-product">
            <div class="services_box text_align_left"><div class="veget">
                <span class="catalogue-category">{{ $delivery->status_label }}</span>
                <h2>{{ $delivery->reference }}</h2>
                <p class="mb-2">{{ $delivery->distributor->name }}</p>
                <p class="mb-2">{{ $delivery->destination }}</p>
                <p class="mb-3">{{ $delivery->delivery_date->format('d/m/Y') }}</p>
            </div></div>
            <a class="read_more" href="{{ route('deliveries.show', $delivery) }}">Voir la livraison</a>
        </article>
    </div>
@empty
    <div class="col-12"><p>Aucune livraison trouvée.</p></div>
@endforelse
</div>{{ $deliveries->links('pagination::bootstrap-4') }}
</section>
@endsection
