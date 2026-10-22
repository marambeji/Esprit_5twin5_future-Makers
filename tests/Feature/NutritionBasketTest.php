<?php

namespace Tests\Feature;

use App\Models\NutritionalAnalysis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NutritionBasketTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_catalogue_and_entry_point_are_accessible(): void
    {
        $this->get(route('nutrition-basket.index'))->assertOk()->assertSee('Aucune analyse disponible');
        $this->get(route('catalog.index'))->assertOk()->assertSee(route('nutrition-basket.index'));
    }

    public function test_weighted_totals_portions_and_reference_selection_without_writes(): void
    {
        $first = NutritionalAnalysis::factory()->create(['energy_kcal' => 40, 'protein' => 2, 'carbohydrates' => 6, 'sugars' => 3, 'fat' => 1, 'salt' => 0.1, 'is_demo' => false, 'source' => 'Source A']);
        $second = NutritionalAnalysis::factory()->create(['energy_kcal' => 100, 'protein' => 4, 'carbohydrates' => 10, 'sugars' => 2, 'fat' => 5, 'salt' => 0.2, 'is_demo' => true]);
        // A newer analysis must not replace the reference explicitly selected.
        NutritionalAnalysis::factory()->create(['product_id' => $first->product_id, 'energy_kcal' => 900]);
        $response = $this->post(route('nutrition-basket.calculate'), ['items' => [
            ['analysis_id' => $first->id, 'grams' => 250],
            ['analysis_id' => $second->id, 'grams' => 50],
        ], 'portions' => 2]);
        $response->assertOk()->assertSee('Source A')->assertSee('données fictives de démonstration');
        $result = $response->viewData('result');
        $this->assertEqualsWithDelta(150, $result['totals']['energy_kcal'], 0.00001);
        $this->assertEqualsWithDelta(7, $result['totals']['protein'], 0.00001);
        $this->assertEqualsWithDelta(20, $result['totals']['carbohydrates'], 0.00001);
        $this->assertEqualsWithDelta(8.5, $result['totals']['sugars'], 0.00001);
        $this->assertEqualsWithDelta(5, $result['totals']['fat'], 0.00001);
        $this->assertEqualsWithDelta(0.35, $result['totals']['salt'], 0.00001);
        $this->assertEquals(300, $result['weight']);
        $this->assertEquals(2, $result['portions']);
        $response->assertSee('75,00')->assertSee('50,00');
        $this->assertDatabaseCount('nutritional_analyses', 3);
        $this->assertDatabaseCount('commandes', 0);
    }

    public function test_invalid_quantities_references_duplicates_and_portions_are_rejected(): void
    {
        $analysis = NutritionalAnalysis::factory()->create();
        foreach ([0, -1, 10001, 'abc', '1.001'] as $grams) {
            $this->from(route('nutrition-basket.index'))->post(route('nutrition-basket.calculate'), [
                'items' => [['analysis_id' => $analysis->id, 'grams' => $grams]], 'portions' => 1,
            ])->assertSessionHasErrors('items.0.grams');
        }
        $this->post(route('nutrition-basket.calculate'), ['items' => [['analysis_id' => 999999, 'grams' => 100]], 'portions' => 0])->assertSessionHasErrors(['items.0.analysis_id', 'portions']);
        $this->post(route('nutrition-basket.calculate'), ['items' => array_fill(0, 2, ['analysis_id' => $analysis->id, 'grams' => 100]), 'portions' => 1])->assertSessionHasErrors('items.0.analysis_id');
        $this->post(route('nutrition-basket.calculate'), ['items' => [], 'portions' => 1])->assertSessionHasErrors('items');
    }

    public function test_fractional_quantities_keep_precision_until_display(): void
    {
        $analysis = NutritionalAnalysis::factory()->create(['salt' => 0.01]);
        $response = $this->post(route('nutrition-basket.calculate'), ['items' => [['analysis_id' => $analysis->id, 'grams' => '0.01']], 'portions' => 100]);
        $response->assertOk();
        $this->assertEqualsWithDelta(0.000001, $response->viewData('result')['totals']['salt'], 0.00000001);
    }
}
