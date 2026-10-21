<?php

namespace Tests\Feature;

use App\Models\Ferme;
use App\Models\Product;
use App\Models\Transformateur;
use App\Models\Transformation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TransformationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['nutrition_ai.key' => null]); // par défaut : pas d'IA, estimation locale
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function transformateurData(array $o = []): array
    {
        return $o + ['nom' => 'Laiterie du Cap Bon', 'type_activite' => 'laiterie', 'email' => 'Contact@Laiterie.test', 'telephone' => '+216 72 111 222', 'ville' => 'Nabeul', 'adresse' => 'Zone industrielle', 'description' => 'Pasteurisation du lait.'];
    }

    public function test_admin_area_requires_admin(): void
    {
        foreach (['/admin/transformateurs', '/admin/transformations', '/admin/transformations/create'] as $url) {
            $this->get($url)->assertRedirect();
        }
        $this->post('/admin/transformations/estimer')->assertRedirect();
    }

    public function test_transformateur_crud_with_validation(): void
    {
        $this->actingAs($this->admin(), 'admin');

        $this->post('/admin/transformateurs', [])->assertSessionHasErrors(['nom', 'type_activite', 'email', 'telephone', 'ville', 'adresse']);
        $this->post('/admin/transformateurs', $this->transformateurData(['type_activite' => 'pirate', 'telephone' => 'abc', 'email' => 'pas-un-mail']))
            ->assertSessionHasErrors(['type_activite', 'telephone', 'email']);

        $this->post('/admin/transformateurs', $this->transformateurData())->assertRedirect();
        $tr = Transformateur::firstOrFail();
        $this->assertSame('contact@laiterie.test', $tr->email); // normalisé en minuscules

        $this->post('/admin/transformateurs', $this->transformateurData(['email' => 'contact@laiterie.test']))->assertSessionHasErrors('email'); // unicité
        $this->put("/admin/transformateurs/{$tr->id}", $this->transformateurData(['ville' => 'Tunis']))->assertSessionHasNoErrors(); // son propre e-mail reste accepté
        $this->assertSame('Tunis', $tr->fresh()->ville);

        $this->get('/admin/transformateurs?q=Cap')->assertOk()->assertSee('Laiterie du Cap Bon');
        $this->get('/admin/transformateurs?type_activite=huilerie')->assertDontSee('Laiterie du Cap Bon');
        $this->get("/admin/transformateurs/{$tr->id}")->assertOk()->assertSee('Laiterie / fromagerie');
        $this->get("/admin/transformateurs/{$tr->id}/edit")->assertOk();

        Transformation::factory()->create(['transformateur_id' => $tr->id]);
        $this->delete("/admin/transformateurs/{$tr->id}")->assertRedirect();
        $this->assertDatabaseCount('transformateurs', 0);
        $this->assertDatabaseCount('transformations', 0); // suppression en cascade
        $this->assertDatabaseCount('products', 1); // le produit est conservé
    }

    public function test_transformation_crud_join_and_eco_score(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $tr = Transformateur::factory()->create(['nom' => 'Huilerie Zitouna']);
        $product = Product::factory()->create(['name' => 'Huile d’olive extra']);
        $ferme = Ferme::factory()->create(['nom' => 'Ferme Oliviers']);

        $this->get('/admin/transformations/create')->assertOk()->assertSee('Estimer avec l’IA');
        $this->post('/admin/transformations', [])->assertSessionHasErrors(['transformateur_id', 'product_id', 'procede', 'date_transformation', 'quantite_kg']);
        $this->post('/admin/transformations', ['transformateur_id' => 999, 'product_id' => $product->id, 'procede' => 'magie', 'date_transformation' => now()->addDay()->toDateString(), 'quantite_kg' => 0, 'co2_kg' => -1])
            ->assertSessionHasErrors(['transformateur_id', 'procede', 'date_transformation', 'quantite_kg', 'co2_kg']);

        $data = ['transformateur_id' => $tr->id, 'product_id' => $product->id, 'ferme_id' => $ferme->id, 'procede' => 'pressage', 'date_transformation' => now()->toDateString(), 'quantite_kg' => 1000, 'co2_kg' => 400, 'eau_litres' => 1500, 'energie_kwh' => 350, 'eco_score' => 'A'];
        $this->post('/admin/transformations', $data)->assertRedirect();
        $t = Transformation::firstOrFail();
        $this->assertSame('B', $t->eco_score); // 0,4 kg CO₂/kg → B, la valeur envoyée « A » est ignorée
        $this->assertMatchesRegularExpression('/^TRF-\d{8}-0001$/', $t->reference);
        $this->assertNull($t->verdict_allegation);

        // Jointures visibles dans la liste et la fiche
        $this->get('/admin/transformations')->assertOk()->assertSee('Huilerie Zitouna')->assertSee('Huile d’olive extra');
        $this->get("/admin/transformations/{$t->id}")->assertOk()->assertSee('Ferme Oliviers')->assertSee('0,400');
        $this->get('/admin/transformations?eco_score=B')->assertSee($t->reference);
        $this->get('/admin/transformations?eco_score=E')->assertDontSee($t->reference);

        $this->get("/admin/transformations/{$t->id}/edit")->assertOk()->assertSee($t->reference);
        $this->get("/admin/transformateurs/{$tr->id}")->assertOk()->assertSee($t->reference);
        $this->put("/admin/transformations/{$t->id}", ['co2_kg' => 2500] + $data)->assertSessionHasNoErrors();
        $this->assertSame('E', $t->fresh()->eco_score);
        $this->put("/admin/transformations/{$t->id}", ['co2_kg' => null] + $data)->assertSessionHasNoErrors();
        $this->assertNull($t->fresh()->eco_score);

        $this->delete("/admin/transformations/{$t->id}")->assertRedirect();
        $this->assertDatabaseCount('transformations', 0);
    }

    public function test_estimation_falls_back_to_local_rules_without_ai(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $product = Product::factory()->create();

        $this->postJson('/admin/transformations/estimer', [])->assertUnprocessable()->assertJsonValidationErrors(['product_id', 'procede', 'quantite_kg']);

        $this->postJson('/admin/transformations/estimer', ['product_id' => $product->id, 'procede' => 'pasteurisation', 'quantite_kg' => 1000, 'allegation' => 'Zéro impact carbone'])
            ->assertOk()
            ->assertJson(['source' => 'local', 'co2_kg' => 150, 'eau_litres' => 2000, 'energie_kwh' => 250, 'eco_score' => 'A', 'verdict_allegation' => 'trompeuse']);

        $this->postJson('/admin/transformations/estimer', ['product_id' => $product->id, 'procede' => 'fromagerie', 'quantite_kg' => 100, 'allegation' => 'Produit éco-responsable'])
            ->assertJson(['eco_score' => 'C', 'verdict_allegation' => 'exageree']);
    }

    public function test_estimation_uses_gemini_answer_when_valid(): void
    {
        $this->actingAs($this->admin(), 'admin');
        config(['nutrition_ai.key' => 'test-key']);
        $answer = ['co2_kg' => 90, 'eau_litres' => 1800, 'energie_kwh' => 200, 'explication' => 'Le chauffage domine.', 'verdict_allegation' => 'trompeuse', 'commentaire_allegation' => 'L’étape émet du CO₂.'];
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode($answer)]]]]]])]);

        $this->postJson('/admin/transformations/estimer', ['product_id' => Product::factory()->create()->id, 'procede' => 'pasteurisation', 'quantite_kg' => 1000, 'allegation' => 'Zéro carbone'])
            ->assertOk()
            ->assertJson(['source' => 'ia', 'co2_kg' => 90, 'eco_score' => 'A', 'verdict_allegation' => 'trompeuse', 'explication' => 'Le chauffage domine.']);

        Http::assertSent(fn ($request) => str_contains($request['contents'][0]['parts'][0]['text'], 'Zéro carbone'));
    }

    public function test_invalid_ai_answer_is_rejected(): void
    {
        $this->actingAs($this->admin(), 'admin');
        config(['nutrition_ai.key' => 'test-key']);
        $bad = ['co2_kg' => -5, 'eau_litres' => 1, 'energie_kwh' => 1, 'explication' => 'x', 'verdict_allegation' => 'fiable', 'commentaire_allegation' => ''];
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode($bad)]]]]]])]);

        $this->postJson('/admin/transformations/estimer', ['product_id' => Product::factory()->create()->id, 'procede' => 'mouture', 'quantite_kg' => 100])
            ->assertJson(['source' => 'local', 'co2_kg' => 5]);
    }

    public function test_public_pages_show_footprint_and_greenwashing_alert(): void
    {
        $tr = Transformateur::factory()->create(['nom' => 'Conserverie El Bahia', 'type_activite' => 'conserverie']);
        $product = Product::factory()->create(['name' => 'Tomates bio']);
        Transformation::factory()->create(['transformateur_id' => $tr->id, 'product_id' => $product->id, 'procede' => 'appertisation', 'quantite_kg' => 100, 'co2_kg' => 50, 'allegation' => 'Zéro impact', 'verdict_allegation' => 'trompeuse']);
        Transformateur::factory()->create(['nom' => 'Moulin Vide', 'type_activite' => 'minoterie']);

        $this->get('/transformateurs')->assertOk()->assertSee('Conserverie El Bahia')->assertSee('Moulin Vide');
        $this->get('/transformateurs?eco_score=B')->assertSee('Conserverie El Bahia')->assertDontSee('Moulin Vide');
        $this->get("/transformateurs/{$tr->id}")->assertOk()->assertSee('Tomates bio')->assertSee('Allégation trompeuse');
        $this->get(route('catalog.show', $product))->assertOk()->assertSee('Transformation et empreinte')->assertSee('Conserverie El Bahia');
    }

    public function test_seeder_creates_demo_data(): void
    {
        $this->seed();
        $this->assertGreaterThan(0, Transformateur::count());
        $this->assertGreaterThan(0, Transformation::whereNotNull('eco_score')->count());
        $this->actingAs(User::where('role', 'admin')->first() ?? $this->admin(), 'admin');
        $this->get('/admin/dashboard')->assertOk()->assertSee('kg CO₂e');
    }
}
