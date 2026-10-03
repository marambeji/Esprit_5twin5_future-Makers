<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_photos_match_each_seeded_product_and_uploads_take_priority(): void
    {
        $this->seed(CatalogSeeder::class);
        $products = Product::all();
        $this->assertCount(15, $products);
        foreach ($products as $product) {
            $credit = $product->image_credit;
            $this->assertNotNull($credit, $product->name);
            $this->assertFileExists(public_path('images/products/'.$credit['file']));
            $this->assertSame(asset('images/products/'.$credit['file']), $product->image_url);
            $this->get('/catalogue/'.$product->id)->assertOk()->assertSee($credit['source']);
        }
        $product = $products->first();
        $product->image_path = 'products/custom.jpg';
        $this->assertNull($product->image_credit);
        $this->assertSame(route('catalog.image', $product), $product->image_url);
        $product->image_path = null;
        $product->name = 'Autre produit';
        $this->assertStringEndsWith('/images/products/placeholder.svg', $product->image_url);
    }

    public function test_image_upload_replacement_and_deletion(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create();
        $data = $product->only(['name', 'description', 'price', 'origin', 'category_id']);
        $image = UploadedFile::fake()->createWithContent('photo.jpg', file_get_contents(resource_path('assets/images/service1.jpg')));
        $this->put('/admin/products/'.$product->id, $data + ['image' => $image])->assertSessionHasNoErrors();
        $path = $product->fresh()->image_path;
        Storage::disk('public')->assertExists($path);
        $this->get('/catalogue/'.$product->id.'/image')->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->put('/admin/products/'.$product->id, $data)->assertSessionHasNoErrors();
        $this->assertSame($path, $product->fresh()->image_path);
        $this->put('/admin/products/'.$product->id, $data + ['image' => UploadedFile::fake()->createWithContent('replacement.jpg', file_get_contents(resource_path('assets/images/service2.jpg')))])->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($path);
        $newPath = $product->fresh()->image_path;
        Storage::disk('public')->assertExists($newPath);
        $this->delete('/admin/products/'.$product->id)->assertRedirect('/admin/products');
        Storage::disk('public')->assertMissing($newPath);
    }

    public function test_invalid_uploads_are_rejected(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create();
        $data = $product->only(['name', 'description', 'price', 'origin', 'category_id']);
        $this->put('/admin/products/'.$product->id, $data + ['image' => UploadedFile::fake()->create('document.pdf')])->assertSessionHasErrors('image');
        $this->put('/admin/products/'.$product->id, $data + ['image' => UploadedFile::fake()->createWithContent('large.jpg', file_get_contents(resource_path('assets/images/service1.jpg')))->size(2049)])->assertSessionHasErrors('image');
        $this->assertNull($product->fresh()->image_path);
        $this->get('/catalogue/'.$product->id.'/image')->assertNotFound();
    }
}
