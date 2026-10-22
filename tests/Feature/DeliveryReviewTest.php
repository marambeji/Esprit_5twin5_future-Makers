<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Commande;
use App\Models\Delivery;
use App\Models\Distributor;
use App\Models\Product;
use App\Models\User;
use App\Services\DeliveryReviewAnalyzer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DeliveryReviewTest extends TestCase
{
    use RefreshDatabase;

    private function delivery(string $status = 'livree'): Delivery
    {
        $client = User::factory()->create();
        $category = Category::firstOrCreate(['name' => 'Fruits']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Pomme', 'description' => 'Bio', 'price' => 2, 'origin' => 'Tunisie']);
        $commande = Commande::create(['user_id' => $client->id, 'product_id' => $product->id, 'quantite' => 1, 'adresse_livraison' => 'Tunis', 'telephone' => '20123456', 'statut' => 'confirmee']);
        $driver = Distributor::create(['name' => 'Ali '.fake()->unique()->uuid(), 'email' => fake()->unique()->safeEmail(), 'phone' => '20123456', 'city' => 'Tunis', 'address' => 'Tunis']);

        return Delivery::create(['commande_id' => $commande->id, 'distributor_id' => $driver->id, 'destination' => 'Tunis', 'delivery_date' => today(), 'status' => $status]);
    }

    private function fakeAnalysis(array $result = ['sentiment' => 'negatif', 'theme' => 'retard']): void
    {
        config(['nutrition_ai.key' => 'test-key']);
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode($result)]]]]]])]);
    }

    public function test_owner_can_review_once_and_analysis_is_saved(): void
    {
        $d = $this->delivery();
        $this->fakeAnalysis();
        $this->actingAs($d->commande->user)->get('/commandes/'.$d->commande_id)->assertOk()->assertSee('Envoyer mon avis');
        $this->post('/livraisons/'.$d->id.'/avis', ['note' => 2, 'commentaire' => 'Arrive en retard'])->assertSessionHasNoErrors()->assertRedirect(route('commandes.show', $d->commande_id));
        $this->assertDatabaseHas('avis_livraisons', ['delivery_id' => $d->id, 'note' => 2, 'sentiment' => 'negatif', 'theme' => 'retard']);
        $this->post('/livraisons/'.$d->id.'/avis', ['note' => 5, 'commentaire' => 'Autre avis'])->assertSessionHasErrors('avis');
        $this->assertDatabaseCount('avis_livraisons', 1);
        $this->get('/commandes/'.$d->commande_id)->assertOk()->assertSee('Arrive en retard')->assertDontSee('Envoyer mon avis');
    }

    public function test_guest_and_other_client_cannot_review(): void
    {
        $d = $this->delivery();
        Http::preventStrayRequests();
        $payload = ['note' => 5, 'commentaire' => 'Bien'];
        $this->post('/livraisons/'.$d->id.'/avis', $payload)->assertRedirect();
        $this->actingAs(User::factory()->create())->post('/livraisons/'.$d->id.'/avis', $payload)->assertNotFound();
        $this->assertDatabaseCount('avis_livraisons', 0);
    }

    public function test_unfinished_delivery_cannot_be_reviewed(): void
    {
        $d = $this->delivery('en_cours');
        $this->actingAs($d->commande->user)->post('/livraisons/'.$d->id.'/avis', ['note' => 5, 'commentaire' => 'Bien'])->assertForbidden();
        $this->get('/commandes/'.$d->commande_id)->assertDontSee('Envoyer mon avis');
        $this->assertDatabaseCount('avis_livraisons', 0);
    }

    public function test_review_input_is_validated(): void
    {
        $d = $this->delivery();
        $this->actingAs($d->commande->user);
        foreach ([0, 6, 2.5] as $note) {
            $this->post('/livraisons/'.$d->id.'/avis', ['note' => $note, 'commentaire' => 'Bien'])->assertSessionHasErrors('note');
        }
        foreach (['', str_repeat('x', 1001)] as $comment) {
            $this->post('/livraisons/'.$d->id.'/avis', ['note' => 5, 'commentaire' => $comment])->assertSessionHasErrors('commentaire');
        }
        $this->assertDatabaseCount('avis_livraisons', 0);
    }

    public function test_review_is_kept_when_ai_is_unavailable(): void
    {
        $d = $this->delivery();
        config(['nutrition_ai.key' => null]);
        Http::preventStrayRequests();
        $this->actingAs($d->commande->user)->post('/livraisons/'.$d->id.'/avis', ['note' => 4, 'commentaire' => 'Bien'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('avis_livraisons', ['delivery_id' => $d->id, 'note' => 4, 'sentiment' => null, 'theme' => null]);
        Http::assertNothingSent();
    }

    public function test_invalid_ai_results_and_failures_are_not_saved_as_classifications(): void
    {
        foreach ([['sentiment' => 'inconnu', 'theme' => 'retard'], ['sentiment' => ['negatif'], 'theme' => 'retard']] as $result) {
            $this->fakeAnalysis($result);
            $this->assertSame(['sentiment' => null, 'theme' => null], app(DeliveryReviewAnalyzer::class)->analyze('Retard'));
        }
        Http::fake(['*' => Http::response([], 503)]);
        $this->assertSame(['sentiment' => null, 'theme' => null], app(DeliveryReviewAnalyzer::class)->analyze('Retard'));
    }

    public function test_admin_sees_average_and_negative_reviews(): void
    {
        $d = $this->delivery();
        $d->avis()->create(['note' => 1, 'commentaire' => 'Retard important', 'sentiment' => 'negatif', 'theme' => 'retard']);
        $other = $this->delivery();
        $other->updateQuietly(['distributor_id' => $d->distributor_id]);
        $other->avis()->create(['note' => 5, 'commentaire' => 'Tres bien', 'sentiment' => 'positif', 'theme' => 'autre']);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'admin')->get('/admin/distributors')->assertOk()->assertSee('3.0/5 (2 avis)');
        $this->get('/admin/distributors/'.$d->distributor_id)->assertOk()->assertSee('Retard important')->assertDontSee('Tres bien');
        $this->get('/admin/deliveries/'.$d->id)->assertOk()->assertSee('Retard important');
        $this->actingAs(User::factory()->create(), 'web')->get('/livraisons/'.$d->id.'/avis')->assertStatus(405);
    }
}
