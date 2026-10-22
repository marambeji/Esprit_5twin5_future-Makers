@php
    $mapMode = $mapMode ?? 'form';
    $isForm = $mapMode === 'form';
    $hasValidCoordinates = is_numeric($ferme->latitude) && is_numeric($ferme->longitude)
        && $ferme->latitude >= -90 && $ferme->latitude <= 90
        && $ferme->longitude >= -180 && $ferme->longitude <= 180;
    $boundaryInput = old('boundary_geojson', $ferme->boundary_geojson
        ? json_encode($ferme->boundary_geojson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        : '');
@endphp
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
    .front-farm-map { height:520px; width:100%; position:relative; border-radius:12px; overflow:hidden; }
    .front-farm-map:fullscreen { height:100vh; width:100vw; border-radius:0; }
    .agricultural-map-toolbar { display:flex; flex-direction:column; }
    .agricultural-map-toolbar button,.agricultural-layer-switch button { width:2.6rem; height:2.6rem; border:0; background:#fff; color:#26352b; display:grid; place-items:center; }
    .agricultural-map-toolbar button[aria-pressed="true"] { background:#f5c518; }
    .agricultural-map-toolbar svg,.agricultural-layer-switch svg { width:1.25rem; height:1.25rem; fill:none; stroke:currentColor; stroke-width:1.8; stroke-linecap:round; stroke-linejoin:round; }
    .agricultural-measure-panel { position:absolute; z-index:1000; top:.75rem; right:.75rem; width:min(350px,calc(100% - 1.5rem)); padding:1rem; border-radius:.75rem; background:#fff; color:#26352b; box-shadow:0 6px 24px #163a202e; font-family:inherit; }
    .agricultural-measure-panel[hidden] { display:none; }
    .agri-panel-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:.65rem; }
    .agricultural-measure-panel [data-row] { display:grid; grid-template-columns:1fr auto auto; align-items:center; gap:.45rem; padding:.35rem 0; border-bottom:1px solid #e7ece5; }
    .agricultural-measure-panel label { margin:0; }
    .agricultural-measure-panel output { font-variant-numeric:tabular-nums; font-weight:600; white-space:nowrap; }
    .agricultural-measure-panel select { border:0; background:#f3f6f0; border-radius:.25rem; margin-left:.25rem; }
    .agricultural-measure-panel [data-copy] { border:0; background:transparent; }
    .agricultural-measure-panel small { display:block; color:#66756a; margin-top:.65rem; }
    .agri-delete-shape { border:0; background:transparent; color:#b42318; padding:.5rem 0 0; }
    .agri-handle { background:#fff; border:2px solid #315b36; border-radius:50%; }
    .agri-vertex { width:15px!important; height:15px!important; }
    .agri-midpoint { width:10px!important; height:10px!important; }
    @media(max-width:575.98px) { .front-farm-map { height:420px; } .agricultural-map-toolbar { position:fixed; left:50%; top:auto; bottom:1rem; transform:translateX(-50%); flex-direction:row; max-width:calc(100vw - 1rem); overflow:auto; z-index:1000; } .agricultural-measure-panel { position:fixed; left:.5rem; right:.5rem; top:auto; bottom:.5rem; width:auto; max-height:45vh; overflow:auto; } }
</style>
@if ($isForm)
    <input type="hidden" id="boundary_geojson" name="boundary_geojson" value="{{ $boundaryInput }}">
    <input type="hidden" id="boundary-remove-flag" name="remove_boundary" value="{{ old('remove_boundary', '0') }}">
    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
        <span id="boundary-status" class="badge text-bg-secondary">{{ $ferme->boundary_geojson ? 'Limite chargée dans ce formulaire' : 'Aucune limite sélectionnée' }}</span>
        <label class="form-check form-switch mb-0"><input id="sync-boundary-area" class="form-check-input" type="checkbox" @checked(old('sync_boundary_area', blank(old('superficie', $ferme->superficie)) ? '1' : '0') === '1')><span class="form-check-label">Mettre à jour automatiquement la superficie en hectares</span></label>
        <button id="remove-boundary" type="button" class="btn btn-sm btn-outline-danger" @disabled(! $ferme->boundary_geojson)>Supprimer la limite</button>
    </div>
    @error('boundary_geojson')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror
@endif
<div id="front-farm-map" class="front-farm-map border mb-2" data-measurement-map data-map-tools role="application" aria-label="Carte de localisation et de limite de la ferme"></div>
<p id="front-farm-map-status" class="small text-muted mb-2" role="status" aria-live="polite">Choisissez l’emplacement manuellement. Aucune coordonnée n’est déduite de l’adresse. © <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a> contributors.</p>
@if ($isForm)
    <p class="small text-muted">Les cercles sont enregistrés sous forme de polygones à 64 sommets. La surface est calculée géodésiquement; la synchronisation met à jour le champ en hectares. Modifier manuellement la superficie désactive la synchronisation jusqu’à ce que vous la réactiviez.</p>
@endif
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script type="module">
import {addAgriculturalBaseLayers, installFarmMapMeasurement} from @json(asset('js/agricultural-map-controls.mjs'));
import {circleToPolygon, polygonMetrics} from @json(asset('js/agricultural-map-geometry.mjs'));
(() => {
    const container = document.getElementById('front-farm-map');
    if (!container || container.dataset.leafletInitialised === 'true' || !window.L) return;
    container.dataset.leafletInitialised = 'true';
    const L = window.L, status = document.getElementById('front-farm-map-status');
    const formMode = @json($isForm), hasMarker = @json($hasValidCoordinates);
    const position = hasMarker ? [Number(@json((float) $ferme->latitude)), Number(@json((float) $ferme->longitude))] : [34, 9];
    const map = L.map(container).setView(position, hasMarker ? 13 : 6), layers = addAgriculturalBaseLayers(map, L, {showControl:false});
    const bounds = [];
    if (hasMarker && !formMode) {
        const marker = L.marker(position).addTo(map);
        const popup = document.createElement('div'); popup.textContent = @json($ferme->nom);
        if (!formMode) marker.bindPopup(popup);
        bounds.push(L.latLng(position));
    }
    let savedFeature = null, selectedArea = null, drawingActive = false;
    const latitude = formMode ? document.getElementById('latitude') : null;
    const longitude = formMode ? document.getElementById('longitude') : null;
    const boundaryInput = formMode ? document.getElementById('boundary_geojson') : null;
    const removeFlag = formMode ? document.getElementById('boundary-remove-flag') : null;
    const boundaryStatus = formMode ? document.getElementById('boundary-status') : null;
    const syncArea = formMode ? document.getElementById('sync-boundary-area') : null;
    const removeButton = formMode ? document.getElementById('remove-boundary') : null;
    const validPosition = () => {
        if (!latitude?.value.trim() || !longitude?.value.trim()) return null;
        const lat = Number(latitude.value), lng = Number(longitude.value);
        return Number.isFinite(lat) && Number.isFinite(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180 ? L.latLng(lat, lng) : null;
    };
    let gpsMarker = null;
    const updateCoordinates = (point) => { latitude.value = point.lat.toFixed(7); longitude.value = point.lng.toFixed(7); };
    const placeMarker = (point, update = true) => {
        if (!gpsMarker) { gpsMarker = L.marker(point, {draggable:true}).addTo(map); gpsMarker.on('dragend', () => updateCoordinates(gpsMarker.getLatLng())); }
        else gpsMarker.setLatLng(point);
        if (update) updateCoordinates(point);
    };
    if (formMode && hasMarker) placeMarker(L.latLng(position), false);
    if (formMode) {
        map.on('click', (event) => { if (!drawingActive) placeMarker(event.latlng); });
        const syncMarker = () => { const point = validPosition(); if (point) { placeMarker(point, false); map.setView(point, Math.max(map.getZoom(), 13)); } };
        latitude.addEventListener('change', syncMarker); longitude.addEventListener('change', syncMarker);
    }
    const serializeShape = (shape) => {
        const points = shape.type === 'circle' ? circleToPolygon(shape.center, shape.radiusMeters) : shape.points;
        const metrics = polygonMetrics(points);
        return {geojson:{type:'Polygon',coordinates:[[...points, points[0]].map((point) => [point.lng,point.lat])]},area:metrics.areaSquareMeters};
    };
    const writeBoundary = (shape) => {
        if (!formMode) return false;
        try {
            const data = serializeShape(shape);
            boundaryInput.value = JSON.stringify(data.geojson); removeFlag.value = '0'; selectedArea = data.area;
            boundaryStatus.textContent = `Limite enregistrée dans le formulaire : ${(selectedArea / 10000).toLocaleString('fr-FR',{maximumFractionDigits:2})} ha`;
            boundaryStatus.className = 'badge text-bg-success'; removeButton.disabled = false;
            if (syncArea.checked) updateSurfaceFromBoundary();
            return true;
        } catch (error) { status.textContent = error.message || 'Cette forme ne peut pas être enregistrée comme limite.'; return false; }
    };
    const superficieInput = formMode ? document.getElementById('superficie') : null;
    const updateSurfaceFromBoundary = () => {
        if (!formMode || selectedArea === null || !syncArea.checked) return;
        superficieInput.value = (selectedArea / 10000).toFixed(2);
    };
    if (formMode) {
        superficieInput.addEventListener('input', () => {
            if (syncArea.checked) {
                syncArea.checked = false;
                status.textContent = 'Superficie modifiée manuellement. Réactivez la synchronisation si vous souhaitez reprendre la valeur calculée.';
            }
        });
        syncArea.addEventListener('change', updateSurfaceFromBoundary);
    }
    const tools = installFarmMapMeasurement(map, L, container, status, {
        layers, markerPosition:hasMarker ? L.latLng(position) : null, editable:formMode,
        onDrawingChange:(active) => { drawingActive = active; },
        onUseShape:(shape) => { if (writeBoundary(shape)) savedFeature = tools.features.find((feature) => feature.layer === shape.layer) || savedFeature; },
        onFeatureChange:(feature) => { if (formMode && feature === savedFeature) writeBoundary(feature); },
    });
    if (formMode) {
        removeButton.addEventListener('click', () => {
            boundaryInput.value = ''; removeFlag.value = '1'; selectedArea = null;
            if (syncArea.checked) superficieInput.value = '';
            if (savedFeature) tools.removeFeature(savedFeature);
            savedFeature = null; boundaryStatus.textContent = 'Limite supprimée'; boundaryStatus.className = 'badge text-bg-secondary'; removeButton.disabled = true;
        });
    }
    let boundary = @json($ferme->boundary_geojson);
    if (formMode && boundaryInput?.value.trim()) {
        try { boundary = JSON.parse(boundaryInput.value); } catch { boundary = null; }
    }
    if (boundary?.type === 'Polygon' && Array.isArray(boundary.coordinates?.[0])) {
        try {
            const points = boundary.coordinates[0].slice(0,-1).map(([lng,lat]) => L.latLng(lat,lng));
            if (points.length >= 3) {
                savedFeature = tools.loadPolygon(points); tools.select(savedFeature); bounds.push(savedFeature.layer.getBounds());
                if (formMode) {
                    selectedArea = polygonMetrics(points.map((point) => ({lat:point.lat,lng:point.lng}))).areaSquareMeters;
                    boundaryStatus.textContent = `Limite enregistrée dans le formulaire : ${(selectedArea / 10000).toLocaleString('fr-FR',{maximumFractionDigits:2})} ha`;
                    boundaryStatus.className = 'badge text-bg-success'; removeButton.disabled = false;
                    if (syncArea.checked && !superficieInput.value.trim()) updateSurfaceFromBoundary();
                }
            }
        } catch { if (formMode) boundaryStatus.textContent = 'La limite actuelle ne peut pas être affichée; elle reste conservée sauf remplacement explicite.'; }
    }
    if (bounds.length) map.fitBounds(bounds, {padding:[30,30],maxZoom:15});
    [layers.osm,layers.satellite].forEach((tileLayer) => {
        tileLayer.on('load', () => { status.textContent = 'Carte chargée. © OpenStreetMap contributors.'; });
        tileLayer.on('tileerror', () => { status.textContent = 'Les tuiles sont indisponibles. La fiche et le formulaire restent utilisables.'; });
    });
    const invalidate = () => window.setTimeout(() => map.invalidateSize(),0);
    window.addEventListener('resize',invalidate); document.addEventListener('fullscreenchange',invalidate); invalidate();
})();
</script>
