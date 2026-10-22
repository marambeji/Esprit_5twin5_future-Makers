<?php

namespace Tests\Feature;

use App\Models\Ferme;
use App\Models\Producteur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FermeFrontofficeTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_producer_can_create_a_farm_and_crafted_boundary_fields_are_ignored(): void
    {
        [$user, $producer] = $this->producerAccount();
        $foreignProducer = Producteur::factory()->create();
        $boundary = $this->squareBoundary();

        $response = $this->actingAs($user)->post(route('fermes.store'), [
            'producteur_id' => $foreignProducer->id,
            'nom' => 'Ferme publique',
            'localisation' => 'Béja',
            'boundary_geojson' => $boundary,
            'boundary_area_m2' => 1,
            'boundary_perimeter_m' => 1,
        ]);

        $farm = Ferme::where('nom', 'Ferme publique')->firstOrFail();
        $response->assertRedirect(route('fermes.show', $farm));
        self::assertSame($producer->id, $farm->producteur_id);
        self::assertNull($farm->boundary_geojson);
        self::assertNull($farm->boundary_area_m2);
        self::assertNull($farm->boundary_perimeter_m);
    }

    public function test_producer_cannot_manage_another_producers_farm(): void
    {
        [$owner] = $this->producerAccount();
        [, $otherProducer] = $this->producerAccount('other-producer@example.test');
        $farm = Ferme::factory()->create(['producteur_id' => $otherProducer->id]);

        $this->actingAs($owner)->get(route('fermes.edit', $farm))->assertNotFound();
        $this->put(route('fermes.update', $farm), [
            'producteur_id' => $otherProducer->id,
            'nom' => 'Hijack', 'localisation' => 'Béja',
        ])->assertNotFound();
        $this->delete(route('fermes.destroy', $farm))->assertNotFound();
        $this->assertDatabaseHas('fermes', ['id' => $farm->id, 'nom' => $farm->nom]);
    }

    public function test_frontoffice_farm_validation_ignores_all_boundary_fields(): void
    {
        [$user, $producer] = $this->producerAccount();
        $base = ['producteur_id' => $producer->id, 'nom' => 'Farm without boundary', 'localisation' => 'Sfax'];
        $this->actingAs($user)->post(route('fermes.store'), $base)->assertRedirect();
        $this->assertDatabaseHas('fermes', ['nom' => 'Farm without boundary', 'boundary_geojson' => null]);

        $this->post(route('fermes.store'), array_replace($base, [
            'nom' => 'Farm with crafted boundary',
            'boundary_geojson' => json_encode(['type' => 'Polygon', 'coordinates' => [[[1, 1], [2, 2], [3, 3], [1, 1]]]]),
            'boundary_area_m2' => 999,
            'boundary_perimeter_m' => 999,
            'remove_boundary' => '1',
        ]))->assertRedirect();
        $this->assertDatabaseHas('fermes', ['nom' => 'Farm with crafted boundary', 'boundary_geojson' => null]);
    }

    public function test_frontoffice_guests_are_redirected_and_other_users_cannot_open_the_create_form(): void
    {
        $this->get(route('fermes.index'))->assertRedirect(route('login'));
        $this->get(route('fermes.show', 1))->assertRedirect(route('login'));
        $this->get(route('fermes.create'))->assertRedirect(route('login'));
        $unlinked = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
        $this->actingAs($unlinked)->get(route('fermes.create'))->assertForbidden();
        $unverified = User::factory()->create(['role' => 'user', 'email_verified_at' => null]);
        Producteur::factory()->create(['email' => $unverified->email]);
        $this->actingAs($unverified)->get(route('fermes.create'))->assertForbidden();
    }

    public function test_producer_forms_render_a_plain_map_and_ai_controls_without_boundary_ui(): void
    {
        [$user, $producer] = $this->producerAccount();
        $this->actingAs($user)->get(route('fermes.create'))->assertOk()
            ->assertSee('front-farm-map', false)
            ->assertSee('agricultural-map-controls.mjs', false)
            ->assertSee('Générer automatiquement')
            ->assertSee('data-ai-generate', false)
            ->assertSee('data-ai-preview', false)
            ->assertDontSee('Assistant IA')
            ->assertDontSee('Régénérer')
            ->assertSee('tools:false', false)
            ->assertDontSee('boundary_geojson', false)
            ->assertDontSee('Utiliser cette surface');

        $farm = Ferme::factory()->create(['producteur_id' => $producer->id]);
        $this->get(route('fermes.edit', $farm))->assertOk()
            ->assertSee('agricultural-map-controls.mjs', false)
            ->assertDontSee('boundary_geojson', false)
            ->assertDontSee('type":"Polygon', false);
    }

    public function test_valid_old_gps_coordinates_are_restored_on_the_form_map_after_validation_failure(): void
    {
        [$user] = $this->producerAccount();
        $this->actingAs($user)->from(route('fermes.create'))->post(route('fermes.store'), [
            'nom' => '', 'localisation' => 'Sfax', 'latitude' => '36.1234567', 'longitude' => '9.1234567',
        ])->assertRedirect(route('fermes.create'))->assertSessionHasErrors('nom');

        $this->get(route('fermes.create'))->assertOk()
            ->assertSee('36.1234567')
            ->assertSee('9.1234567')
            ->assertSee('Number(36.1234567), Number(9.1234567)', false);
    }

    public function test_frontoffice_update_cannot_change_an_existing_admin_boundary(): void
    {
        [$user, $producer] = $this->producerAccount();
        $boundary = json_decode($this->squareBoundary(), true);
        $farm = Ferme::factory()->create([
            'producteur_id' => $producer->id,
            'boundary_geojson' => $boundary,
            'boundary_area_m2' => 12345.67,
            'boundary_perimeter_m' => 456.78,
        ]);
        $payload = [
            'producteur_id' => $producer->id,
            'nom' => $farm->nom,
            'localisation' => $farm->localisation,
            'boundary_geojson' => json_encode(['type' => 'Point', 'coordinates' => [0, 0]]),
            'boundary_area_m2' => 1,
            'boundary_perimeter_m' => 1,
            'remove_boundary' => '1',
        ];

        $this->actingAs($user)->put(route('fermes.update', $farm), $payload)->assertRedirect();
        self::assertSame($boundary, $farm->fresh()->boundary_geojson);
        self::assertSame(12345.67, $farm->fresh()->boundary_area_m2);
        self::assertSame(456.78, $farm->fresh()->boundary_perimeter_m);
    }

    public function test_frontoffice_farm_list_maps_only_valid_gps_markers_and_links_to_details(): void
    {
        [$user, $producer] = $this->producerAccount();
        $located = Ferme::factory()->create([
            'producteur_id' => $producer->id, 'nom' => 'Ferme visible', 'latitude' => 36.7, 'longitude' => 9.1,
        ]);
        Ferme::factory()->create(['producteur_id' => $producer->id, 'nom' => 'Ferme sans GPS', 'latitude' => null, 'longitude' => null]);

        $this->actingAs($user)->get(route('fermes.index'))->assertOk()
            ->assertSee('Carte des fermes géolocalisées')
            ->assertSee('Ferme visible')
            ->assertSee(route('fermes.show', $located), false)
            ->assertSee('Ajouter une ferme')
            ->assertViewHas('markers', fn ($markers) => $markers->count() === 1 && $markers->first()->id === $located->id);
    }

    public function test_frontoffice_details_do_not_render_an_admin_saved_boundary(): void
    {
        [$user, $producer] = $this->producerAccount();
        $boundary = json_decode($this->squareBoundary(), true);
        $farm = Ferme::factory()->create([
            'producteur_id' => $producer->id,
            'latitude' => 36.7,
            'longitude' => 9.1,
            'boundary_geojson' => $boundary,
            'boundary_area_m2' => 12345,
            'boundary_perimeter_m' => 456,
        ]);

        $this->actingAs($user)->get(route('fermes.show', $farm))->assertOk()
            ->assertSee('front-farm-map', false)
            ->assertSee('Ouvrir dans Google Maps')
            ->assertDontSee('type":"Polygon', false)
            ->assertDontSee('Périmètre de la limite');
    }

    public function test_frontoffice_farm_detail_displays_saved_area_in_hectares_and_shared_quality_score(): void
    {
        [$user, $producer] = $this->producerAccount();
        $farm = Ferme::factory()->create([
            'producteur_id' => $producer->id, 'description' => 'Ferme familiale.',
            'localisation' => 'Sfax', 'superficie' => 12.5,
        ]);
        $this->actingAs($user)->get(route('fermes.show', $farm))->assertOk()
            ->assertSee('Superficie :')->assertSee('12,50 ha')
            ->assertSee('Qualité de la fiche')->assertSee('100 % complétée');
    }

    public function test_frontoffice_detail_explains_missing_area_without_hiding_it(): void
    {
        [$user, $producer] = $this->producerAccount();
        $farm = Ferme::factory()->create(['producteur_id' => $producer->id, 'superficie' => null]);
        $this->actingAs($user)->get(route('fermes.show', $farm))->assertOk()
            ->assertSee('Superficie :')->assertSee('Non renseignée')
            ->assertSee('À compléter')->assertSee('Superficie');
    }

    public function test_frontoffice_description_generation_uses_fake_gemini(): void
    {
        [$user] = $this->producerAccount();
        config(['services.gemini.api_key' => 'fake-key', 'services.gemini.model' => 'gemini-test-model']);
        Http::fake(['*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Description suggérée.']]]]],
        ], 200)]);

        $this->actingAs($user)->postJson(route('fermes.generate-description'), [
            'nom' => 'Ferme Atlas', 'localisation' => 'Béja', 'superficie' => 12,
        ])->assertOk()->assertJsonPath('description', 'Description suggérée.');
        Http::assertSent(fn ($request) => str_contains($request->url(), 'gemini-test-model:generateContent')
            && $request->hasHeader('x-goog-api-key', 'fake-key'));
    }

    public function test_frontoffice_description_generation_returns_fake_provider_errors(): void
    {
        [$user] = $this->producerAccount();
        config(['services.gemini.api_key' => 'fake-key', 'services.gemini.model' => 'gemini-test-model']);
        Http::fake(['*' => Http::response(['error' => ['status' => 'UNAVAILABLE']], 503)]);
        $this->actingAs($user)->postJson(route('fermes.generate-description'), [
            'nom' => 'Ferme Atlas', 'localisation' => 'Béja',
        ])->assertStatus(503)->assertJsonPath('error', 'ai_upstream_unavailable');
    }

    private function producerAccount(string $email = 'producer@example.test'): array
    {
        $user = User::factory()->create([
            'role' => 'user',
            'email' => $email,
            'email_verified_at' => now(),
        ]);
        $producer = Producteur::factory()->create(['email' => $email]);

        return [$user, $producer];
    }

    private function squareBoundary(): string
    {
        return json_encode(['type' => 'Polygon', 'coordinates' => [[[9, 36], [9.01, 36], [9.01, 36.01], [9, 36.01], [9, 36]]]]);
    }
}
