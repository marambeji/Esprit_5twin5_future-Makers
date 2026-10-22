<?php

namespace Tests\Feature;

use App\Models\NutritionalAnalysis;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FoodPhotoTest extends TestCase
{
    use RefreshDatabase;

    private function setupAdmin(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']), 'admin');
        config(['nutrition_ai.key' => 'test', 'nutrition_ai.model' => 'test-vision', 'nutrition_ai.usda_key' => 'DEMO_KEY']);
        Cache::flush();
        Http::preventStrayRequests();
    }

    private function fakeReferences(): void
    {
        $nutrients = [];
        foreach ([[1008, 'KCAL', 18], [1003, 'G', 0.9], [1005, 'G', 3.9], [2000, 'G', 2.6], [1004, 'G', 0.2], [1093, 'MG', 5]] as [$id, $unit, $value]) {
            $nutrients[] = ['nutrientId' => $id, 'unitName' => $unit, 'value' => $value];
        }
        Http::fake(['api.nal.usda.gov/*' => Http::response(['foods' => [
            ['fdcId' => 170457, 'description' => 'Tomatoes, red, raw', 'dataType' => 'SR Legacy', 'foodNutrients' => $nutrients],
            ['fdcId' => 999, 'description' => 'Tomatoes, cooked', 'dataType' => 'SR Legacy', 'foodNutrients' => []],
            ['fdcId' => 123, 'description' => 'Tomatoes, raw branded', 'dataType' => 'Branded', 'foodNutrients' => $nutrients],
        ]])]);
    }

    public function test_photo_identifies_only_food_not_nutrients(): void
    {
        $this->setupAdmin();
        $identification = ['name_fr' => 'Tomate', 'search_en' => 'tomato', 'warning_fr' => 'Variété à confirmer.'];
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['finishReason' => 'STOP', 'content' => ['parts' => [['text' => json_encode($identification)]]]]]])]);
        $photo = UploadedFile::fake()->createWithContent('photo.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
        $this->postJson(route('admin.nutrition.identify'), ['photo' => $photo])->assertOk()->assertExactJson($identification);
        $this->assertDatabaseCount('nutritional_analyses', 0);
    }

    public function test_reference_selection_requires_confirmation_and_saves_provenance(): void
    {
        $this->setupAdmin();
        $this->fakeReferences();
        $product = Product::factory()->create();
        $search = ['query' => 'tomato', 'state' => 'raw'];
        $this->postJson(route('admin.nutrition.references'), $search)->assertUnprocessable();
        Http::assertNothingSent();
        $response = $this->postJson(route('admin.nutrition.references'), $search + ['confirmed' => 1])->assertOk()->assertJsonCount(1, 'foods')->assertJsonPath('foods.0.values.salt', 0.01);
        $food = $response->json('foods.0');
        $data = $food['values'] + ['product_id' => $product->id, 'analysed_at' => today()->format('Y-m-d'), 'is_demo' => 0, 'ai_imported' => 1, 'reference_token' => $food['token']];
        $this->post(route('admin.nutrition.store'), $data)->assertSessionHasErrors('ai_reviewed');
        $this->assertDatabaseCount('nutritional_analyses', 0);
        $this->post(route('admin.nutrition.store'), $data + ['ai_reviewed' => 1])->assertSessionHasNoErrors();
        $analysis = NutritionalAnalysis::firstOrFail();
        $this->assertEquals(170457, $analysis->reference_fdc_id);
        $this->get(route('admin.nutrition.show', $analysis))->assertOk()->assertSee('Valeurs de référence')->assertSee('Tomatoes, red, raw');
        $this->get(route('catalog.show', $product))->assertOk()->assertSee('Valeurs de référence');
        $data['reference_token'] = 'tampered';
        $this->post(route('admin.nutrition.store'), $data + ['ai_reviewed' => 1])->assertSessionHasErrors('reference_token');
    }

    public function test_missing_values_stay_null_and_reference_requests_are_cached(): void
    {
        $this->setupAdmin();
        $this->fakeReferences();
        $search = ['query' => 'tomato', 'state' => 'cooked', 'product_id' => Product::factory()->create()->id, 'confirmed' => 1];
        foreach ([1, 2] as $attempt) {
            $this->postJson(route('admin.nutrition.references'), $search)->assertOk()->assertJsonCount(1, 'foods')->assertJsonPath('foods.0.values.salt', null);
        }
        Http::assertSentCount(1);
    }

    public function test_reference_endpoints_are_admin_only_and_fail_gracefully(): void
    {
        $this->postJson(route('admin.nutrition.identify'))->assertUnauthorized();
        $this->postJson(route('admin.nutrition.references'))->assertUnauthorized();
        $this->actingAs(User::factory()->create(), 'admin')->postJson(route('admin.nutrition.references'))->assertForbidden();
        $this->setupAdmin();
        Http::fake(['api.nal.usda.gov/*' => Http::response([], 429)]);
        $this->postJson(route('admin.nutrition.references'), ['query' => 'tomato', 'state' => 'raw', 'product_id' => Product::factory()->create()->id, 'confirmed' => 1])->assertStatus(503);
        $this->assertDatabaseCount('nutritional_analyses', 0);
    }

    public function test_search_excludes_unrelated_raw_foods_and_matches_whole_words(): void
    {
        $this->setupAdmin();
        $foods = [];
        foreach (['Carrots, raw', 'Carrots, baby, raw', 'Apricots, raw', 'Bananas, raw', 'Carrotwood fruit, raw', 'Carrots, boiled'] as $i => $description) {
            $foods[] = ['fdcId' => 100 + $i, 'description' => $description, 'dataType' => 'SR Legacy', 'foodNutrients' => []];
        }
        Http::fake(['api.nal.usda.gov/*' => Http::response(['foods' => $foods])]);
        $product = Product::factory()->create();
        foreach (['Carrot', ' carrots '] as $query) {
            $this->postJson(route('admin.nutrition.references'), ['query' => $query, 'state' => 'raw', 'product_id' => $product->id, 'confirmed' => 1])
                ->assertOk()->assertJsonCount(2, 'foods')
                ->assertJsonPath('foods.0.description', 'Carrots, raw')
                ->assertJsonPath('foods.1.description', 'Carrots, baby, raw');
        }
    }

    public function test_multiword_food_search_requires_all_words(): void
    {
        $this->setupAdmin();
        Http::fake(['api.nal.usda.gov/*' => Http::response(['foods' => [
            ['fdcId' => 1, 'description' => 'Sweet potato, raw', 'dataType' => 'SR Legacy', 'foodNutrients' => []],
            ['fdcId' => 2, 'description' => 'Potatoes, raw', 'dataType' => 'SR Legacy', 'foodNutrients' => []],
            ['fdcId' => 3, 'description' => 'Sweet corn, raw', 'dataType' => 'SR Legacy', 'foodNutrients' => []],
        ]])]);
        $this->postJson(route('admin.nutrition.references'), ['query' => 'sweet potatoes', 'state' => 'raw', 'product_id' => Product::factory()->create()->id, 'confirmed' => 1])
            ->assertOk()->assertJsonCount(1, 'foods')->assertJsonPath('foods.0.description', 'Sweet potato, raw');
    }
}
