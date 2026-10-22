@extends('layouts.layout')
@section('title', 'Fermes — NutriTrace')
@section('body_class', 'main-layout inner_page')

@php
    $farmMapMarkers = $markers->map(fn ($farm) => [
        'lat' => (float) $farm->latitude,
        'lng' => (float) $farm->longitude,
        'name' => $farm->nom,
        'location' => $farm->localisation,
        'url' => route('fermes.show', $farm),
        'googleUrl' => 'https://www.google.com/maps/search/?api=1&query='.urlencode($farm->latitude.','.$farm->longitude),
    ])->values();
@endphp

@section('content')
<section class="container py-5 fermes-page">

    <div class="titlepage text_align_left">
        <span>Exploitations agricoles</span>
        <h1>FERMES PARTENAIRES</h1>
        <p>Explorez les fermes et exploitations agricoles qui contribuent à la traçabilité des produits NutriTrace.</p>
        @if ($managedProducer)
            <a class="btn btn-success mt-2" href="{{ route('fermes.create') }}">Ajouter une ferme</a>
        @endif
    </div>

    <div class="mb-5">
        <h2 class="h4">Carte des fermes géolocalisées</h2>
        <p class="text-muted">Les marqueurs utilisent uniquement les coordonnées GPS enregistrées.</p>
        @if ($farmMapMarkers->isNotEmpty())
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
            <style>#front-farm-list-map{height:520px;width:100%;border-radius:12px;z-index:0}#front-farm-list-map:fullscreen{height:100vh;width:100vw}@media(max-width:575.98px){#front-farm-list-map{height:420px}}</style>
            <div id="front-farm-list-map" class="border" role="application" aria-label="Carte des fermes géolocalisées"></div>
            <p id="front-farm-list-map-status" class="small text-muted mt-2" role="status" aria-live="polite">© <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a> contributors.</p>
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
            <script type="module">
            import {addAgriculturalBaseLayers, installFarmMapMeasurement} from @json(asset('js/agricultural-map-controls.mjs'));
            (() => {
                const container = document.getElementById('front-farm-list-map');
                if (!container || !window.L) return;
                const markers = @json($farmMapMarkers), map = L.map(container).setView([34,9],6);
                const layers = addAgriculturalBaseLayers(map,L,{showControl:false,osmDefault:true}), bounds=[];
                markers.forEach((farm) => {
                    const marker = L.marker([farm.lat,farm.lng]).addTo(map), popup = document.createElement('div');
                    const title = document.createElement('strong'); title.textContent = farm.name; popup.appendChild(title);
                    const location = document.createElement('p'); location.className = 'mb-1'; location.textContent = farm.location; popup.appendChild(location);
                    const link = document.createElement('a'); link.href = farm.url; link.textContent = 'Voir la ferme'; popup.appendChild(link);
                    const mapsLink = document.createElement('a'); mapsLink.href = farm.googleUrl; mapsLink.target = '_blank'; mapsLink.rel = 'noopener noreferrer'; mapsLink.className = 'd-block'; mapsLink.textContent = 'Ouvrir dans Google Maps'; popup.appendChild(mapsLink);
                    marker.bindPopup(popup); bounds.push(marker.getLatLng());
                });
                if (bounds.length) map.fitBounds(bounds,{padding:[28,28],maxZoom:13});
                installFarmMapMeasurement(map,L,container,document.getElementById('front-farm-list-map-status'),{layers,tools:false});
                [layers.osm,layers.satellite].forEach((layer) => layer.on('tileerror',() => { document.getElementById('front-farm-list-map-status').textContent='Les tuiles sont indisponibles; les fermes restent consultables dans la liste.'; }));
                window.addEventListener('resize',() => window.setTimeout(() => map.invalidateSize(),0));
            })();
            </script>
        @else
            <p class="small text-muted">Aucune ferme correspondant aux filtres ne possède de coordonnées GPS valides.</p>
        @endif
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
                @if ($managedProducer && $managedProducer->id === $ferme->producteur_id)
                    <a class="btn btn-sm btn-outline-success mt-2" href="{{ route('fermes.edit', $ferme) }}">Modifier ma ferme</a>
                @endif
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
