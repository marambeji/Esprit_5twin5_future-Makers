<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NutritionLabelTest extends TestCase
{
    use RefreshDatabase;

    private function photo(): File
    {
        return UploadedFile::fake()->createWithContent('label.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
    }

    private function endpoint(): string
    {
        return route('admin.nutrition.read-label');
    }

    private function configure(): void
    {
        config(['nutrition_ai.key' => 'test-key', 'nutrition_ai.model' => 'test-vision']);
        $this->actingAs(User::factory()->create(['role' => 'admin']), 'admin');
        Http::preventStrayRequests();
    }

    private function labelData(array $overrides = []): array
    {
        return array_replace(['basis' => '100g', 'energy_kcal' => 200, 'protein' => 5, 'carbohydrates' => 30, 'sugars' => null, 'fat' => 7, 'salt' => 0.5, 'warnings' => ['Sucres illisibles.']], $overrides);
    }

    private function fakeResponse(array $data): void
    {
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['finishReason' => 'STOP', 'content' => ['parts' => [['text' => json_encode($data)]]]]]])]);
    }

    public function test_reading_returns_suggestions_without_saving_and_sends_image(): void
    {
        $this->configure();
        $this->fakeResponse($this->labelData());
        $this->postJson($this->endpoint(), ['photo' => $this->photo()])
            ->assertOk()->assertJsonPath('values.energy_kcal', 200)->assertJsonPath('values.sugars', null);
        $this->assertDatabaseCount('nutritional_analyses', 0);
        Http::assertSent(fn ($request) => $request->hasHeader('x-goog-api-key', 'test-key')
            && str_ends_with($request->url(), '/test-vision:generateContent')
            && $request['contents'][0]['parts'][1]['inlineData']['mimeType'] === 'image/png'
            && $request['generationConfig']['responseMimeType'] === 'application/json');
    }

    public function test_wrong_basis_never_prefills_values(): void
    {
        $this->configure();
        $this->fakeResponse($this->labelData(['basis' => '100ml']));
        $this->postJson($this->endpoint(), ['photo' => $this->photo()])
            ->assertOk()->assertJsonPath('values.energy_kcal', null)->assertJsonPath('values.salt', null);
    }

    public function test_access_files_and_missing_configuration(): void
    {
        Http::fake();
        $this->postJson($this->endpoint())->assertUnauthorized();
        $this->actingAs(User::factory()->create(), 'admin')->postJson($this->endpoint())->assertForbidden();
        $this->configure();
        $this->postJson($this->endpoint(), ['photo' => UploadedFile::fake()->create('file.pdf', 20, 'application/pdf')])->assertUnprocessable();
        $this->postJson($this->endpoint(), ['photo' => $this->photo()->size(2049)])->assertUnprocessable();
        config(['nutrition_ai.key' => null]);
        $this->postJson($this->endpoint(), ['photo' => $this->photo()])->assertStatus(503)->assertJsonFragment(['message' => 'La lecture IA n’est pas configurée. Renseignez GEMINI_API_KEY et GEMINI_VISION_MODEL dans le fichier .env.']);
        Http::assertNothingSent();
    }

    public function test_provider_errors_and_invalid_values_are_handled(): void
    {
        $this->configure();
        Http::fake(['*' => Http::response(['secret' => 'never show this'], 429)]);
        $this->postJson($this->endpoint(), ['photo' => $this->photo()])->assertStatus(503)->assertDontSee('never show this');
        $this->fakeResponse($this->labelData(['energy_kcal' => -1]));
        $this->postJson($this->endpoint(), ['photo' => $this->photo()])->assertStatus(503);
        $this->fakeResponse($this->labelData(['sugars' => 80]));
        $this->postJson($this->endpoint(), ['photo' => $this->photo()])->assertOk()->assertJsonCount(2, 'warnings');
    }

    public function test_user_must_review_then_can_save_corrected_values(): void
    {
        $this->configure();
        $product = Product::factory()->create();
        $data = ['product_id' => $product->id, 'analysed_at' => today()->format('Y-m-d'), 'energy_kcal' => 200, 'protein' => 5, 'carbohydrates' => 30, 'sugars' => 3, 'fat' => 7, 'salt' => 0.5, 'is_demo' => 0, 'ai_imported' => 1];
        $this->get(route('admin.nutrition.create'))->assertOk()->assertSee('Lire une étiquette avec l’IA');
        $this->post(route('admin.nutrition.store'), $data)->assertSessionHasErrors('ai_reviewed');
        $this->assertDatabaseCount('nutritional_analyses', 0);
        $this->post(route('admin.nutrition.store'), $data + ['ai_reviewed' => 1])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('nutritional_analyses', ['product_id' => $product->id, 'sugars' => 3]);
    }
}
