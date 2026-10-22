<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\NutritionalAnalysis;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class NutritionNewProductTest extends TestCase
{
    use RefreshDatabase;

    private function data(): array
    {
        return [
            'create_product' => 1,
            'new_product' => ['name' => 'Carottes nouvelles', 'description' => 'Carottes fraîches', 'price' => '3.50', 'origin' => 'Nabeul', 'category_id' => Category::factory()->create()->id],
            'analysed_at' => today()->format('Y-m-d'), 'energy_kcal' => 41, 'protein' => 0.93, 'carbohydrates' => 9.58, 'sugars' => 4.74, 'fat' => 0.24, 'salt' => 0.17, 'is_demo' => 0,
        ];
    }

    public function test_new_product_and_analysis_are_created_together_without_existing_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'admin');
        $data = $this->data();
        $this->get(route('admin.nutrition.create'))->assertOk()->assertSee('Créer un nouveau produit');
        $data['reference_token'] = Crypt::encryptString(json_encode(['id' => 170393, 'description' => 'Carrots, raw', 'admin_id' => $admin->id, 'expires' => now()->addHour()->timestamp]));
        $data['ai_imported'] = 1;
        $this->post(route('admin.nutrition.store'), $data)->assertSessionHasErrors('ai_reviewed');
        $this->assertDatabaseCount('products', 0);
        $this->post(route('admin.nutrition.store'), $data + ['ai_reviewed' => 1])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('products', 1);
        $analysis = NutritionalAnalysis::firstOrFail();
        $this->assertEquals(Product::firstOrFail()->id, $analysis->product_id);
        $this->assertEquals(170393, $analysis->reference_fdc_id);
        $this->assertSame('Carottes nouvelles', $analysis->product->name);
    }

    public function test_invalid_product_or_nutrition_creates_nothing(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']), 'admin');
        $data = $this->data();
        $data['sugars'] = 90;
        $this->post(route('admin.nutrition.store'), $data)->assertSessionHasErrors('sugars');
        $data['sugars'] = 4;
        $data['new_product']['price'] = -1;
        $data['new_product']['category_id'] = 999999;
        $this->post(route('admin.nutrition.store'), $data)->assertSessionHasErrors(['new_product.price', 'new_product.category_id']);
        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('nutritional_analyses', 0);
    }

    public function test_existing_product_mode_ignores_new_product_fields(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']), 'admin');
        $product = Product::factory()->create();
        $data = $this->data();
        $data['create_product'] = 0;
        $data['product_id'] = $product->id;
        $this->post(route('admin.nutrition.store'), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseHas('nutritional_analyses', ['product_id' => $product->id]);
    }

    public function test_reference_cannot_be_used_by_another_administrator(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'admin');
        $data = $this->data();
        $data['reference_token'] = Crypt::encryptString(json_encode(['id' => 1, 'description' => 'Carrots, raw', 'admin_id' => $admin->id + 100, 'expires' => now()->addHour()->timestamp]));
        $data['ai_reviewed'] = 1;
        $this->post(route('admin.nutrition.store'), $data)->assertSessionHasErrors('reference_token');
        $this->assertDatabaseCount('products', 0);
    }
}
