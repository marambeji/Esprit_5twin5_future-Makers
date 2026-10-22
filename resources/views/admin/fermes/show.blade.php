@extends('admin.layouts.layout')
@section('title', 'Ferme — NutriTrace')
@php
    $rawSuperficie = $ferme->getAttributes()['superficie'] ?? null;
    $displaySuperficie = is_numeric($rawSuperficie) && is_finite((float) $rawSuperficie)
        ? number_format((float) $rawSuperficie, 2, ',', ' ').' ha'
        : ($rawSuperficie === null || $rawSuperficie === '' ? 'Non renseignée' : 'Valeur à vérifier');
    $hasValidCoordinates = is_numeric($ferme->latitude) && is_numeric($ferme->longitude)
        && $ferme->latitude >= -90 && $ferme->latitude <= 90
        && $ferme->longitude >= -180 && $ferme->longitude <= 180;
    $hasBoundary = is_array($ferme->boundary_geojson) && ($ferme->boundary_geojson['type'] ?? null) === 'Polygon';
    $hasMapContent = $hasValidCoordinates || $hasBoundary;
@endphp
@if ($hasMapContent)
    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <style>
        #farm-detail-map { height: 520px; width: 100%; position: relative; }
        #farm-detail-map:fullscreen { height:100vh; width:100vw; border-radius:0!important; }
        .agricultural-map-toolbar { display:flex; flex-direction:column; }
        .agricultural-map-toolbar button,.agricultural-layer-switch button { width:2.6rem; height:2.6rem; border:0; background:#fff; color:#24292f; display:grid; place-items:center; }
        .agricultural-map-toolbar button[aria-pressed="true"] { background:#f5c518; }
        .agricultural-map-toolbar svg,.agricultural-layer-switch svg { width:1.25rem; height:1.25rem; fill:none; stroke:currentColor; stroke-width:1.8; stroke-linecap:round; stroke-linejoin:round; }
        .agricultural-measure-panel { position:absolute; z-index:1000; top:.75rem; right:.75rem; width:min(350px,calc(100% - 1.5rem)); padding:1rem; border-radius:.5rem; background:#fff; color:#212529; box-shadow:0 3px 16px #0003; }
        .agricultural-measure-panel[hidden] { display:none; }
        .agri-panel-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:.65rem; }
        .agricultural-measure-panel [data-row] { display:grid; grid-template-columns:1fr auto auto; align-items:center; gap:.45rem; padding:.35rem 0; border-bottom:1px solid #eee; }
        .agricultural-measure-panel label { margin:0; }
        .agricultural-measure-panel output { font-variant-numeric:tabular-nums; font-weight:600; white-space:nowrap; }
        .agricultural-measure-panel select { border:0; background:#f3f4f6; border-radius:.25rem; margin-left:.25rem; }
        .agricultural-measure-panel [data-copy] { border:0; background:transparent; }
        .agricultural-measure-panel small { display:block; color:#667085; margin-top:.65rem; }
        .agri-delete-shape { border:0; background:transparent; color:#b42318; padding:.5rem 0 0; }
        .agri-handle { background:#fff; border:2px solid #343a40; border-radius:50%; }
        .agri-vertex { width:15px!important; height:15px!important; }
        .agri-midpoint { width:10px!important; height:10px!important; }
        @media(max-width:575.98px) { #farm-detail-map { height:420px; } .agricultural-map-toolbar { position:fixed; left:50%; top:auto; bottom:1rem; transform:translateX(-50%); flex-direction:row; max-width:calc(100vw - 1rem); overflow:auto; z-index:1000; } .agricultural-measure-panel { position:fixed; left:.5rem; right:.5rem; top:auto; bottom:.5rem; width:auto; max-height:45vh; overflow:auto; } }
    </style>
    @endpush
@endif
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <h1 class="h3 mb-0">{{ $ferme->nom }}</h1>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary" href="{{ route('admin.fermes.index') }}">Retour à la liste</a>
        <a class="btn btn-outline-secondary" href="{{ route('admin.agriculture.map') }}">Carte des fermes</a>
        <a class="btn btn-outline-primary" href="{{ route('admin.fermes.edit', $ferme) }}">Modifier</a>
        <form method="POST" action="{{ route('admin.fermes.destroy', $ferme) }}" onsubmit="return confirm('Supprimer cette ferme ?');">@csrf @method('DELETE')<button class="btn btn-outline-danger">Supprimer</button></form>
    </div>
</div>
<div class="card"><div class="card-body">
    <h2 class="h5">Informations de la ferme</h2>
    <dl class="row mb-0">
        <dt class="col-sm-3">Nom</dt><dd class="col-sm-9">{{ $ferme->nom }}</dd>
        <dt class="col-sm-3">Producteur</dt><dd class="col-sm-9"><a href="{{ route('admin.producteurs.show', $ferme->producteur) }}">{{ $ferme->producteur->prenom }} {{ $ferme->producteur->nom }}</a></dd>
        <dt class="col-sm-3">Localisation</dt><dd class="col-sm-9">{{ $ferme->localisation ?: 'Non renseignée' }}</dd>
        <dt class="col-sm-3">Superficie</dt><dd class="col-sm-9">{{ $displaySuperficie }}</dd>
        @if ($hasBoundary && $ferme->boundary_perimeter_m !== null)<dt class="col-sm-3">Périmètre de la limite</dt><dd class="col-sm-9">{{ number_format((float) $ferme->boundary_perimeter_m, 2, ',', ' ') }} m</dd>@endif
        @if ($hasBoundary && $ferme->boundary_area_m2 !== null)<dt class="col-sm-3">Surface de la limite</dt><dd class="col-sm-9">{{ number_format((float) $ferme->boundary_area_m2, 2, ',', ' ') }} m² ({{ number_format((float) $ferme->boundary_area_m2 / 10000, 2, ',', ' ') }} ha)</dd>@endif
        <dt class="col-sm-3">Description</dt><dd class="col-sm-9">{!! $ferme->description ? nl2br(e($ferme->description)) : 'Non renseignée' !!}</dd>
        <dt class="col-sm-3">Latitude</dt><dd class="col-sm-9">{{ $hasValidCoordinates ? $ferme->latitude : 'Non renseignée' }}</dd>
        <dt class="col-sm-3">Longitude</dt><dd class="col-sm-9">{{ $hasValidCoordinates ? $ferme->longitude : 'Non renseignée' }}</dd>
        <dt class="col-sm-3">Fiche créée</dt><dd class="col-sm-9">{{ $ferme->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
        <dt class="col-sm-3">Dernière modification</dt><dd class="col-sm-9">{{ $ferme->updated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
    </dl>
</div></div>
@if ($hasMapContent)
<div class="card mt-4"><div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><h2 class="h5 mb-0">Emplacement enregistré et limite</h2>@if ($hasValidCoordinates)<a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener noreferrer" href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($ferme->latitude.','.$ferme->longitude) }}">Ouvrir dans Google Maps</a>@endif</div>
    <div id="farm-detail-map" class="rounded border" data-measurement-map data-map-tools role="application" aria-label="Carte de l’emplacement enregistré de la ferme"></div>
    <p id="farm-detail-map-status" class="small text-muted mt-2 mb-0" role="status" aria-live="polite">Carte en cours de chargement. Les informations restent disponibles ci-dessus si les tuiles ne répondent pas.</p>
    <p class="form-text mt-2 mb-0">© <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a> contributors.</p>
</div></div>
@else
<div class="card mt-4"><div class="card-body"><h2 class="h5">Emplacement</h2><p class="text-muted mb-0">Aucune coordonnée GPS valide n’est enregistrée pour cette ferme. Vous pouvez en ajouter depuis la page de modification.</p></div></div>
@endif
<div class="card mt-4"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">Qualité de la fiche</h2><span class="badge bg-{{ $dataQuality['score'] === 100 ? 'success' : 'warning' }}">{{ $dataQuality['score'] }} % complétée</span></div>
    @if ($dataQuality['missing'])<p class="mb-2"><strong>À compléter :</strong> {{ implode(', ', $dataQuality['missing']) }}.</p>@endif
    @if ($dataQuality['invalid'])<p class="mb-2 text-danger"><strong>Valeurs à vérifier :</strong> {{ implode(', ', $dataQuality['invalid']) }}.</p>@endif
    @foreach ($dataQuality['warnings'] as $warning)<div class="alert alert-warning py-2">{{ $warning }}</div>@endforeach
    @foreach ($dataQuality['recommendations'] as $recommendation)<p class="text-muted mb-1">{{ $recommendation }}</p>@endforeach
    @if (! $dataQuality['missing'] && ! $dataQuality['invalid'] && ! $dataQuality['warnings'])<p class="text-success mb-0">Les informations disponibles sont complètes.</p>@endif
</div></div>
<div class="card mt-4"><div class="card-body">
    <h2 class="h5">Assistant agricole</h2><p class="text-muted">Les réponses sont des suggestions basées uniquement sur les données enregistrées.</p>
    @if (session('agricultural_ai_error'))<div class="alert alert-warning">{{ session('agricultural_ai_error') }}</div>@endif
    @if (session('agricultural_ai_result'))<div class="alert alert-info mb-3" style="white-space: pre-line">{{ session('agricultural_ai_result') }}</div>@endif
    <div class="d-flex flex-wrap gap-2">
        @foreach (['description' => 'Générer une description', 'summary' => 'Résumer la fiche', 'comparison' => 'Comparer les superficies', 'improvements' => 'Suggérer les informations à compléter', 'organization' => 'Conseils d’organisation'] as $action => $label)
        <form method="POST" action="{{ route('admin.fermes.assistant', $ferme) }}">@csrf<input type="hidden" name="action" value="{{ $action }}"><button class="btn btn-sm btn-outline-primary">{{ $label }}</button></form>
        @endforeach
    </div>
</div></div>
@endsection
@if ($hasMapContent)
    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script type="module">
    import {addAgriculturalBaseLayers, installFarmMapMeasurement} from @json(asset('js/agricultural-map-controls.mjs'));
    (() => {
        const container = document.getElementById('farm-detail-map');
        if (!container || container.dataset.leafletInitialised === 'true') return;
        container.dataset.leafletInitialised = 'true';
        const status = document.getElementById('farm-detail-map-status');
        if (!window.L) {
            status.textContent = 'La bibliothèque de carte est indisponible. Les coordonnées restent consultables ci-dessus.';
            return;
        }
        const hasMarker = @json($hasValidCoordinates);
        const position = hasMarker ? [Number(@json((float) $ferme->latitude)), Number(@json((float) $ferme->longitude))] : [34, 9];
        const map = L.map(container).setView(position, hasMarker ? 14 : 6);
        const layers = addAgriculturalBaseLayers(map, L, {showControl:false});
        const popup = document.createElement('div');
        popup.textContent = @json($ferme->nom);
        const bounds = [];
        if (hasMarker) { L.marker(position).addTo(map).bindPopup(popup).openPopup(); bounds.push(L.latLng(position)); }
        [layers.osm, layers.satellite].forEach((tiles) => {
            tiles.on('load', () => { status.textContent = 'Carte prête.'; });
            tiles.on('tileerror', () => { status.textContent = 'Les tuiles sont indisponibles. La ferme et ses coordonnées restent consultables.'; });
        });
        const tools = installFarmMapMeasurement(map, L, container, status, {layers, markerPosition: hasMarker ? L.latLng(position) : null, editable:false});
        const boundary = @json($hasBoundary ? $ferme->boundary_geojson : null);
        if (boundary?.type === 'Polygon' && Array.isArray(boundary.coordinates?.[0])) {
            const points = boundary.coordinates[0].slice(0, -1).map(([lng, lat]) => L.latLng(lat, lng));
            if (points.length >= 3) {
                const feature = tools.loadPolygon(points);
                tools.select(feature);
                bounds.push(feature.layer.getBounds());
            }
        }
        if (bounds.length) map.fitBounds(bounds, {padding:[32,32], maxZoom:15});
        const invalidateMap = () => window.setTimeout(() => map.invalidateSize(), 0);
        window.addEventListener('resize', invalidateMap);
        document.addEventListener('shown.bs.tab', invalidateMap);
        document.addEventListener('shown.bs.modal', invalidateMap);
        invalidateMap();
    })();
    </script>
    @endpush
@endif
