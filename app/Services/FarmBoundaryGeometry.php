<?php

namespace App\Services;

use InvalidArgumentException;

class FarmBoundaryGeometry
{
    private const EARTH_RADIUS_METERS = 6371008.8;

    /** @return array{geojson: array{type: string, coordinates: array}, area_m2: float, perimeter_m: float} */
    public function measure(string $json): array
    {
        if (strlen($json) > 65536) {
            throw new InvalidArgumentException('La limite dépasse la taille maximale autorisée (64 Ko).');
        }

        try {
            $geometry = json_decode($json, true, 32, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new InvalidArgumentException('La limite doit être un GeoJSON valide.');
        }

        if (! is_array($geometry) || ($geometry['type'] ?? null) !== 'Polygon'
            || ! is_array($geometry['coordinates'] ?? null) || count($geometry['coordinates']) !== 1
            || ! is_array($geometry['coordinates'][0])) {
            throw new InvalidArgumentException('La limite doit être un polygone GeoJSON avec un seul contour.');
        }

        $ring = $geometry['coordinates'][0];
        if (count($ring) < 4 || count($ring) > 501) {
            throw new InvalidArgumentException('Le contour doit contenir de 3 à 500 sommets distincts et être fermé.');
        }

        $points = [];
        foreach ($ring as $coordinate) {
            if (! is_array($coordinate) || count($coordinate) !== 2
                || ! is_numeric($coordinate[0]) || is_bool($coordinate[0])
                || ! is_numeric($coordinate[1]) || is_bool($coordinate[1])) {
                throw new InvalidArgumentException('Chaque coordonnée doit contenir une longitude et une latitude numériques.');
            }
            $longitude = (float) $coordinate[0];
            $latitude = (float) $coordinate[1];
            if (! is_finite($longitude) || ! is_finite($latitude) || $longitude < -180 || $longitude > 180 || $latitude < -90 || $latitude > 90) {
                throw new InvalidArgumentException('Les coordonnées de la limite sont hors des plages autorisées.');
            }
            $points[] = ['lat' => $latitude, 'lng' => $longitude];
        }

        $first = $points[0];
        $last = $points[array_key_last($points)];
        if ($first !== $last) {
            throw new InvalidArgumentException('Le contour GeoJSON doit être fermé.');
        }
        array_pop($points);
        if (count($points) < 3 || count($points) > 500 || count(array_unique(array_map(
            fn (array $point) => sprintf('%.12F,%.12F', $point['lat'], $point['lng']), $points
        ))) !== count($points)) {
            throw new InvalidArgumentException('Le contour doit contenir de 3 à 500 sommets distincts.');
        }
        if (! $this->isSimple($points)) {
            throw new InvalidArgumentException('Le contour est invalide ou se croise; vérifiez la forme dessinée.');
        }

        $signedArea = 0.0;
        foreach ($points as $index => $point) {
            $next = $points[($index + 1) % count($points)];
            $delta = deg2rad($next['lng'] - $point['lng']);
            if ($delta > M_PI) {
                $delta -= 2 * M_PI;
            }
            if ($delta < -M_PI) {
                $delta += 2 * M_PI;
            }
            $signedArea += $delta * (2 + sin(deg2rad($point['lat'])) + sin(deg2rad($next['lat'])));
        }
        $area = abs($signedArea * self::EARTH_RADIUS_METERS ** 2 / 2);
        $sphereArea = 4 * M_PI * self::EARTH_RADIUS_METERS ** 2;
        $area = min($area, max(0, $sphereArea - $area));
        $perimeter = 0.0;
        foreach ($points as $index => $point) {
            $perimeter += $this->distance($point, $points[($index + 1) % count($points)]);
        }
        if (! is_finite($area) || $area <= 0 || ! is_finite($perimeter) || $perimeter <= 0) {
            throw new InvalidArgumentException('Le contour ne permet pas de calculer une surface valide.');
        }

        return ['geojson' => ['type' => 'Polygon', 'coordinates' => [array_map(
            fn (array $point) => [$point['lng'], $point['lat']], [...$points, $points[0]]
        )]], 'area_m2' => $area, 'perimeter_m' => $perimeter];
    }

    private function distance(array $a, array $b): float
    {
        $lat1 = deg2rad($a['lat']);
        $lat2 = deg2rad($b['lat']);
        $deltaLat = $lat2 - $lat1;
        $deltaLng = deg2rad($b['lng'] - $a['lng']);
        if ($deltaLng > M_PI) {
            $deltaLng -= 2 * M_PI;
        }
        if ($deltaLng < -M_PI) {
            $deltaLng += 2 * M_PI;
        }
        $h = sin($deltaLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($deltaLng / 2) ** 2;

        return 2 * self::EARTH_RADIUS_METERS * asin(min(1, sqrt($h)));
    }

    private function isSimple(array $points): bool
    {
        $longitudes = array_column($points, 'lng');
        if (max($longitudes) - min($longitudes) > 180) {
            return false;
        }
        $meanLat = deg2rad(array_sum(array_column($points, 'lat')) / count($points));
        $projected = array_map(fn ($point) => [deg2rad($point['lng'] - $points[0]['lng']) * cos($meanLat), deg2rad($point['lat'])], $points);
        $orientation = fn ($a, $b, $c) => ($b[0] - $a[0]) * ($c[1] - $a[1]) - ($b[1] - $a[1]) * ($c[0] - $a[0]);
        $onSegment = fn ($a, $b, $c) => $b[0] >= min($a[0], $c[0]) - 1e-12
            && $b[0] <= max($a[0], $c[0]) + 1e-12
            && $b[1] >= min($a[1], $c[1]) - 1e-12
            && $b[1] <= max($a[1], $c[1]) + 1e-12;
        $n = count($projected);
        for ($i = 0; $i < $n; $i++) {
            $a = $projected[$i];
            $b = $projected[($i + 1) % $n];
            for ($j = $i + 1; $j < $n; $j++) {
                $c = $projected[$j];
                $d = $projected[($j + 1) % $n];
                if ($i === $j || ($i + 1) % $n === $j || ($j + 1) % $n === $i) {
                    continue;
                }
                $o1 = $orientation($a, $b, $c);
                $o2 = $orientation($a, $b, $d);
                $o3 = $orientation($c, $d, $a);
                $o4 = $orientation($c, $d, $b);
                $properIntersection = ($o1 > 1e-12 && $o2 < -1e-12 || $o1 < -1e-12 && $o2 > 1e-12)
                    && ($o3 > 1e-12 && $o4 < -1e-12 || $o3 < -1e-12 && $o4 > 1e-12);
                $touchingIntersection = (abs($o1) <= 1e-12 && $onSegment($a, $c, $b))
                    || (abs($o2) <= 1e-12 && $onSegment($a, $d, $b))
                    || (abs($o3) <= 1e-12 && $onSegment($c, $a, $d))
                    || (abs($o4) <= 1e-12 && $onSegment($c, $b, $d));
                if ($properIntersection || $touchingIntersection) {
                    return false;
                }
            }
        }

        return true;
    }
}
