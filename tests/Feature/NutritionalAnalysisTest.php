<?php

namespace Tests\Feature;

use App\Models\NutritionalAnalysis;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\NutritionalAnalysisSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NutritionalAnalysisTest extends TestCase
{
    use RefreshDatabase;

    private function data(Product $product): array
    {
        return ['product_id' => $product->id, 'analysed_at' => today()->toDateString(), 'energy_kcal' => '150.25', 'protein' => '5.50', 'carbohydrates' => '20', 'sugars' => '8', 'fat' => '5', 'salt' => '0.30', 'source' => 'Laboratoire de test', 'notes' => 'Analyse de test', 'is_demo' => '1'];
    }

    public function test_full_crud_and_product_relation(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']), 'admin');
        $product = Product::factory()->create();
        $other = Product::factory()->create();
        $data = $this->data($product);
        $this->get(route('admin.nutrition.create', ['product_id' => $product->id]))->assertOk()->assertSee($product->name);
        $this->post(route('admin.nutrition.store'), $data)->assertSessionHasNoErrors();
        $analysis = NutritionalAnalysis::firstOrFail();
        $this->assertTrue($analysis->product->is($product));
        $this->assertTrue($product->nutritionalAnalyses->first()->is($analysis));
        $this->get(route('admin.nutrition.index'))->assertOk()->assertSee($product->name);
        $this->get(route('admin.nutrition.show', $analysis))->assertOk()->assertSee('Laboratoire de test');
        $this->get(route('admin.nutrition.edit', $analysis))->assertOk()->assertSee('value="5.50"', false);
        $this->get(route('admin.products.show', $product))->assertOk()->assertSee('Analyse de test');
        $this->get(route('catalog.show', $product))->assertOk()->assertSee('Analyse de test')->assertSee('Données fictives');
        $this->get(route('catalog.show', $other))->assertDontSee('Analyse de test');
        $this->delete(route('admin.products.destroy', $product))->assertSessionHas('error');
        $this->assertModelExists($product);
        $this->put(route('admin.nutrition.update', $analysis), array_replace($data, ['product_id' => $other->id, 'protein' => '6']))->assertSessionHasNoErrors();
        $this->assertSame($other->id, $analysis->fresh()->product_id);
        $this->get(route('admin.nutrition.index', ['product_id' => $product->id]))->assertSee('Aucune analyse nutritionnelle.');
        $this->delete(route('admin.nutrition.destroy', $analysis))->assertRedirect(route('admin.nutrition.index'));
        $this->assertModelMissing($analysis);
        $this->delete(route('admin.products.destroy', $other))->assertRedirect(route('admin.products.index'));
    }

    public function test_invalid_values_are_rejected_and_inputs_preserved(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']), 'admin');
        $data = $this->data(Product::factory()->create());
        $this->post(route('admin.nutrition.store'), [])->assertSessionHasErrors(['product_id', 'analysed_at', 'energy_kcal', 'protein']);
        foreach ([['product_id' => 99999], ['analysed_at' => today()->addDay()->toDateString()], ['energy_kcal' => -1], ['energy_kcal' => 901], ['protein' => 101], ['salt' => -1], ['fat' => 'abc'], ['sugars' => 21], ['protein' => '3.456']] as $invalid) {
            $this->post(route('admin.nutrition.store'), array_replace($data, $invalid))->assertSessionHasErrors(array_key_first($invalid));
        }
        $this->from(route('admin.nutrition.create'))->post(route('admin.nutrition.store'), array_replace($data, ['protein' => 80]))->assertSessionHasErrors('protein')->assertSessionHasInput('source', 'Laboratoire de test');
        $this->get(route('admin.nutrition.create'))->assertSee('La somme protéines')->assertSee('Laboratoire de test');
        $this->assertDatabaseCount('nutritional_analyses', 0);
    }

    public function test_admin_access_required_and_public_is_read_only(): void
    {
        $analysis = NutritionalAnalysis::factory()->create();
        $this->get(route('admin.nutrition.index'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.nutrition.store'), $this->data($analysis->product))->assertRedirect(route('admin.login'));
        $this->actingAs(User::factory()->create(), 'web')->delete(route('admin.nutrition.destroy', $analysis))->assertRedirect(route('admin.login'));
        $this->assertModelExists($analysis);
        $this->get(route('catalog.show', $analysis->product))->assertOk()->assertDontSee('Gérer cette analyse');
        $this->actingAs(User::factory()->create(['role' => 'admin']), 'admin')->get('/admin/analyses-nutritionnelles/9999')->assertNotFound();
    }

    public function test_factory_seeder_is_related_and_repeatable(): void
    {
        Product::factory()->count(2)->create();
        $this->seed(NutritionalAnalysisSeeder::class);
        $this->assertDatabaseCount('nutritional_analyses', 2);
        $first = NutritionalAnalysis::first();
        $this->assertTrue($first->is_demo);
        $this->assertNotNull($first->product);
        $this->seed(NutritionalAnalysisSeeder::class);
        $this->assertDatabaseCount('nutritional_analyses', 2);
        $this->assertSame($first->energy_kcal, $first->fresh()->energy_kcal);
    }
}
