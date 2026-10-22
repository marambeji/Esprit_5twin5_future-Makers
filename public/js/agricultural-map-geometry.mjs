export const EARTH_RADIUS_METERS = 6371008.8;

const radians = (value) => value * Math.PI / 180;

const wrappedLongitudeDelta = (from, to) => {
    let delta = radians(to - from);
    if (delta > Math.PI) delta -= 2 * Math.PI;
    if (delta < -Math.PI) delta += 2 * Math.PI;
    return delta;
};

export function distanceMeters(first, second) {
    const lat1 = radians(first.lat);
    const lat2 = radians(second.lat);
    const deltaLat = lat2 - lat1;
    const deltaLng = wrappedLongitudeDelta(first.lng, second.lng);
    const haversine = Math.sin(deltaLat / 2) ** 2
        + Math.cos(lat1) * Math.cos(lat2) * Math.sin(deltaLng / 2) ** 2;

    return 2 * EARTH_RADIUS_METERS * Math.asin(Math.min(1, Math.sqrt(haversine)));
}

export function midpointOnSphere(first, second) {
    const lat1 = radians(first.lat), lon1 = radians(first.lng);
    const lat2 = radians(second.lat), lon2 = radians(second.lng);
    const x = Math.cos(lat1) * Math.cos(lon1) + Math.cos(lat2) * Math.cos(lon2);
    const y = Math.cos(lat1) * Math.sin(lon1) + Math.cos(lat2) * Math.sin(lon2);
    const z = Math.sin(lat1) + Math.sin(lat2);
    const length = Math.hypot(x, y, z);
    if (!Number.isFinite(length) || length < 1e-12) throw new RangeError('Antipodal points have no unique midpoint.');

    return {lat: Math.atan2(z, Math.hypot(x, y)) * 180 / Math.PI, lng: Math.atan2(y, x) * 180 / Math.PI};
}

export function polylineMetrics(points) {
    if (!Array.isArray(points) || points.length < 2 || points.some((point) => (
        !Number.isFinite(point.lat) || !Number.isFinite(point.lng)
        || point.lat < -90 || point.lat > 90 || point.lng < -180 || point.lng > 180
    ))) throw new RangeError('A measured line needs at least two valid coordinates.');

    const segments = points.slice(1).map((point, index) => distanceMeters(points[index], point));
    return {segments, totalMeters: segments.reduce((sum, distance) => sum + distance, 0)};
}

export function convertDistance(meters, unit = 'm') {
    if (!Number.isFinite(meters) || meters < 0) throw new RangeError('Distance must be non-negative.');
    if (unit === 'm') return meters;
    if (unit === 'km') return meters / 1000;
    throw new RangeError('Unsupported distance unit.');
}

export function convertArea(squareMeters, unit = 'm2') {
    if (!Number.isFinite(squareMeters) || squareMeters < 0) throw new RangeError('Area must be non-negative.');
    if (unit === 'm2') return squareMeters;
    if (unit === 'ha') return squareMeters / 10000;
    if (unit === 'km2') return squareMeters / 1_000_000;
    throw new RangeError('Unsupported area unit.');
}

export function formatFrenchMeasure(value, unit, maximumFractionDigits = 2) {
    if (!Number.isFinite(value) || value < 0) throw new RangeError('A measure must be non-negative.');
    return `${value.toLocaleString('fr-FR', {maximumFractionDigits})} ${unit}`;
}

export function polygonMetrics(points) {
    if (!isSimplePolygon(points)) {
        throw new RangeError('Polygon must be simple, non-degenerate, and within one longitude span.');
    }

    const signedArea = points.reduce((sum, point, index) => {
        const next = points[(index + 1) % points.length];
        return sum + wrappedLongitudeDelta(point.lng, next.lng)
            * (2 + Math.sin(radians(point.lat)) + Math.sin(radians(next.lat)));
    }, 0) * EARTH_RADIUS_METERS ** 2 / 2;
    const sphereArea = 4 * Math.PI * EARTH_RADIUS_METERS ** 2;
    const rawArea = Math.abs(signedArea);
    const areaSquareMeters = Math.min(rawArea, Math.max(0, sphereArea - rawArea));
    const perimeterMeters = points.reduce((sum, point, index) => (
        sum + distanceMeters(point, points[(index + 1) % points.length])
    ), 0);

    if (!Number.isFinite(areaSquareMeters) || areaSquareMeters <= 0 || !Number.isFinite(perimeterMeters) || perimeterMeters <= 0) {
        throw new RangeError('Polygon has no measurable area or perimeter.');
    }

    return {areaSquareMeters, areaHectares: areaSquareMeters / 10000, perimeterMeters};
}

export function circleMetrics(radiusMeters) {
    if (!Number.isFinite(radiusMeters) || radiusMeters <= 0 || radiusMeters > Math.PI * EARTH_RADIUS_METERS) {
        throw new RangeError('Circle radius must be positive and no greater than half the globe.');
    }

    const angularRadius = radiusMeters / EARTH_RADIUS_METERS;

    return {
        radiusMeters,
        areaSquareMeters: 2 * Math.PI * EARTH_RADIUS_METERS ** 2 * (1 - Math.cos(angularRadius)),
        areaHectares: 2 * Math.PI * EARTH_RADIUS_METERS ** 2 * (1 - Math.cos(angularRadius)) / 10000,
        circumferenceMeters: 2 * Math.PI * EARTH_RADIUS_METERS * Math.sin(angularRadius),
    };
}

export function circleToPolygon(center, radiusMeters, vertexCount = 64) {
    if (!Number.isInteger(vertexCount) || vertexCount < 3 || vertexCount > 500
        || !Number.isFinite(center?.lat) || !Number.isFinite(center?.lng)
        || center.lat < -90 || center.lat > 90 || center.lng < -180 || center.lng > 180
        || !Number.isFinite(radiusMeters) || radiusMeters <= 0 || radiusMeters > Math.PI * EARTH_RADIUS_METERS) {
        throw new RangeError('Circle cannot be converted to a polygon.');
    }
    const angularDistance = radiusMeters / EARTH_RADIUS_METERS;
    const latitude = radians(center.lat), longitude = radians(center.lng);
    return Array.from({length: vertexCount}, (_, index) => {
        const bearing = 2 * Math.PI * index / vertexCount;
        const targetLatitude = Math.asin(Math.sin(latitude) * Math.cos(angularDistance)
            + Math.cos(latitude) * Math.sin(angularDistance) * Math.cos(bearing));
        const targetLongitude = longitude + Math.atan2(Math.sin(bearing) * Math.sin(angularDistance) * Math.cos(latitude),
            Math.cos(angularDistance) - Math.sin(latitude) * Math.sin(targetLatitude));
        let lng = targetLongitude * 180 / Math.PI;
        lng = ((lng + 540) % 360) - 180;
        return {lat: targetLatitude * 180 / Math.PI, lng};
    });
}

export function isSimplePolygon(points) {
    if (!Array.isArray(points) || points.length < 3 || points.some((point) => (
        !Number.isFinite(point.lat) || !Number.isFinite(point.lng)
        || point.lat < -90 || point.lat > 90 || point.lng < -180 || point.lng > 180
    ))) return false;

    const longitudes = points.map((point) => point.lng);
    if (Math.max(...longitudes) - Math.min(...longitudes) > 180) return false;
    const unique = new Set(points.map((point) => `${point.lat.toFixed(7)},${point.lng.toFixed(7)}`));
    if (unique.size < 3) return false;

    const meanLatitude = radians(points.reduce((sum, point) => sum + point.lat, 0) / points.length);
    const projected = points.map((point) => [
        wrappedLongitudeDelta(points[0].lng, point.lng) * Math.cos(meanLatitude),
        radians(point.lat),
    ]);
    const orientation = (a, b, c) => (b[0] - a[0]) * (c[1] - a[1]) - (b[1] - a[1]) * (c[0] - a[0]);
    const onSegment = (a, b, c) => b[0] >= Math.min(a[0], c[0]) - 1e-12
        && b[0] <= Math.max(a[0], c[0]) + 1e-12
        && b[1] >= Math.min(a[1], c[1]) - 1e-12
        && b[1] <= Math.max(a[1], c[1]) + 1e-12;
    const intersects = (a, b, c, d) => {
        const abC = orientation(a, b, c);
        const abD = orientation(a, b, d);
        const cdA = orientation(c, d, a);
        const cdB = orientation(c, d, b);
        const opposite = (first, second) => (first > 1e-12 && second < -1e-12) || (first < -1e-12 && second > 1e-12);

        if (opposite(abC, abD) && opposite(cdA, cdB)) return true;

        return (Math.abs(abC) <= 1e-12 && onSegment(a, c, b))
            || (Math.abs(abD) <= 1e-12 && onSegment(a, d, b))
            || (Math.abs(cdA) <= 1e-12 && onSegment(c, a, d))
            || (Math.abs(cdB) <= 1e-12 && onSegment(c, b, d));
    };

    for (let first = 0; first < projected.length; first++) {
        const firstNext = (first + 1) % projected.length;
        for (let second = first + 1; second < projected.length; second++) {
            const secondNext = (second + 1) % projected.length;
            if (first === second || firstNext === second || secondNext === first) continue;
            if (intersects(projected[first], projected[firstNext], projected[second], projected[secondNext])) return false;
        }
    }

    const signedArea = points.reduce((sum, point, index) => {
        const next = points[(index + 1) % points.length];
        return sum + wrappedLongitudeDelta(point.lng, next.lng)
            * (2 + Math.sin(radians(point.lat)) + Math.sin(radians(next.lat)));
    }, 0) * EARTH_RADIUS_METERS ** 2 / 2;

    return Number.isFinite(signedArea) && Math.abs(signedArea) > 0
        && points.some((point, index) => distanceMeters(point, points[(index + 1) % points.length]) > 0);
}

export function pointInPolygon(point, polygon) {
    if (!isSimplePolygon(polygon) || !Number.isFinite(point.lat) || !Number.isFinite(point.lng)) return false;

    const referenceLongitude = radians(point.lng);
    const project = (coordinate) => {
        let longitude = radians(coordinate.lng) - referenceLongitude;
        if (longitude > Math.PI) longitude -= 2 * Math.PI;
        if (longitude < -Math.PI) longitude += 2 * Math.PI;
        return [longitude * Math.cos(radians(point.lat)), radians(coordinate.lat) - radians(point.lat)];
    };
    let inside = false;

    for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
        const a = project(polygon[i]);
        const b = project(polygon[j]);
        if ((a[1] > 0) !== (b[1] > 0) && 0 < ((b[0] - a[0]) * -a[1]) / (b[1] - a[1]) + a[0]) inside = !inside;
    }

    return inside;
}

export function pointInShape(point, shape) {
    if (shape.type === 'circle') return distanceMeters(shape.center, point) <= shape.radiusMeters;
    if (shape.type === 'rectangle') {
        return point.lat >= shape.south && point.lat <= shape.north
            && point.lng >= shape.west && point.lng <= shape.east;
    }
    if (shape.type === 'polygon') return pointInPolygon(point, shape.points);

    return false;
}

export function countPointsInShape(points, shape) {
    return points.reduce((count, point) => count + Number(pointInShape(point, shape)), 0);
}
