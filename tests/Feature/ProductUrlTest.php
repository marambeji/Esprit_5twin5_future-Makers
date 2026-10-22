<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_uses_names_and_redirects_old_urls(): void
    {
        $product = Product::factory()->create(['name' => 'Huile de tournesol']);
        $this->assertSame('huile-de-tournesol', $product->slug);
        $this->assertSame(url('/catalogue/huile-de-tournesol'), route('catalog.show', $product));
        $this->get('/catalogue')->assertSee('/catalogue/huile-de-tournesol');
        $this->get('/catalogue/huile-de-tournesol')->assertOk()->assertSee('Huile de tournesol');
        $this->get('/catalogue/'.$product->id)->assertStatus(301)->assertRedirect('/catalogue/huile-de-tournesol');
        $this->actingAs(User::factory()->create(['role' => 'admin']), 'admin')->get('/admin/products/'.$product->id)->assertOk();
    }

    public function test_duplicate_and_numeric_names_have_unique_stable_urls(): void
    {
        $first = Product::factory()->create(['name' => 'Blé dur']);
        $second = Product::factory()->create(['name' => 'Blé dur']);
        $numeric = Product::factory()->create(['name' => '123']);
        $this->assertSame('ble-dur', $first->slug);
        $this->assertSame('ble-dur-2', $second->slug);
        $this->assertSame('produit-123', $numeric->slug);
        $first->update(['name' => 'Blé tunisien']);
        $this->get('/catalogue/ble-dur')->assertOk()->assertSee('Blé tunisien');
        $this->get('/catalogue/produit-inexistant')->assertNotFound();
    }
}
