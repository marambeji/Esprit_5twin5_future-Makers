<?php

namespace Tests\Feature;

use App\Models\Ferme;
use App\Models\Producteur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgriculturalMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_verified_coordinates_with_a_farm(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $producer = Producteur::factory()->create();

        $this->actingAs($admin, 'admin')->post(route('admin.fermes.store'), [
            'producteur_id' => $producer->id,
            'nom' => 'Ferme Atlas',
            'localisation' => 'Béja, Tunisie',
            'latitude' => '36.7256400',
            'longitude' => '9.1816900',
            'superficie' => '12.50',
        ])->assertRedirect();

        $this->assertDatabaseHas('fermes', [
            'nom' => 'Ferme Atlas',
            'latitude' => '36.7256400',
            'longitude' => '9.1816900',
        ]);
    }

    public function test_invalid_or_incomplete_coordinates_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $producer = Producteur::factory()->create();
        $payload = ['producteur_id' => $producer->id, 'nom' => 'Ferme test', 'localisation' => 'Béja, Tunisie'];

        $this->actingAs($admin, 'admin')->from(route('admin.fermes.create'))
            ->post(route('admin.fermes.store'), $payload + ['latitude' => '91', 'longitude' => ''])
            ->assertRedirect(route('admin.fermes.create'))
            ->assertSessionHasErrors(['latitude', 'longitude']);
    }

    public function test_map_only_exposes_farms_with_valid_coordinates_and_requires_an_admin(): void
    {
        config(['services.openstreetmap.tiles_url' => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png']);
        $producer = Producteur::factory()->create(['nom' => 'Ben Salah', 'prenom' => 'Ahmed']);
        $located = Ferme::factory()->create(['producteur_id' => $producer->id, 'nom' => 'Ferme localisée', 'latitude' => 36.7, 'longitude' => 9.1]);
        $legacy = Ferme::factory()->create(['producteur_id' => $producer->id, 'nom' => 'Ferme sans coordonnées', 'latitude' => null, 'longitude' => null]);
        Ferme::factory()->create(['producteur_id' => $producer->id, 'nom' => 'Ferme hors limites', 'latitude' => 91, 'longitude' => 9.1]);

        $this->get(route('admin.agriculture.map'))->assertRedirect(route('admin.login'));
        $this->actingAs(User::factory()->create(['role' => 'user']), 'admin')
            ->get(route('admin.agriculture.map'))
            ->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'admin')->get(route('admin.agriculture.map'))
            ->assertOk()
            ->assertSee('Ferme localisée')
            ->assertDontSee('Ferme sans coordonnées')
            ->assertDontSee('Ferme hors limites')
            ->assertSee('leaflet@1.9.4', false)
            ->assertSee('OpenStreetMap</a> contributors', false)
            ->assertSee('Fermes correspondant aux filtres')
            ->assertSee('Ouvrir dans Google Maps')
            ->assertSee('Commencer le dessin')
            ->assertSee('rectangle')
            ->assertSee('polygonMetrics', false)
            ->assertSee('farmCount', false)
            ->assertSee('agricultural-map-controls.mjs', false)
            ->assertSee('data-measurement-map', false)
            ->assertSee('data-map-tools', false)
            ->assertDontSee('maps.googleapis.com', false)
            ->assertDontSee('GOOGLE_MAPS_API_KEY', false)
            ->assertViewHas('markers', fn ($markers) => $markers->count() === 1 && $markers->first()['nom'] === $located->nom)
            ->assertViewHas('matchingCount', 3)
            ->assertViewHas('locatedCount', 1)
            ->assertViewHas('withoutValidCoordinatesCount', 2);

        $this->assertDatabaseHas('fermes', ['id' => $legacy->id, 'latitude' => null, 'longitude' => null]);
    }

    public function test_forms_include_leaflet_without_an_api_key(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'admin')->get(route('admin.fermes.create'))
            ->assertOk()
            ->assertSee('leaflet@1.9.4', false)
            ->assertSee('OpenStreetMap</a> contributors', false)
            ->assertSee('agricultural-map-controls.mjs', false)
            ->assertSee('data-map-tools', false)
            ->assertSee('sync-boundary-area', false)
            ->assertSee('selectedArea / 10000', false)
            ->assertSee('Réactivez la synchronisation', false)
            ->assertSee('Les cercles sont enregistrés sous forme de polygones à 64 sommets')
            ->assertDontSee('GOOGLE_MAPS_API_KEY', false)
            ->assertDontSee('maps.googleapis.com', false);
    }

    public function test_farm_form_remains_admin_only(): void
    {
        $this->get(route('admin.fermes.create'))->assertRedirect(route('admin.login'));
        $this->actingAs(User::factory()->create(['role' => 'user']), 'admin')
            ->get(route('admin.fermes.create'))->assertForbidden();
    }

    public function test_existing_boundary_is_measured_on_edit_without_overwriting_saved_surface_by_default(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $farm = Ferme::factory()->create([
            'superficie' => 12.34,
            'boundary_geojson' => ['type' => 'Polygon', 'coordinates' => [[[9, 36], [9.01, 36], [9.01, 36.01], [9, 36.01], [9, 36]]]],
        ]);
        $this->actingAs($admin, 'admin')->get(route('admin.fermes.edit', $farm))->assertOk()
            ->assertSee('value="12.34"', false)
            ->assertSee('selectedArea = polygonMetrics', false)
            ->assertSee('id="sync-boundary-area"', false)
            ->assertSee('Mettre à jour automatiquement la superficie en hectares');
    }

    public function test_map_reuses_the_farm_listing_filters_for_its_markers(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $producer = Producteur::factory()->create();
        $included = Ferme::factory()->create(['producteur_id' => $producer->id, 'nom' => 'Ferme filtrée', 'superficie' => 25, 'latitude' => 36.7, 'longitude' => 9.1]);
        Ferme::factory()->create(['nom' => 'Ferme exclue', 'superficie' => 5, 'latitude' => 36.8, 'longitude' => 9.2]);

        $this->actingAs($admin, 'admin')->get(route('admin.agriculture.map', [
            'producteur_id' => $producer->id,
            'superficie_min' => 20,
        ]))->assertOk()
            ->assertSee('Ferme filtrée')
            ->assertDontSee('Ferme exclue')
            ->assertViewHas('markers', fn ($markers) => $markers->count() === 1 && $markers->first()['nom'] === $included->nom);
    }

    public function test_farm_can_be_created_without_coordinates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $producer = Producteur::factory()->create();

        $this->actingAs($admin, 'admin')->post(route('admin.fermes.store'), [
            'producteur_id' => $producer->id,
            'nom' => 'Ferme historique',
            'localisation' => 'Kairouan',
        ])->assertRedirect();

        $this->assertDatabaseHas('fermes', ['nom' => 'Ferme historique', 'latitude' => null, 'longitude' => null]);
    }

    public function test_farm_details_show_saved_information_map_and_safe_google_maps_link_only_for_valid_coordinates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $located = Ferme::factory()->create([
            'nom' => 'Ferme Atlas',
            'description' => 'Description de la ferme.',
            'latitude' => 36.7,
            'longitude' => 9.1,
        ]);

        $this->actingAs($admin, 'admin')->get(route('admin.fermes.show', $located))
            ->assertOk()
            ->assertSee('Description de la ferme.')
            ->assertSee('Emplacement enregistré')
            ->assertSee('farm-detail-map', false)
            ->assertSee('data-map-tools', false)
            ->assertSee('google.com/maps/search/', false)
            ->assertSee('36.7000000%2C9.1000000', false)
            ->assertSee('noopener noreferrer', false)
            ->assertSee('OpenStreetMap</a> contributors', false)
            ->assertSee('installFarmMapMeasurement', false)
            ->assertSee('agricultural-map-controls.mjs', false);

        $unlocated = Ferme::factory()->create(['latitude' => null, 'longitude' => null]);
        $this->get(route('admin.fermes.show', $unlocated))
            ->assertOk()
            ->assertSee('Aucune coordonnée GPS valide')
            ->assertDontSee('google.com/maps/search/', false)
            ->assertDontSee('farm-detail-map', false);

    }

    public function test_farm_boundary_is_validated_measured_saved_and_shown(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $producer = Producteur::factory()->create();
        $geojson = json_encode(['type' => 'Polygon', 'coordinates' => [[[9, 36], [9.01, 36], [9.01, 36.01], [9, 36.01], [9, 36]]]]);

        $this->actingAs($admin, 'admin')->post(route('admin.fermes.store'), [
            'producteur_id' => $producer->id, 'nom' => 'Ferme délimitée', 'localisation' => 'Béja',
            'boundary_geojson' => $geojson, 'boundary_area_m2' => 1, 'boundary_perimeter_m' => 1,
        ])->assertRedirect();

        $farm = Ferme::where('nom', 'Ferme délimitée')->firstOrFail();
        $this->assertSame('Polygon', $farm->boundary_geojson['type']);
        $this->assertGreaterThan(900000, $farm->boundary_area_m2);
        $this->assertLessThan(1010000, $farm->boundary_area_m2);
        $this->assertGreaterThan(4000, $farm->boundary_perimeter_m);
        $this->assertNotEquals(1, $farm->boundary_area_m2);

        $this->get(route('admin.fermes.show', $farm))->assertOk()
            ->assertSee('Surface de la limite')->assertSee('Périmètre de la limite')
            ->assertSee('farm-detail-map', false)
            ->assertSee('type":"Polygon', false);
    }

    public function test_farm_boundary_can_be_updated_or_explicitly_removed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $farm = Ferme::factory()->create();
        $geojson = json_encode(['type' => 'Polygon', 'coordinates' => [[[9, 36], [9.02, 36], [9.02, 36.02], [9, 36.02], [9, 36]]]]);

        $payload = ['producteur_id' => $farm->producteur_id, 'nom' => $farm->nom, 'localisation' => $farm->localisation, 'boundary_geojson' => $geojson];
        $this->actingAs($admin, 'admin')->put(route('admin.fermes.update', $farm), $payload)->assertRedirect();
        $farm->refresh();
        $this->assertNotNull($farm->boundary_geojson);
        $this->assertGreaterThan(0, $farm->boundary_area_m2);

        $payload['boundary_geojson'] = '';
        $this->put(route('admin.fermes.update', $farm), $payload)->assertRedirect();
        $this->assertNotNull($farm->fresh()->boundary_geojson);
        $payload['remove_boundary'] = '1';
        $this->put(route('admin.fermes.update', $farm), $payload)->assertRedirect();
        $this->assertNull($farm->fresh()->boundary_geojson);
        $this->assertNull($farm->fresh()->boundary_area_m2);
        $this->assertNull($farm->fresh()->boundary_perimeter_m);
    }

    public function test_invalid_farm_boundaries_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $producer = Producteur::factory()->create();
        $valid = ['producteur_id' => $producer->id, 'nom' => 'Ferme test', 'localisation' => 'Béja'];
        $invalid = [
            json_encode(['type' => 'Point', 'coordinates' => [9, 36]]),
            json_encode(['type' => 'Polygon', 'coordinates' => [[[9, 36], [9.01, 36], [9, 36]]]]),
            json_encode(['type' => 'Polygon', 'coordinates' => [[[181, 36], [9.01, 36], [9, 36.01], [181, 36]]]]),
            str_repeat('x', 65537),
        ];
        foreach ($invalid as $index => $boundary) {
            $this->actingAs($admin, 'admin')->from(route('admin.fermes.create'))
                ->post(route('admin.fermes.store'), $valid + ['nom' => 'Ferme invalide '.$index, 'boundary_geojson' => $boundary])
                ->assertRedirect(route('admin.fermes.create'))->assertSessionHasErrors('boundary_geojson');
        }
    }
}
