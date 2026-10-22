@extends('admin.layouts.layout')
@section('title', 'Carte des fermes — NutriTrace')
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
    #farms-map { min-height: 430px; height: min(62vh, 680px); position: relative; }
    #farms-map:fullscreen { height: 100vh; width: 100vw; max-width: none; border-radius: 0 !important; }
    .map-legend { display: flex; flex-wrap: wrap; gap: .75rem 1.25rem; }
    .shape-item { display: flex; justify-content: space-between; align-items: center; gap: .75rem; padding: .55rem 0; border-bottom: 1px solid var(--bs-border-color); }
    .shape-item:last-child { border-bottom: 0; }
    .agricultural-layer-switch button { border: 0; background: #fff; padding: .45rem; width: 2.5rem; height: 2.5rem; }
    .agricultural-layer-switch svg, .agricultural-map-toolbar svg { width: 1.25rem; height: 1.25rem; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; vertical-align: middle; }
    .agricultural-map-toolbar { display: flex; flex-direction: column; }
    .agricultural-map-toolbar button { width: 2.5rem; height: 2.5rem; border: 0; background: #fff; color: #343a40; }
    .agricultural-map-toolbar button[aria-pressed="true"] { background: #f5c518; }
    .agricultural-measure-panel { position: absolute; z-index: 500; top: .75rem; right: .75rem; width: min(320px, calc(100% - 1.5rem)); padding: 1rem; background: #fff; border-radius: .5rem; box-shadow: 0 2px 12px #0003; }
    .agricultural-measure-panel[hidden] { display: none; }
    #drawing-mode, label[for="drawing-mode"], #start-drawing, #clear-drawings { display: none !important; }
    .measure-handle { background: #fff; border: 2px solid #343a40; border-radius: 50%; width: 14px; height: 14px; }
    .agricultural-measure-panel { color: #212529; }
    .agri-panel-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:.65rem; }
    .agricultural-measure-panel [data-row] { display:grid; grid-template-columns:1fr auto auto; align-items:center; gap:.45rem; padding:.35rem 0; border-bottom:1px solid #eee; }
    .agricultural-measure-panel label { margin:0; }
    .agricultural-measure-panel output { font-variant-numeric:tabular-nums; font-weight:600; white-space:nowrap; }
    .agricultural-measure-panel select { border:0; background:#f3f4f6; border-radius:.25rem; margin-left:.25rem; }
    .agricultural-measure-panel [data-copy] { border:0; background:transparent; }
    .agricultural-measure-panel small { display:block; color:#667085; margin-top:.65rem; }
    .agri-delete-shape { border:0; background:transparent; color:#b42318; padding:.5rem 0 0; }
    @media (max-width: 575.98px) {
        #farms-map { min-height: 360px; height: 52vh; }
        .agricultural-map-toolbar { position: fixed; left: 50%; top: auto; bottom: 1rem; transform: translateX(-50%); flex-direction: row; max-width: calc(100vw - 1.5rem); overflow-x: auto; z-index: 1000; }
        .agricultural-measure-panel { position: fixed; left: .5rem; right: .5rem; bottom: .5rem; top: auto; width: auto; max-height: 45vh; overflow:auto; }
    }
</style>
@endpush
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><h1 class="h3 mb-1">Carte des fermes</h1><p class="text-muted mb-0">Explorez les fermes géolocalisées et mesurez des zones de planification.</p></div>
    <a class="btn btn-outline-primary" href="{{ route('admin.fermes.index') }}">Gérer les fermes</a>
</div>

<div class="row g-3 mb-4" aria-live="polite">
    <div class="col-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Fermes correspondant aux filtres</div><div class="fs-4 fw-bold">{{ $matchingCount }}</div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Avec coordonnées valides</div><div class="fs-4 fw-bold">{{ $locatedCount }}</div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Sans coordonnées GPS valides</div><div class="fs-4 fw-bold">{{ $withoutValidCoordinatesCount }}</div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Marqueurs affichés</div><div class="fs-4 fw-bold" id="visible-marker-count">{{ $markers->count() }}</div></div></div></div>
</div>

<div class="card mb-4"><div class="card-body"><form method="GET" class="row gx-3 gy-2 align-items-end">
    <div class="col-md-3"><label for="map-search" class="form-label">Ferme ou localisation</label><input id="map-search" name="q" type="search" class="form-control" value="{{ request('q') }}" placeholder="Nom ou localisation"></div>
    <div class="col-md-3"><label for="producer-filter" class="form-label">Producteur</label><select id="producer-filter" name="producteur_id" class="form-select"><option value="">Tous les producteurs</option>@foreach($producteurs as $producteur)<option value="{{ $producteur->id }}" @selected(request('producteur_id') == $producteur->id)>{{ $producteur->prenom }} {{ $producteur->nom }}</option>@endforeach</select></div>
    <div class="col-md-2"><label for="area-min" class="form-label">Superficie min. (ha)</label><input id="area-min" name="superficie_min" type="number" min="0" step="0.01" class="form-control" value="{{ request('superficie_min') }}"></div>
    <div class="col-md-2"><label for="area-max" class="form-label">Superficie max. (ha)</label><input id="area-max" name="superficie_max" type="number" min="0" step="0.01" class="form-control" value="{{ request('superficie_max') }}"></div>
    <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary">Filtrer</button><a class="btn btn-outline-secondary" href="{{ route('admin.agriculture.map') }}">Effacer</a></div>
</form></div></div>

<div class="card mb-4"><div class="card-body">
    <div class="d-flex flex-wrap align-items-end gap-2 mb-3">
        <div><label for="drawing-mode" class="form-label">Outil de mesure</label><select id="drawing-mode" class="form-select"><option value="polygon">Polygone</option><option value="rectangle">Rectangle</option><option value="circle">Cercle</option><option value="distance">Mesurer une distance</option></select></div>
        <button id="start-drawing" class="btn btn-outline-primary">Commencer le dessin</button>
        <button id="finish-polygon" class="btn btn-primary" disabled>Terminer le dessin</button>
        <button id="cancel-drawing" class="btn btn-outline-secondary" disabled>Annuler le dessin</button>
        <button id="clear-drawings" class="btn btn-outline-danger ms-md-auto">Effacer les dessins</button>
    </div>
    <p id="map-status" class="small text-muted" role="status" aria-live="polite">Chargement de la carte…</p>
    <div id="farms-map" class="rounded border" data-measurement-map data-map-tools role="application" aria-label="Carte interactive des fermes. Utilisez les commandes de dessin pour mesurer une zone."></div>
    <div class="map-legend small mt-3" aria-label="Légende de la carte"><span><span class="badge rounded-pill bg-primary">●</span> Ferme géolocalisée</span><span>Les formes et mesures sont temporaires et ne représentent pas des limites cadastrales vérifiées.</span><span id="shape-counts">0 rectangle · 0 cercle · 0 polygone</span></div>
    <div id="shape-results" class="mt-2" aria-live="polite"></div>
    <p class="form-text mt-3 mb-0">Mesures approximatives sur la surface terrestre, en mètres et hectares. Les fermes comptées dans une forme sont celles dont le point GPS enregistré se trouve à l’intérieur ; cela ne signifie pas que leur parcelle entière s’y trouve. © <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a> contributors.</p>
</div></div>

<div class="card"><div class="card-body"><h2 class="h5">Fermes géolocalisées affichées ({{ $locatedCount }} sur {{ $matchingCount }} correspondantes)</h2><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Ferme</th><th>Producteur</th><th>Localisation</th><th>Superficie</th><th>Coordonnées</th></tr></thead><tbody>
@forelse($fermes as $ferme)<tr><td><a href="{{ route('admin.fermes.show', $ferme) }}">{{ $ferme->nom }}</a></td><td>{{ $ferme->producteur->prenom }} {{ $ferme->producteur->nom }}</td><td>{{ $ferme->localisation }}</td><td>{{ $ferme->superficie ? $ferme->superficie.' ha' : '—' }}</td><td>{{ $ferme->latitude }}, {{ $ferme->longitude }}</td></tr>@empty<tr><td colspan="5" class="text-muted">Aucun marqueur à afficher. Les fermes sans coordonnées valides restent accessibles depuis la liste générale.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script type="module">
import {circleMetrics, countPointsInShape, distanceMeters, isSimplePolygon, polygonMetrics, polylineMetrics, midpointOnSphere} from @json(asset('js/agricultural-map-geometry.mjs'));
import {addAgriculturalBaseLayers, createMeasurementPanel} from @json(asset('js/agricultural-map-controls.mjs'));
(() => {
    const container = document.getElementById('farms-map');
    if (!container || container.dataset.leafletInitialised === 'true') return;
    container.dataset.leafletInitialised = 'true';

    const farms = @json($markers);
    const status = document.getElementById('map-status');
    if (!window.L) {
        status.textContent = 'La bibliothèque de carte est indisponible. Les statistiques et la liste des fermes restent accessibles.';
        return;
    }
    const markerCount = document.getElementById('visible-marker-count');
    const map = L.map(container).setView([34, 9], 6);
    const baseLayers = addAgriculturalBaseLayers(map, L, {showControl:false});
    const markerBounds = L.latLngBounds([]);
    const validPoint = (farm) => Number.isFinite(farm.latitude) && Number.isFinite(farm.longitude)
        && farm.latitude >= -90 && farm.latitude <= 90 && farm.longitude >= -180 && farm.longitude <= 180;

    const appendText = (parent, tag, label, value) => {
        const row = document.createElement(tag);
        row.textContent = label ? `${label}: ${value || 'Non renseigné'}` : value;
        parent.appendChild(row);
    };
    farms.filter(validPoint).forEach((farm) => {
        const marker = L.marker([farm.latitude, farm.longitude]).addTo(map);
        const popup = document.createElement('div');
        const title = document.createElement('strong');
        title.textContent = farm.nom;
        popup.appendChild(title);
        appendText(popup, 'p', 'Producteur', farm.producteur);
        appendText(popup, 'p', 'Localisation', farm.localisation);
        appendText(popup, 'p', 'Superficie', farm.superficie ? `${farm.superficie} ha` : null);
        appendText(popup, 'p', 'Coordonnées', `${farm.latitude}, ${farm.longitude}`);
        appendText(popup, 'p', 'Description', farm.description || 'Non renseignée');
        const detailLink = document.createElement('a');
        detailLink.href = farm.url;
        detailLink.textContent = 'Ouvrir la fiche ferme';
        popup.appendChild(detailLink);
        const googleLink = document.createElement('a');
        googleLink.href = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(`${farm.latitude},${farm.longitude}`)}`;
        googleLink.target = '_blank';
        googleLink.rel = 'noopener noreferrer';
        googleLink.className = 'd-block';
        googleLink.textContent = 'Ouvrir dans Google Maps';
        popup.appendChild(googleLink);
        marker.bindPopup(popup);
        markerBounds.extend(marker.getLatLng());
    });
    const safeFarmPoints = farms.filter(validPoint).map((farm) => L.latLng(farm.latitude, farm.longitude));
    markerCount.textContent = String(safeFarmPoints.length);
    if (markerBounds.isValid()) map.fitBounds(markerBounds, {padding: [24, 24], maxZoom: 14});
    else status.textContent = 'Aucune ferme avec des coordonnées valides ne correspond aux filtres.';
    map.addControl(L.control.scale({metric: true, imperial: false}));
    [baseLayers.osm, baseLayers.satellite].forEach((layer) => {
        layer.on('load', () => { if (safeFarmPoints.length) status.textContent = 'Carte prête.'; });
        layer.on('tileerror', () => { status.textContent = 'Les tuiles cartographiques sont indisponibles. La liste et les données restent accessibles.'; });
    });
    const measurementPanel = createMeasurementPanel(container, L);

    const resultList = document.getElementById('shape-results');
    const shapeCounts = document.getElementById('shape-counts');
    const drawingMode = document.getElementById('drawing-mode');
    const startButton = document.getElementById('start-drawing');
    const finishButton = document.getElementById('finish-polygon');
    const cancelButton = document.getElementById('cancel-drawing');
    const clearButton = document.getElementById('clear-drawings');
    const toolbar = L.control({position: 'topleft'});
    const toolbarIcons = {
        search: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg>',
        polygon: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 18 2-12 11-2 3 12-16 2Z"/></svg>',
        rectangle: '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="14" rx="1"/></svg>',
        circle: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8"/></svg>',
        distance: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 19 16-14M6 7l2 2m2-6 2 2m2 12 2 2m2-6 2 2"/></svg>',
        layers: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 7 9-4 9 4-9 4-9-4Zm0 5 9 4 9-4M3 17l9 4 9-4"/></svg>',
        center: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="7"/><path d="M12 2v4m0 12v4M2 12h4m12 0h4"/></svg>',
        clear: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m3 0-1 13H7L6 7m4 4v5m4-5v5"/></svg>',
        fullscreen: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9V4h5m6 0h5v5m0 6v5h-5m-6 0H4v-5"/></svg>',
    };
    toolbar.onAdd = () => {
        const group = L.DomUtil.create('div', 'leaflet-bar agricultural-map-toolbar');
        const addTool = (icon, label, action, pressed = false) => {
            const button = L.DomUtil.create('button', '', group);
            button.type = 'button'; button.innerHTML = toolbarIcons[icon];
            button.title = label; button.setAttribute('aria-label', label); button.setAttribute('aria-pressed', String(pressed));
            L.DomEvent.disableClickPropagation(button); L.DomEvent.on(button, 'click', action);
            return button;
        };
        addTool('search', 'Rechercher une ferme', () => document.getElementById('map-search').focus());
        const drawingTools = [
            ['polygon', 'Polygone', 'polygon'], ['rectangle', 'Rectangle', 'rectangle'],
            ['circle', 'Cercle', 'circle'], ['distance', 'Mesurer une distance', 'distance'],
        ].map(([icon, label, type]) => addTool(icon, label, () => {
            drawingMode.value = type;
            if (mode) cancelButton.click();
            startButton.click();
            const selectedTool = drawingTools.find((button) => button.getAttribute('aria-label') === label);
            selectedTool?.setAttribute('aria-pressed', 'true');
        }));
        addTool('layers', 'Basculer Plan / Satellite', () => {
            baseLayers.toggle();
        });
        addTool('center', 'Recentrer la carte', () => { if (markerBounds.isValid()) map.fitBounds(markerBounds, {padding: [24, 24], maxZoom: 14}); else map.setView([34, 9], 6); });
        addTool('clear', 'Effacer les dessins', () => clearButton.click());
        addTool('fullscreen', 'Plein écran', async () => { try { if (document.fullscreenElement === container) await document.exitFullscreen(); else await container.requestFullscreen(); map.invalidateSize(); } catch { status.textContent = 'Le plein écran n’est pas disponible dans ce navigateur.'; } });
        startButton.addEventListener('click', () => drawingTools.forEach((button) => button.setAttribute('aria-pressed', 'false')));
        return group;
    };
    toolbar.addTo(map);
    const shapes = [];
    let mode = null;
    let firstPoint = null;
    let polygonPoints = [];
    let draftLayer = null;
    let hoverLayer = null;

    const formatDistance = (metres) => metres >= 1000 ? `${(metres / 1000).toFixed(2)} km` : `${metres.toFixed(0)} m`;
    const formatArea = (squareMetres) => `${squareMetres.toLocaleString('fr-FR', {maximumFractionDigits: 0})} m² (${(squareMetres / 10000).toFixed(3)} ha)`;
    const shapePointCount = (shape) => shape.type === 'distance' ? 0 : countPointsInShape(safeFarmPoints, shape);

    const refreshShapeSummary = () => {
        const countByType = (type) => shapes.filter((shape) => shape.type === type).length;
        shapeCounts.textContent = `${countByType('rectangle')} rectangle · ${countByType('circle')} cercle · ${countByType('polygon')} polygone${countByType('polygon') > 1 ? 's' : ''} · ${countByType('distance')} distance`;
        resultList.replaceChildren();
        if (!shapes.length) {
            const empty = document.createElement('p');
            empty.className = 'small text-muted mb-0';
            empty.textContent = 'Aucun dessin. Choisissez un outil pour commencer.';
            resultList.appendChild(empty);
            clearButton.disabled = true;
            return;
        }
        clearButton.disabled = false;
        shapes.forEach((shape, index) => {
            const item = document.createElement('div');
            item.className = 'shape-item';
            const details = document.createElement('div');
            details.className = 'small';
            details.textContent = shape.type === 'distance' ? `${shape.label} — ${shape.measurement}` : `${shape.label} — ${shape.measurement}; ${shape.farmCount} point${shape.farmCount > 1 ? 's' : ''} GPS de ferme à l’intérieur`;
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-sm btn-outline-danger';
            remove.textContent = 'Supprimer';
            remove.setAttribute('aria-label', `Supprimer ${shape.label}`);
            remove.addEventListener('click', () => {
                map.removeLayer(shape.layer);
                (shape.handleLayers || []).forEach((handle) => map.removeLayer(handle));
                shapes.splice(index, 1);
                refreshShapeSummary();
            });
            item.append(details, remove);
            resultList.appendChild(item);
        });
    };
    const cancelCurrentDrawing = (message = null) => {
        mode = null;
        map.doubleClickZoom.enable();
        firstPoint = null;
        polygonPoints = [];
        if (draftLayer) map.removeLayer(draftLayer);
        if (hoverLayer) map.removeLayer(hoverLayer);
        draftLayer = null;
        finishButton.disabled = true;
        cancelButton.disabled = true;
        startButton.disabled = false;
        drawingMode.disabled = false;
        status.textContent = message || (safeFarmPoints.length ? 'Carte prête.' : 'Aucune ferme avec des coordonnées valides ne correspond aux filtres.');
    };
    const saveShape = (shape) => {
        shape.farmCount = shapePointCount(shape);
        shapes.push(shape);
        const refreshHandles = () => {
            (shape.handleLayers || []).forEach((handle) => map.removeLayer(handle));
            const handles = [];
            shape.handleLayers = handles;
            let centerHandle, radiusHandle;
            const updateGeometry = () => {
                if (shape.type === 'circle') {
                    shape.center = centerHandle.getLatLng();
                    shape.radiusMeters = distanceMeters(shape.center, radiusHandle.getLatLng());
                    shape.layer.setLatLng(shape.center); shape.layer.setRadius(shape.radiusMeters);
                } else {
                    if (!isSimplePolygon(shape.points)) return;
                    shape.layer.setLatLngs(shape.points);
                    if (shape.type === 'rectangle') {
                        shape.south = Math.min(...shape.points.map((point) => point.lat)); shape.north = Math.max(...shape.points.map((point) => point.lat));
                        shape.west = Math.min(...shape.points.map((point) => point.lng)); shape.east = Math.max(...shape.points.map((point) => point.lng));
                    }
                }
                const metric = shape.type === 'circle' ? circleMetrics(shape.radiusMeters) : polygonMetrics(shape.points);
                shape.measurement = shape.type === 'circle'
                    ? `rayon ${formatDistance(metric.radiusMeters)} · ${formatArea(metric.areaSquareMeters)} · circonférence ${formatDistance(metric.circumferenceMeters)}`
                    : `${formatArea(metric.areaSquareMeters)} · périmètre ${formatDistance(metric.perimeterMeters)}`;
                shape.farmCount = shapePointCount(shape);
                measurementPanel.show(shape, () => removeShape(shape));
                refreshShapeSummary();
            };
            if (shape.type === 'circle') {
                const centerHandle = L.marker(shape.center, {draggable:true, icon:L.divIcon({className:'measure-handle', iconSize:[14,14]}), title:'Déplacer le centre'}).addTo(map);
                const radiusPosition = L.latLng(shape.center.lat, shape.layer.getBounds().getEast());
                const radiusHandle = L.marker(radiusPosition, {draggable:true, icon:L.divIcon({className:'measure-handle', iconSize:[11,11]}), title:'Modifier le rayon'}).addTo(map);
                centerHandle.on('drag', updateGeometry); radiusHandle.on('drag', updateGeometry);
                centerHandle.on('dragend', refreshHandles); radiusHandle.on('dragend', refreshHandles);
                handles.push(centerHandle, radiusHandle);
            } else {
                shape.points.forEach((point, index) => {
                    const vertex = L.marker(point, {draggable:true, icon:L.divIcon({className:'measure-handle', iconSize:[14,14]}), title:'Déplacer un sommet'}).addTo(map);
                    vertex.on('drag', () => { shape.points[index] = {lat:vertex.getLatLng().lat, lng:vertex.getLatLng().lng}; updateGeometry(); });
                    vertex.on('dragend', refreshHandles); handles.push(vertex);
                    const next = (index + 1) % shape.points.length;
                    const middle = midpointOnSphere(point, shape.points[next]);
                    const midHandle = L.marker(middle, {icon:L.divIcon({className:'measure-handle', iconSize:[10,10]}), title:'Ajouter un sommet'}).addTo(map);
                    midHandle.on('click', () => { shape.points.splice(next,0,middle); updateGeometry(); refreshHandles(); });
                    handles.push(midHandle);
                });
            }
        };
        const removeShape = (target) => {
            map.removeLayer(target.layer); (target.handleLayers || []).forEach((handle) => map.removeLayer(handle));
            const index = shapes.indexOf(target); if (index >= 0) shapes.splice(index,1);
            refreshShapeSummary();
        };
        if (shape.type === 'polygon' || shape.type === 'rectangle' || shape.type === 'circle') {
            const renderHandles = () => {
                refreshHandles();
            };
            shape.layer.on('click', renderHandles);
        }
        shape.layer.on('click', () => measurementPanel.show(shape, () => removeShape(shape)));
        measurementPanel.show(shape, () => removeShape(shape));
        refreshShapeSummary();
        cancelCurrentDrawing();
    };
    const boundsAsPolygon = (bounds) => [bounds.getSouthWest(), L.latLng(bounds.getSouthEast()), bounds.getNorthEast(), L.latLng(bounds.getNorthWest())];

    startButton.addEventListener('click', () => {
        mode = drawingMode.value;
        map.doubleClickZoom.disable();
        firstPoint = null;
        polygonPoints = [];
        startButton.disabled = true;
        drawingMode.disabled = true;
        cancelButton.disabled = false;
        finishButton.disabled = true;
        status.textContent = mode === 'rectangle' ? 'Cliquez deux fois : premier puis second coin du rectangle.' : mode === 'circle' ? 'Cliquez au centre puis sur le bord du cercle pour définir son rayon.' : mode === 'distance' ? 'Cliquez pour placer les points de la distance, puis terminez.' : 'Cliquez pour placer au moins trois sommets, puis terminez le polygone.';
    });
    cancelButton.addEventListener('click', () => { cancelCurrentDrawing(); measurementPanel.hide(); });
    clearButton.addEventListener('click', () => {
        shapes.forEach((shape) => { map.removeLayer(shape.layer); (shape.handleLayers || []).forEach((handle) => map.removeLayer(handle)); });
        shapes.splice(0, shapes.length);
        measurementPanel.hide();
        cancelCurrentDrawing();
        refreshShapeSummary();
    });
    finishButton.addEventListener('click', () => {
        if (mode === 'distance' && polygonPoints.length >= 2) {
            const points = [...polygonPoints];
            const metrics = polylineMetrics(points);
            if (draftLayer) map.removeLayer(draftLayer);
            const layer = L.polyline(points, {color: '#f5c518', weight: 3}).addTo(map);
            saveShape({type: 'distance', label: 'Distance', points, layer, measurement: `${formatDistance(metrics.totalMeters)} (${metrics.segments.length} segments)`});
            return;
        }
        if (mode !== 'polygon' || polygonPoints.length < 3) return;
        if (!isSimplePolygon(polygonPoints)) {
            cancelCurrentDrawing('Polygone invalide : tracez une forme simple, non dégénérée, sans traverser l’antiméridien.');
            return;
        }
        if (draftLayer) map.removeLayer(draftLayer);
        const points = [...polygonPoints];
        const layer = L.polygon(points, {color: '#f5c518', fillColor: '#f5c518', fillOpacity: .18, weight: 3}).addTo(map);
        const metrics = polygonMetrics(points);
        saveShape({type: 'polygon', label: 'Polygone', points, layer, measurement: `${formatArea(metrics.areaSquareMeters)} · périmètre ${formatDistance(metrics.perimeterMeters)}`});
    });
    map.on('click', (event) => {
        if (!mode) return;
        const point = event.latlng;
        if (mode === 'distance' || mode === 'polygon') {
            if (mode === 'polygon' && polygonPoints.length >= 3 && distanceMeters(polygonPoints[0], point) < 15) {
                finishButton.click();
                return;
            }
            if (polygonPoints.length >= 200) return;
            polygonPoints.push({lat: point.lat, lng: point.lng});
            if (draftLayer) map.removeLayer(draftLayer);
            draftLayer = L.polyline(polygonPoints, {color: '#f5c518', dashArray: '5, 8'}).addTo(map);
            finishButton.disabled = polygonPoints.length < (mode === 'distance' ? 2 : 3);
            if ((mode === 'distance' && polygonPoints.length >= 2) || (mode === 'polygon' && polygonPoints.length >= 3)) measurementPanel.show({type: mode, points: polygonPoints});
            return;
        }
        if (mode === 'polygon') {
            if (polygonPoints.length >= 200) {
                status.textContent = 'Un polygone est limité à 200 sommets. Terminez-le ou annulez le dessin.';
                return;
            }
            polygonPoints.push(point);
            if (draftLayer) map.removeLayer(draftLayer);
            draftLayer = L.polyline(polygonPoints, {color: '#198754', dashArray: '5, 8'}).addTo(map);
            finishButton.disabled = polygonPoints.length < 3;
            return;
        }
        if (!firstPoint) {
            firstPoint = point;
            measurementPanel.show(mode === 'circle' ? {type:'circle', center:point, radiusMeters:0} : {type:'rectangle', points:[point]});
            return;
        }
        if (mode === 'rectangle') {
            const bounds = L.latLngBounds(firstPoint, point);
            if (bounds.getEast() - bounds.getWest() > 180) {
                cancelCurrentDrawing('Un rectangle qui traverse l’antiméridien doit être dessiné en plusieurs zones.');
                return;
            }
            const points = boundsAsPolygon(bounds);
            if (!isSimplePolygon(points)) {
                cancelCurrentDrawing('Ce rectangle ne forme pas une surface mesurable. Le dessin a été annulé.');
                return;
            }
            const metrics = polygonMetrics(points);
            const layer = L.polygon(points, {color: '#f5c518', fillColor: '#f5c518', fillOpacity: .18, weight: 3}).addTo(map);
            saveShape({type: 'rectangle', label: 'Rectangle', points: points.map((point) => ({lat:point.lat,lng:point.lng})), south: bounds.getSouth(), west: bounds.getWest(), north: bounds.getNorth(), east: bounds.getEast(), layer, measurement: `${formatArea(metrics.areaSquareMeters)} · périmètre ${formatDistance(metrics.perimeterMeters)}`});
        } else if (mode === 'circle') {
            const radius = distanceMeters(firstPoint, point);
            let metrics;
            try { metrics = circleMetrics(radius); } catch {
                cancelCurrentDrawing('Le rayon doit être supérieur à zéro et rester inférieur à la moitié du globe.');
                return;
            }
            const layer = L.circle(firstPoint, {radius, color: '#f5c518', fillColor: '#f5c518', fillOpacity: .18, weight: 3}).addTo(map);
            saveShape({type: 'circle', label: 'Cercle', center: firstPoint, radiusMeters: radius, layer, measurement: `rayon ${formatDistance(metrics.radiusMeters)} · ${formatArea(metrics.areaSquareMeters)} · circonférence ${formatDistance(metrics.circumferenceMeters)}`});
        }
    });

    map.on('mousemove', (event) => {
        if (!mode) return;
        if (hoverLayer) map.removeLayer(hoverLayer);
        const point = {lat:event.latlng.lat,lng:event.latlng.lng};
        if (mode === 'polygon' || mode === 'distance') {
            if (polygonPoints.length) hoverLayer = L.polyline([...polygonPoints, point], {color:'#f5c518',weight:2,dashArray:'3,5'}).addTo(map);
            if (mode === 'polygon' && polygonPoints.length >= 2) measurementPanel.show({type:'polygon',points:[...polygonPoints,point]});
            if (mode === 'distance' && polygonPoints.length >= 1) measurementPanel.show({type:'distance',points:[...polygonPoints,point]});
        } else if (firstPoint && mode === 'rectangle') {
            const bounds = L.latLngBounds(firstPoint,event.latlng);
            const points = boundsAsPolygon(bounds);
            if (draftLayer) map.removeLayer(draftLayer);
            draftLayer = L.polygon(points,{color:'#f5c518',fillColor:'#f5c518',fillOpacity:.2,weight:3}).addTo(map);
            measurementPanel.show({type:'rectangle',points});
        } else if (firstPoint && mode === 'circle') {
            const radiusMeters = distanceMeters(firstPoint,event.latlng);
            if (draftLayer) map.removeLayer(draftLayer);
            draftLayer = L.circle(firstPoint,{radius:radiusMeters,color:'#f5c518',fillColor:'#f5c518',fillOpacity:.2,weight:3}).addTo(map);
            measurementPanel.show({type:'circle',center:firstPoint,radiusMeters});
        }
    });

    map.on('dblclick', () => { if ((mode === 'polygon' && polygonPoints.length >= 3) || (mode === 'distance' && polygonPoints.length >= 2)) finishButton.click(); });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && mode) { cancelCurrentDrawing('Dessin annulé.'); measurementPanel.hide(); }
        if (event.key === 'Backspace' && mode && polygonPoints.length) {
            event.preventDefault(); polygonPoints.pop();
            if (draftLayer) map.removeLayer(draftLayer);
            draftLayer = polygonPoints.length ? L.polyline(polygonPoints,{color:'#f5c518',dashArray:'5,8'}).addTo(map) : null;
            finishButton.disabled = polygonPoints.length < (mode === 'distance' ? 2 : 3);
            if (mode === 'distance' && polygonPoints.length >= 2) measurementPanel.show({type:'distance',points:polygonPoints});
            else if (mode === 'polygon' && polygonPoints.length >= 3) measurementPanel.show({type:'polygon',points:polygonPoints});
        }
    });

    refreshShapeSummary();
    const invalidateMap = () => window.setTimeout(() => map.invalidateSize(), 0);
    window.addEventListener('resize', invalidateMap);
    document.addEventListener('fullscreenchange', invalidateMap);
    document.addEventListener('shown.bs.tab', invalidateMap);
    document.addEventListener('shown.bs.modal', invalidateMap);
    invalidateMap();
})();
</script>
@endpush
