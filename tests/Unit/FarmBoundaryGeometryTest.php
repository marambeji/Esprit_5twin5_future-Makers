<?php

namespace Tests\Unit;

use App\Services\FarmBoundaryGeometry;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FarmBoundaryGeometryTest extends TestCase
{
    public function test_measures_a_geojson_polygon_geodesically(): void
    {
        $result = (new FarmBoundaryGeometry)->measure(json_encode([
            'type' => 'Polygon',
            'coordinates' => [[[9, 36], [9.01, 36], [9.01, 36.01], [9, 36.01], [9, 36]]],
        ]));

        self::assertEqualsWithDelta(998000, $result['area_m2'], 15000);
        self::assertEqualsWithDelta(4000, $result['perimeter_m'], 100);
        self::assertSame($result['geojson']['coordinates'][0][0], $result['geojson']['coordinates'][0][4]);
    }

    public function test_rejects_self_intersecting_polygon(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new FarmBoundaryGeometry)->measure(json_encode([
            'type' => 'Polygon', 'coordinates' => [[[9, 36], [9.01, 36.01], [9, 36.01], [9.01, 36], [9, 36]]],
        ]));
    }
}
