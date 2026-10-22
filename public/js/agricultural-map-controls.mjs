import {circleMetrics, circleToPolygon, convertArea, convertDistance, distanceMeters, formatFrenchMeasure, midpointOnSphere, polygonMetrics, polylineMetrics} from './agricultural-map-geometry.mjs';

const STORAGE_KEY = 'nutritrace-agricultural-map-layer';
const ESRI_TILES = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}';
const YELLOW = '#f5c518';

export function addAgriculturalBaseLayers(map, L, options = {}) {
    const osm = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    });
    const satellite = L.tileLayer(ESRI_TILES, {
        maxZoom: 19,
        attribution: 'Tiles &copy; <a href="https://www.esri.com/">Esri</a> — Sources: Esri, Maxar, Earthstar Geographics, and the GIS User Community',
    });
    let preferred = 'plan';
    try { preferred = options.osmDefault === true ? 'plan' : (localStorage.getItem(STORAGE_KEY) === 'satellite' ? 'satellite' : 'plan'); } catch { /* Storage can be disabled. */ }
    let activeLayer = preferred === 'satellite' ? satellite : osm;
    activeLayer.addTo(map);
    const toggle = () => {
        map.removeLayer(activeLayer);
        activeLayer = activeLayer === osm ? satellite : osm;
        activeLayer.addTo(map);
        try { localStorage.setItem(STORAGE_KEY, activeLayer === satellite ? 'satellite' : 'plan'); } catch { /* Ignore unavailable storage. */ }
    };
    const control = L.control({position: 'topleft'});
    control.onAdd = () => {
        const wrapper = L.DomUtil.create('div', 'leaflet-bar agricultural-layer-switch');
        const button = L.DomUtil.create('button', '', wrapper);
        button.type = 'button';
        button.innerHTML = layerIcon;
        button.title = 'Plan / Satellite';
        button.setAttribute('aria-label', 'Basculer entre Plan et Satellite');
        L.DomEvent.disableClickPropagation(wrapper);
        L.DomEvent.on(button, 'click', () => {
            toggle();
            button.setAttribute('aria-label', activeLayer === satellite ? 'Passer au fond Plan' : 'Passer au fond Satellite');
        });
        return wrapper;
    };
    if (options.showControl !== false) control.addTo(map);
    return {osm, satellite, toggle};
}

const layerIcon = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 7 9-4 9 4-9 4-9-4Zm0 5 9 4 9-4M3 17l9 4 9-4"/></svg>';

function buildMeasurePanel(container, L, onClose, onUseShape = null) {
    const panel = document.createElement('section');
    panel.className = 'agricultural-measure-panel';
    panel.hidden = true;
    panel.setAttribute('aria-label', 'Mesures de la forme sélectionnée');
    panel.innerHTML = `<div class="agri-panel-head"><strong>Mesurer</strong><button type="button" class="agri-close" aria-label="Fermer le panneau">×</button></div>
        <div data-row="perimeter"><label>Périmètre <select data-unit="distance" aria-label="Unité du périmètre"><option value="m">m</option><option value="km">km</option></select></label><output data-value="perimeter">—</output><button type="button" data-copy="perimeter" aria-label="Copier le périmètre">⧉</button></div>
        <div data-row="distance" hidden><label>Distance totale <select data-unit="distance" aria-label="Unité de la distance"><option value="m">m</option><option value="km">km</option></select></label><output data-value="distance">—</output><button type="button" data-copy="distance" aria-label="Copier la distance">⧉</button></div>
        <div data-row="area"><label>Zone <select data-unit="area" aria-label="Unité de la zone"><option value="m2">m²</option><option value="ha">ha</option><option value="km2">km²</option></select></label><output data-value="area">—</output><button type="button" data-copy="area" aria-label="Copier la zone">⧉</button></div>
        <div data-row="radius" hidden><label>Rayon <select data-unit="radius" aria-label="Unité du rayon"><option value="m">m</option><option value="km">km</option></select></label><output data-value="radius">—</output><button type="button" data-copy="radius" aria-label="Copier le rayon">⧉</button></div>
        <button type="button" class="agri-delete-shape" data-delete hidden>Supprimer cette forme</button>
        <button type="button" class="btn btn-sm btn-warning mt-2" data-use-shape hidden>Utiliser cette forme comme limite de la ferme</button>
        <small>Mesures approximatives, pas des limites cadastrales certifiées.</small>`;
    container.appendChild(panel);
    const getOutput = (key) => panel.querySelector(`[data-value="${key}"]`);
    const format = (value, unit) => formatFrenchMeasure(value, unit);
    const selected = {shape: null, onDelete: null};
    const hide = () => { panel.hidden = true; selected.shape = null; selected.onDelete = null; onClose?.(); };
    panel.querySelector('.agri-close').addEventListener('click', hide);
    panel.querySelector('[data-delete]').addEventListener('click', () => { selected.onDelete?.(); hide(); });
    panel.querySelector('[data-use-shape]').addEventListener('click', () => selected.shape && onUseShape?.(selected.shape));
    panel.querySelectorAll('[data-unit]').forEach((unit) => unit.addEventListener('change', () => selected.shape && render(selected.shape, selected.onDelete)));
    panel.querySelectorAll('[data-copy]').forEach((button) => button.addEventListener('click', async () => {
        const output = getOutput(button.dataset.copy);
        try { await navigator.clipboard.writeText(output.textContent.trim()); } catch {
            const input = document.createElement('textarea'); input.value = output.textContent.trim(); document.body.appendChild(input); input.select(); document.execCommand('copy'); input.remove();
        }
        button.textContent = 'Copié'; window.setTimeout(() => { button.textContent = '⧉'; }, 1200);
    }));
    const render = (shape, onDelete = null) => {
        selected.shape = shape; selected.onDelete = onDelete;
        panel.hidden = false;
        panel.querySelector('[data-delete]').hidden = !onDelete;
        panel.querySelector('[data-use-shape]').hidden = !onUseShape || shape.type === 'distance';
        const rows = Object.fromEntries(['perimeter', 'distance', 'area', 'radius'].map((key) => [key, panel.querySelector(`[data-row="${key}"]`)]));
        Object.values(rows).forEach((row) => { row.hidden = true; });
        let metrics;
        try {
            if (shape.type === 'circle') metrics = {...circleMetrics(shape.radiusMeters), perimeterMeters: circleMetrics(shape.radiusMeters).circumferenceMeters};
            else if (shape.type === 'distance') metrics = {totalMeters: polylineMetrics(shape.points).totalMeters};
            else metrics = polygonMetrics(shape.points);
        } catch { return; }
        if (shape.type === 'distance') {
            rows.distance.hidden = false;
            const unit = rows.distance.querySelector('[data-unit]').value;
            getOutput('distance').textContent = format(convertDistance(metrics.totalMeters, unit), unit);
            return;
        }
        rows.perimeter.hidden = false; rows.area.hidden = false;
        const distanceUnit = rows.perimeter.querySelector('[data-unit]').value;
        const areaUnit = rows.area.querySelector('[data-unit]').value;
        getOutput('perimeter').textContent = format(convertDistance(metrics.perimeterMeters, distanceUnit), distanceUnit);
        getOutput('area').textContent = format(convertArea(metrics.areaSquareMeters, areaUnit), areaUnit === 'm2' ? 'm²' : areaUnit);
        if (shape.type === 'circle') {
            rows.radius.hidden = false;
            const unit = rows.radius.querySelector('[data-unit]')?.value || 'm';
            getOutput('radius').textContent = format(convertDistance(metrics.radiusMeters, unit), unit);
        }
    };
    return {show: render, hide, panel};
}

export function createMeasurementPanel(container, L, onClose = null) {
    return buildMeasurePanel(container, L, onClose);
}

const icons = {
    polygon: '<path d="m4 18 2-12 11-2 3 12-16 2Z"/><circle cx="4" cy="18" r="1.4"/><circle cx="6" cy="6" r="1.4"/><circle cx="17" cy="4" r="1.4"/><circle cx="20" cy="16" r="1.4"/>',
    rectangle: '<rect x="4" y="5" width="16" height="14" rx="1"/>',
    circle: '<circle cx="12" cy="12" r="8"/>',
    distance: '<path d="m4 19 16-14M6 7l2 2m2-6 2 2m2 12 2 2m2-6 2 2"/>',
    recenter: '<circle cx="12" cy="12" r="7"/><path d="M12 2v4m0 12v4M2 12h4m12 0h4"/>',
    clear: '<path d="M4 7h16M9 7V4h6v3m3 0-1 13H7L6 7m4 4v5m4-5v5"/>',
    fullscreen: '<path d="M4 9V4h5m6 0h5v5m0 6v5h-5m-6 0H4v-5"/>',
    layer: '<path d="m3 7 9-4 9 4-9 4-9-4Zm0 5 9 4 9-4M3 17l9 4 9-4"/>',
};

export function installFarmMapMeasurement(map, L, container, status, options = {}) {
    if (options.tools === false) {
        const control = L.control({position: 'topleft'});
        control.onAdd = () => {
            const group = L.DomUtil.create('div', 'leaflet-bar agricultural-map-toolbar');
            const addButton = (label, icon, action) => {
                const button = L.DomUtil.create('button', '', group);
                button.type = 'button';
                button.title = label;
                button.setAttribute('aria-label', label);
                button.innerHTML = `<svg viewBox="0 0 24 24" aria-hidden="true">${icons[icon]}</svg>`;
                L.DomEvent.disableClickPropagation(button);
                L.DomEvent.on(button, 'click', action);
            };
            addButton('Basculer entre Plan et Satellite', 'layer', () => options.layers?.toggle());
            addButton('Plein écran', 'fullscreen', async () => {
                try {
                    if (document.fullscreenElement === container) await document.exitFullscreen();
                    else await container.requestFullscreen();
                    map.invalidateSize();
                } catch {
                    status.textContent = 'Le plein écran n’est pas disponible dans ce navigateur.';
                }
            });
            return group;
        };
        control.addTo(map);
        return {panel: null, clear() {}, addFeature() {}, select() {}, removeFeature() {}, loadPolygon() {}, features: []};
    }
    const panel = buildMeasurePanel(container, L, options.onDeselect, options.onUseShape);
    const markerPosition = options.markerPosition || null;
    const features = new Set();
    let mode = null, points = [], first = null, draft = null, hover = null, selected = null, selectedHandles = [];
    const toPoint = (latlng) => ({lat: latlng.lat, lng: latlng.lng});
    const style = {color: YELLOW, fillColor: YELLOW, fillOpacity: .2, weight: 3};
    const cleanHandles = () => { selectedHandles.forEach((layer) => map.removeLayer(layer)); selectedHandles = []; };
    const removeFeature = (feature) => { map.removeLayer(feature.layer); feature.handles?.forEach((handle) => map.removeLayer(handle)); features.delete(feature); if (selected === feature) selected = null; };
    const clear = () => {
        [...features].forEach(removeFeature); cleanHandles(); selected = null; panel.hide();
        if (draft) map.removeLayer(draft); if (hover) map.removeLayer(hover);
        draft = hover = null; points = []; first = null; mode = null;
        toolbarButtons.forEach(([, button]) => button.setAttribute('aria-pressed', 'false'));
    };
    const updateFeature = (feature) => {
        if (feature.type === 'circle') {
            feature.center = feature.layer.getLatLng(); feature.radiusMeters = feature.layer.getRadius();
        } else if (feature.type === 'rectangle') {
            feature.points = feature.layer.getLatLngs()[0].map(toPoint);
        }
        if (feature.type === 'polygon' || feature.type === 'rectangle') feature.layer.setLatLngs(feature.points);
        options.onFeatureChange?.(feature);
        if (selected === feature) panel.show(feature, () => removeFeature(feature));
    };
    const editable = (feature) => {
        feature.handles?.forEach((handle) => map.removeLayer(handle));
        feature.handles = [];
        if (feature.type === 'circle') {
            const center = L.marker(feature.center, {draggable: true, title: 'Déplacer le centre', icon: L.divIcon({className: 'agri-handle agri-vertex'})}).addTo(map);
            center.on('drag', () => { feature.center = center.getLatLng(); feature.layer.setLatLng(feature.center); });
            center.on('dragend', () => { updateFeature(feature); editable(feature); });
            const edge = map.containerPointToLatLng(map.latLngToContainerPoint(feature.center).add([0, -32]));
            const radius = L.marker(edge, {draggable: true, title: 'Modifier le rayon', icon: L.divIcon({className: 'agri-handle agri-midpoint'})}).addTo(map);
            radius.on('drag', () => { feature.radiusMeters = distanceMeters(toPoint(feature.center), toPoint(radius.getLatLng())); feature.layer.setRadius(feature.radiusMeters); });
            radius.on('dragend', () => { updateFeature(feature); editable(feature); });
            feature.handles.push(center, radius);
        } else {
            feature.points.forEach((point, index) => {
                const vertex = L.marker(point, {draggable: true, title: 'Déplacer un sommet', icon: L.divIcon({className: 'agri-handle agri-vertex'})}).addTo(map);
                vertex.on('drag', () => { feature.points[index] = toPoint(vertex.getLatLng()); feature.layer.setLatLngs(feature.points); if (selected === feature) panel.show(feature, () => removeFeature(feature)); });
                vertex.on('dragend', () => { updateFeature(feature); editable(feature); });
                feature.handles.push(vertex);
                if (feature.type === 'distance') return;
                const nextIndex = (index + 1) % feature.points.length;
                const mid = midpointOnSphere(point, feature.points[nextIndex]);
                const midpoint = L.marker(mid, {title: 'Ajouter un sommet', icon: L.divIcon({className: 'agri-handle agri-midpoint'})}).addTo(map);
                midpoint.on('click', () => { feature.points.splice(nextIndex, 0, mid); feature.layer.setLatLngs(feature.points); editable(feature); updateFeature(feature); });
                feature.handles.push(midpoint);
            });
        }
    };
    const select = (feature) => {
        cleanHandles(); selected = feature;
        if (options.editable !== false) editable(feature);
        panel.show(feature, options.editable === false ? null : () => removeFeature(feature));
    };
    const addFeature = (feature) => {
        features.add(feature);
        feature.layer.on('click', () => select(feature));
        select(feature);
        mode = null; points = []; first = null;
        if (draft) map.removeLayer(draft); draft = null;
        setActive(null);
        return feature;
    };
    const setActive = (tool) => {
        mode = tool;
        options.onDrawingChange?.(Boolean(tool));
        if (tool) map.doubleClickZoom.disable(); else map.doubleClickZoom.enable();
        toolbarButtons.forEach(([key, button]) => button.setAttribute('aria-pressed', String(key === tool)));
        if (tool) {
            points = []; first = null;
            if (draft) map.removeLayer(draft); if (hover) map.removeLayer(hover); draft = hover = null;
            panel.hide(); status.textContent = tool === 'rectangle' || tool === 'circle' ? 'Cliquez deux fois pour définir la forme.' : 'Cliquez pour ajouter des points; double-cliquez ou cliquez le premier point pour terminer.';
        }
    };
    const draw = (latlngs, type, extras = {}) => {
        const layer = type === 'circle' ? L.circle(extras.center, {...style, radius: extras.radiusMeters})
            : type === 'rectangle' ? L.polygon(latlngs, style)
                : type === 'distance' ? L.polyline(latlngs, {color: YELLOW, weight: 3}) : L.polygon(latlngs, style);
        layer.addTo(map);
        const feature = {type, layer, points: latlngs.map(toPoint), ...extras};
        return addFeature(feature);
    };
    const finishPolygon = () => {
        if (mode === 'distance' && points.length >= 2) return draw(points, 'distance');
        if (mode !== 'polygon' || points.length < 3) return;
        try { polygonMetrics(points); } catch { status.textContent = 'Forme non valide : évitez les croisements et les points confondus.'; return; }
        draw(points, 'polygon');
    };
    map.on('click', (event) => {
        if (!mode) return;
        const point = toPoint(event.latlng);
        if ((mode === 'polygon' || mode === 'distance') && mode === 'polygon' && points.length > 2 && distanceMeters(points[0], point) < 15) { finishPolygon(); return; }
        if (mode === 'polygon' || mode === 'distance') {
            points.push(point);
            if (draft) map.removeLayer(draft);
            draft = L.polyline(points, {color: YELLOW, weight: 3, dashArray: '5,7'}).addTo(map);
            if (mode === 'polygon' && points.length >= 3) {
                try { panel.show({type: 'polygon', points}); } catch { /* Keep the panel open while a polygon is incomplete. */ }
            } else if (mode === 'distance' && points.length >= 2) panel.show({type: 'distance', points});
            return;
        }
        if (!first) {
            first = event.latlng;
            panel.show(mode === 'circle' ? {type:'circle',center:first,radiusMeters:0} : {type:'rectangle',points:[toPoint(first)]});
            return;
        }
        if (mode === 'rectangle') {
            const bounds = L.latLngBounds(first, event.latlng);
            const corners = [bounds.getSouthWest(), bounds.getSouthEast(), bounds.getNorthEast(), bounds.getNorthWest()];
            draw(corners, 'rectangle');
        } else if (mode === 'circle') {
            const radiusMeters = distanceMeters(toPoint(first), point);
            if (radiusMeters > 0) draw([], 'circle', {center: first, radiusMeters});
        }
    });
    map.on('mousemove', (event) => {
        if (!mode) return;
        if (mode === 'polygon' || mode === 'distance') {
            if (!points.length) return;
            const preview = [...points, toPoint(event.latlng)];
            if (hover) map.removeLayer(hover);
            hover = L.polyline(preview, {color: YELLOW, weight: 2, dashArray: '3,5'}).addTo(map);
            if (mode === 'polygon' && points.length >= 2) panel.show({type:'polygon',points:preview});
            if (mode === 'distance' && points.length >= 1) panel.show({type:'distance',points:preview});
            return;
        }
        if (!first) return;
        if (draft) map.removeLayer(draft);
        if (mode === 'rectangle') {
            const bounds = L.latLngBounds(first,event.latlng);
            const corners = [bounds.getSouthWest(),bounds.getSouthEast(),bounds.getNorthEast(),bounds.getNorthWest()];
            draft = L.polygon(corners,style).addTo(map);
            panel.show({type:'rectangle',points:corners.map(toPoint)});
        } else if (mode === 'circle') {
            const radiusMeters = distanceMeters(toPoint(first),toPoint(event.latlng));
            draft = L.circle(first,{...style,radius:radiusMeters}).addTo(map);
            panel.show({type:'circle',center:first,radiusMeters});
        }
    });
    map.on('dblclick', () => { if (mode === 'polygon' || mode === 'distance') finishPolygon(); });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && mode) { setActive(null); points = []; first = null; if (draft) map.removeLayer(draft); if (hover) map.removeLayer(hover); draft = hover = null; panel.hide(); }
        if (event.key === 'Backspace' && mode && points.length) {
            event.preventDefault(); points.pop(); if (draft) map.removeLayer(draft);
            draft = points.length ? L.polyline(points, {color: YELLOW, weight: 3, dashArray: '5,7'}).addTo(map) : null;
            if (mode === 'distance' && points.length >= 2) panel.show({type:'distance',points});
            else if (mode === 'polygon' && points.length >= 3) panel.show({type:'polygon',points});
        }
    });
    const toolbar = L.control({position: 'topleft'});
    const toolbarButtons = [];
    toolbar.onAdd = () => {
        const group = L.DomUtil.create('div', 'leaflet-bar agricultural-map-toolbar');
        const add = (key, title, action) => {
            const button = L.DomUtil.create('button', '', group); button.type = 'button'; button.title = title; button.setAttribute('aria-label', title); button.setAttribute('aria-pressed', 'false');
            button.innerHTML = `<svg viewBox="0 0 24 24" aria-hidden="true">${icons[key]}</svg>`;
            L.DomEvent.disableClickPropagation(button); L.DomEvent.on(button, 'click', action); toolbarButtons.push([key, button]); return button;
        };
        add('polygon', 'Polygone — mesurer une zone', () => setActive('polygon'));
        add('rectangle', 'Rectangle', () => setActive('rectangle'));
        add('circle', 'Cercle', () => setActive('circle'));
        add('distance', 'Mesurer une distance', () => setActive('distance'));
        add('layer', 'Basculer entre Plan et Satellite', () => options.layers?.toggle());
        add('recenter', 'Recentrer sur la ferme', () => markerPosition ? map.setView(markerPosition, Math.max(map.getZoom(), 14)) : options.recenter?.());
        add('clear', 'Effacer les dessins', clear);
        add('fullscreen', 'Plein écran', async () => {
            try { if (document.fullscreenElement === container) await document.exitFullscreen(); else await container.requestFullscreen(); map.invalidateSize(); }
            catch { status.textContent = 'Le plein écran n’est pas disponible dans ce navigateur.'; }
        });
        return group;
    };
    toolbar.addTo(map);
    document.addEventListener('fullscreenchange', () => window.setTimeout(() => map.invalidateSize(), 80));
    const loadPolygon = (polygonPoints) => draw(polygonPoints, 'polygon');
    return {panel, clear, addFeature, select, removeFeature, loadPolygon, get features() { return [...features]; }};
}
