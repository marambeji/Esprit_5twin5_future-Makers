<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_crud_and_uniqueness(): void
    {
        $this->get('/admin/categories/create')->assertOk();
        $this->post('/admin/categories', ['name' => 'Fruits', 'description' => 'Fruits frais'])->assertSessionHasNoErrors();
        $category = Category::firstOrFail();
        $this->get('/admin/categories')->assertOk()->assertSee('Fruits');
        $this->get('/admin/categories/'.$category->id)->assertOk()->assertSee('Fruits frais');
        $this->get('/admin/categories/'.$category->id.'/edit')->assertOk()->assertSee('value="Fruits"', false);
        $this->post('/admin/categories', ['name' => 'Fruits'])->assertSessionHasErrors('name');
        $this->put('/admin/categories/'.$category->id, ['name' => 'Fruits', 'description' => 'Nouvelle description'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'description' => 'Nouvelle description']);
        $this->delete('/admin/categories/'.$category->id)->assertRedirect('/admin/categories');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_product_crud_and_category_relation(): void
    {
        $category = Category::factory()->create(['name' => 'Légumes']);
        $other = Category::factory()->create();
        $data = ['name' => 'Tomates', 'description' => 'Tomates fraîches', 'price' => '4.50', 'origin' => 'Nabeul', 'category_id' => $category->id];
        $this->get('/admin/products/create')->assertOk()->assertSee('Légumes');
        $this->post('/admin/products', $data)->assertSessionHasNoErrors();
        $product = Product::firstOrFail();
        $this->assertTrue($product->category->is($category));
        $this->get('/admin/products')->assertOk()->assertSee('Tomates')->assertSee('Légumes');
        $this->get('/admin/products/'.$product->id)->assertOk()->assertSee('Tomates fraîches');
        $this->get('/admin/products/'.$product->id.'/edit')->assertOk()->assertSee('value="Tomates"', false);
        $this->get('/admin/categories/'.$category->id)->assertSee('Tomates');
        $this->put('/admin/products/'.$product->id, array_replace($data, ['name' => 'Tomates cerises', 'category_id' => $other->id]))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Tomates cerises', 'category_id' => $other->id]);
        $this->delete('/admin/products/'.$product->id)->assertRedirect('/admin/products');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_invalid_data_is_rejected_and_inputs_are_preserved(): void
    {
        $this->post('/admin/categories', [])->assertSessionHasErrors('name');
        $response = $this->from('/admin/products/create')->post('/admin/products', ['name' => 'Essai', 'price' => '-1', 'origin' => 'Tunis', 'category_id' => 999]);
        $response->assertRedirect('/admin/products/create')->assertSessionHasErrors(['description', 'price', 'category_id'])->assertSessionHasInput('name', 'Essai');
        $this->get('/admin/products/create')->assertSee('Ce champ est obligatoire.')->assertSee('value="Essai"', false);
        $this->assertDatabaseCount('products', 0);
        $category = Category::factory()->create();
        $this->post('/admin/products', ['name' => 'Essai', 'description' => 'Test', 'price' => '1.234', 'origin' => 'Tunis', 'category_id' => $category->id])->assertSessionHasErrors('price');
    }

    public function test_used_category_is_preserved_and_missing_records_return_404(): void
    {
        $product = Product::factory()->create();
        $this->delete('/admin/categories/'.$product->category_id)->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $product->category_id]);
        $this->get('/admin/products/999')->assertNotFound();
        $this->get('/admin/categories/999')->assertNotFound();
        $this->get('/catalogue/999')->assertNotFound();
    }

    public function test_public_catalog_filters_products_and_displays_details(): void
    {
        $first = Product::factory()->create(['name' => 'Pommes du verger']);
        Product::factory()->create(['name' => 'Autre aliment']);
        $this->get('/catalogue?category='.$first->category_id)->assertOk()->assertSee('Pommes du verger')->assertDontSee('Autre aliment');
        $this->get('/catalogue/'.$first->id)->assertOk()->assertSee($first->category->name)->assertSee($first->origin);
    }

    public function test_seeder_creates_related_data_without_duplicating_it(): void
    {
        $this->seed(CatalogSeeder::class);
        $this->assertDatabaseCount('categories', 5);
        $this->assertDatabaseCount('products', 15);
        $this->assertSame(3, Category::first()->products()->count());
        $this->seed(CatalogSeeder::class);
        $this->assertDatabaseCount('products', 15);
    }
}
