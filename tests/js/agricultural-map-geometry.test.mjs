import test from 'node:test';
import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';
import {
    EARTH_RADIUS_METERS,
    circleMetrics,
    circleToPolygon,
    countPointsInShape,
    convertArea,
    convertDistance,
    distanceMeters,
    formatFrenchMeasure,
    isSimplePolygon,
    midpointOnSphere,
    polylineMetrics,
    polygonMetrics,
} from '../../public/js/agricultural-map-geometry.mjs';

const square = [
    {lat: 0, lng: 0},
    {lat: 0, lng: 0.01},
    {lat: 0.01, lng: 0.01},
    {lat: 0.01, lng: 0},
];

test('polygon measurement uses spherical area and geodesic perimeter', () => {
    const measured = polygonMetrics(square);

    assert.ok(measured.areaSquareMeters > 1_200_000 && measured.areaSquareMeters < 1_250_000);
    assert.ok(measured.areaHectares > 120 && measured.areaHectares < 125);
    assert.ok(measured.perimeterMeters > 4_400 && measured.perimeterMeters < 4_500);
});

test('circle measurement converts radius to spherical area and circumference', () => {
    const measured = circleMetrics(1000);

    assert.ok(Math.abs(measured.areaSquareMeters - Math.PI * 1_000_000) < 200);
    assert.equal(measured.areaHectares, measured.areaSquareMeters / 10000);
    assert.ok(Math.abs(measured.circumferenceMeters - 2 * Math.PI * 1000) < 1);
    assert.throws(() => circleMetrics(0), RangeError);
    assert.throws(() => circleMetrics(Math.PI * EARTH_RADIUS_METERS + 1), RangeError);
});

test('circle boundaries are approximated by a closed-ready 64-point polygon', () => {
    const points = circleToPolygon({lat: 36, lng: 9}, 1000);
    assert.equal(points.length, 64);
    assert.ok(points.every((point) => Number.isFinite(point.lat) && Number.isFinite(point.lng)));
    assert.throws(() => circleToPolygon({lat: 91, lng: 9}, 1000), RangeError);
});

test('invalid and self-intersecting polygons are rejected safely', () => {
    const bowTie = [
        {lat: 0, lng: 0}, {lat: 0.01, lng: 0.01},
        {lat: 0, lng: 0.01}, {lat: 0.01, lng: 0},
    ];

    assert.equal(isSimplePolygon(bowTie), false);
    assert.throws(() => polygonMetrics(bowTie), RangeError);
    assert.throws(() => polygonMetrics([{lat: 0, lng: 0}, {lat: 0, lng: 0}, {lat: 0, lng: 0}]), RangeError);
    assert.equal(isSimplePolygon([{lat: 0, lng: 179}, {lat: 1, lng: -179}, {lat: 2, lng: 179}]), false);
});

test('shape counts use farm GPS points, not inferred farm boundaries', () => {
    const points = [{lat: 0.005, lng: 0.005}, {lat: 0.02, lng: 0.02}, {lat: 0.005, lng: 0.005}];
    const rectangle = {type: 'rectangle', south: 0, west: 0, north: 0.01, east: 0.01};
    const circle = {type: 'circle', center: {lat: 0, lng: 0}, radiusMeters: 1000};
    const polygon = {type: 'polygon', points: square};

    assert.equal(countPointsInShape(points, rectangle), 2);
    assert.equal(countPointsInShape(points, circle), 2);
    assert.equal(countPointsInShape(points, polygon), 2);
});

test('distance calculation handles the shortest path across the antimeridian', () => {
    const distance = distanceMeters({lat: 0, lng: 179.9}, {lat: 0, lng: -179.9});

    assert.ok(distance > 22_000 && distance < 23_000);
});

test('spherical midpoint and polyline metrics use geodesic distances', () => {
    const midpoint = midpointOnSphere({lat: 0, lng: 0}, {lat: 0, lng: 2});
    assert.ok(Math.abs(midpoint.lat) < 1e-10);
    assert.ok(Math.abs(midpoint.lng - 1) < 1e-10);
    const measured = polylineMetrics([{lat: 0, lng: 0}, {lat: 0, lng: 1}, {lat: 1, lng: 1}]);
    assert.equal(measured.segments.length, 2);
    assert.ok(Math.abs(measured.totalMeters - measured.segments.reduce((sum, segment) => sum + segment, 0)) < 1e-8);
    assert.throws(() => midpointOnSphere({lat: 0, lng: 0}, {lat: 0, lng: 180}), RangeError);
    assert.throws(() => polylineMetrics([{lat: 0, lng: 0}]), RangeError);
});

test('shared map controls keep OSM default, add attributed Esri imagery and reusable measurement UI', async () => {
    const controls = await readFile(new URL('../../public/js/agricultural-map-controls.mjs', import.meta.url), 'utf8');
    assert.match(controls, /tile\.openstreetmap\.org\/\{z\}\/\{x\}\/\{y\}\.png/);
    assert.match(controls, /World_Imagery\/MapServer\/tile\/\{z\}\/\{y\}\/\{x\}/);
    assert.match(controls, /OpenStreetMap/);
    assert.match(controls, /Esri/);
    assert.match(controls, /localStorage/);
    assert.match(controls, /Mesurer une distance/);
    assert.match(controls, /Copié/);
    assert.match(controls, /agricultural-map-toolbar/);
    assert.match(controls, /data-row=/);
    assert.match(controls, /Supprimer cette forme/);
    assert.match(controls, /requestFullscreen/);
    assert.match(controls, /aria-label/);
    assert.match(controls, /Périmètre/);
    assert.match(controls, /Zone/);
    assert.match(controls, /Rayon/);
    assert.match(controls, /Distance totale/);
    assert.match(controls, /agricultural-measure-panel/);
});

test('measurement unit conversion and French formatting are explicit and validated', () => {
    assert.equal(convertDistance(1250, 'km'), 1.25);
    assert.equal(convertDistance(1250, 'm'), 1250);
    assert.ok(Math.abs(convertArea(170052.07, 'ha') - 17.005207) < 1e-12);
    assert.equal(convertArea(1_000_000, 'km2'), 1);
    assert.equal(formatFrenchMeasure(170052.07, 'm²'), '170 052,07 m²');
    assert.throws(() => convertDistance(-1, 'm'), RangeError);
    assert.throws(() => convertArea(1, 'acre'), RangeError);
});
