@php
    $mapMode = $mapMode ?? 'form';
    $isForm = $mapMode === 'form';
    $latitudeValue = old('latitude', $ferme->latitude);
    $longitudeValue = old('longitude', $ferme->longitude);
    $hasValidCoordinates = is_numeric($latitudeValue) && is_numeric($longitudeValue)
        && $latitudeValue >= -90 && $latitudeValue <= 90
        && $longitudeValue >= -180 && $longitudeValue <= 180;
@endphp
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
    .front-farm-map { height:520px; width:100%; position:relative; border-radius:12px; overflow:hidden; }
    .front-farm-map:fullscreen { height:100vh; width:100vw; border-radius:0; }
    .agricultural-map-toolbar { display:flex; flex-direction:column; }
    .agricultural-map-toolbar button { width:2.6rem; height:2.6rem; border:0; background:#fff; color:#26352b; display:grid; place-items:center; }
    .agricultural-map-toolbar svg { width:1.25rem; height:1.25rem; fill:none; stroke:currentColor; stroke-width:1.8; stroke-linecap:round; stroke-linejoin:round; }
    @media(max-width:575.98px) { .front-farm-map { height:420px; } }
</style>
<div id="front-farm-map" class="front-farm-map border mb-2" role="application" aria-label="Carte de localisation de la ferme"></div>
<p id="front-farm-map-status" class="small text-muted mb-2" role="status" aria-live="polite">Cliquez sur la carte ou déplacez le marqueur pour définir les coordonnées. © <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a> contributors.</p>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script type="module">
import {addAgriculturalBaseLayers, installFarmMapMeasurement} from @json(asset('js/agricultural-map-controls.mjs'));
(() => {
    const container = document.getElementById('front-farm-map');
    if (!container || container.dataset.leafletInitialised === 'true' || !window.L) return;
    container.dataset.leafletInitialised = 'true';
    const L = window.L, status = document.getElementById('front-farm-map-status');
    const formMode = @json($isForm), hasMarker = @json($hasValidCoordinates);
    const position = hasMarker ? [Number(@json((float) $latitudeValue)), Number(@json((float) $longitudeValue))] : [34, 9];
    const map = L.map(container).setView(position, hasMarker ? 13 : 6);
    const layers = addAgriculturalBaseLayers(map, L, {showControl:false, osmDefault:true});
    let gpsMarker = null;
    const latitude = formMode ? document.getElementById('latitude') : null;
    const longitude = formMode ? document.getElementById('longitude') : null;
    const updateCoordinates = (point) => {
        if (!latitude || !longitude) return;
        latitude.value = point.lat.toFixed(7);
        longitude.value = point.lng.toFixed(7);
    };
    const placeMarker = (point, update = true) => {
        if (!gpsMarker) {
            gpsMarker = L.marker(point, {draggable:formMode}).addTo(map);
            if (formMode) gpsMarker.on('dragend', () => updateCoordinates(gpsMarker.getLatLng()));
        } else gpsMarker.setLatLng(point);
        if (update) updateCoordinates(point);
    };
    if (hasMarker) placeMarker(L.latLng(position), false);
    if (formMode) {
        map.on('click', (event) => placeMarker(event.latlng));
        const syncMarker = () => {
            if (!latitude.value.trim() || !longitude.value.trim()) return;
            const lat = Number(latitude.value), lng = Number(longitude.value);
            if (Number.isFinite(lat) && Number.isFinite(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                const point = L.latLng(lat, lng);
                placeMarker(point, false);
                map.setView(point, Math.max(map.getZoom(), 13));
            }
        };
        latitude.addEventListener('change', syncMarker);
        longitude.addEventListener('change', syncMarker);
    }
    if (!formMode && gpsMarker) {
        const popup = document.createElement('div');
        popup.textContent = @json($ferme->nom);
        gpsMarker.bindPopup(popup);
    }
    installFarmMapMeasurement(map, L, container, status, {layers, markerPosition:hasMarker ? L.latLng(position) : null, tools:false});
    [layers.osm,layers.satellite].forEach((layer) => {
        layer.on('load', () => { status.textContent = 'Carte chargée. © OpenStreetMap contributors.'; });
        layer.on('tileerror', () => { status.textContent = 'Les tuiles sont indisponibles. La fiche et le formulaire restent utilisables.'; });
    });
    const invalidate = () => window.setTimeout(() => map.invalidateSize(),0);
    window.addEventListener('resize',invalidate);
    document.addEventListener('fullscreenchange',invalidate);
    invalidate();
})();
</script>
