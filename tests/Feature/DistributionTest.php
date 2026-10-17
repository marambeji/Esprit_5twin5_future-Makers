<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Commande;
use App\Models\Delivery;
use App\Models\Distributor;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DistributionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'password' => 'secret-pass-1']);
    }

    private function distributor(array $o = []): Distributor
    {
        return Distributor::create($o + ['name' => 'Agro Tunis', 'email' => 'a@t.test', 'phone' => '71 123 456', 'city' => 'Tunis', 'address' => '1 rue X']);
    }

    private function product(): Product
    {
        return Product::firstOrCreate(['name' => 'Tomate'], ['category_id' => Category::firstOrCreate(['name' => 'Fruits'])->id, 'description' => 'Bio', 'price' => 2.5, 'origin' => 'Tunisie']);
    }

    private function commande(User $client, string $statut = 'en_attente'): Commande
    {
        return Commande::create(['user_id' => $client->id, 'product_id' => $this->product()->id, 'quantite' => 3, 'adresse_livraison' => '10 rue Y, Tunis', 'telephone' => '20 000 001', 'statut' => $statut]);
    }

    public function test_admin_can_login(): void
    {
        $admin = $this->admin();
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'secret-pass-1'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_admin_area_requires_admin(): void
    {
        foreach (['/admin/distributors', '/admin/commandes', '/admin/deliveries'] as $url) {
            $this->get($url)->assertRedirect();
        }
        $this->actingAs(User::factory()->create(['role' => 'user']), 'web')->get('/admin/commandes')->assertRedirect();
    }

    public function test_distributor_crud_takes_identity_from_linked_account(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $user = User::factory()->create(['name' => 'Ali Livreur', 'username' => 'livreur1', 'email' => 'ali@t.test', 'phone' => '20123456']);
        $base = ['city' => 'Tunis', 'address' => '1 rue X'];
        $this->post('/admin/distributors', [])->assertSessionHasErrors(['user_id', 'city', 'address']);
        $this->post('/admin/distributors', $base + ['user_id' => $user->id, 'actif' => 1])->assertRedirect();
        $d = Distributor::firstOrFail();
        $this->assertSame(['Ali Livreur', 'ali@t.test', '20123456'], [$d->name, $d->email, $d->phone]);
        $other = User::factory()->create();
        $this->post('/admin/distributors', $base + ['user_id' => $user->id])->assertSessionHasErrors('user_id'); // compte déjà lié
        $this->get('/admin/distributors/create')->assertDontSee('ali@t.test');
        $noPhone = User::factory()->create(['phone' => null]);
        $this->post('/admin/distributors', $base + ['user_id' => $noPhone->id])->assertSessionHasErrors('phone');
        $this->put("/admin/distributors/{$d->id}", ['city' => 'Bizerte', 'user_id' => $user->id, 'address' => '1 rue X'])->assertSessionHasNoErrors();
        $this->assertFalse($d->fresh()->actif);
        $this->get('/admin/distributors?q=Bizerte')->assertSee('Ali Livreur');
        $this->get('/admin/distributors?q=zzz')->assertDontSee('Ali Livreur');
        $this->get("/admin/distributors/{$d->id}")->assertOk()->assertSee('livreur1');
        $this->delete("/admin/distributors/{$d->id}")->assertRedirect();
        $this->assertDatabaseCount('distributors', 0);
        $this->assertDatabaseHas('users', ['username' => 'livreur1']); // le compte utilisateur est conservé
    }

    public function test_legacy_distributor_without_account_can_still_be_edited(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $d = $this->distributor();
        $this->put("/admin/distributors/{$d->id}", ['city' => 'Sfax', 'address' => '2 rue Y', 'actif' => 1])->assertSessionHasNoErrors();
        $this->assertSame('Sfax', $d->fresh()->city);
        $this->assertSame('Agro Tunis', $d->fresh()->name);
    }

    public function test_linked_user_logs_in_and_sees_his_space(): void
    {
        $user = User::factory()->create(['username' => 'livreur1', 'password' => 'motdepasse1']);
        $this->distributor(['user_id' => $user->id]);
        $this->post('/login', ['username' => 'livreur1', 'password' => 'motdepasse1'])->assertRedirect();
        $this->assertAuthenticated('web');
        $this->get('/espace-distributeur/livraisons')->assertOk();
    }

    public function test_full_order_to_delivery_workflow(): void
    {
        $client = User::factory()->create();
        $driverUser = User::factory()->create();
        $distributor = $this->distributor(['user_id' => $driverUser->id]);
        $product = $this->product();

        // Le client commande (validation puis succès).
        $this->actingAs($client, 'web');
        $this->post('/commandes', ['quantite' => 0])->assertSessionHasErrors(['product_id', 'quantite', 'adresse_livraison', 'telephone']);
        $this->post('/commandes', ['product_id' => $product->id, 'quantite' => 4, 'adresse_livraison' => '10 rue Y, Tunis', 'telephone' => '20 000 001'])->assertRedirect();
        $commande = Commande::firstOrFail();
        $this->assertSame('en_attente', $commande->statut);
        $this->assertMatchesRegularExpression('/^CMD-\d{8}-0001$/', $commande->reference);

        // L'admin confirme et crée la livraison.
        $this->actingAs($this->admin(), 'admin');
        $this->post('/admin/deliveries', ['commande_id' => $commande->id, 'distributor_id' => $distributor->id, 'delivery_date' => now()->format('Y-m-d')])->assertSessionHasErrors('commande_id');
        $this->patch("/admin/commandes/{$commande->id}/confirmer")->assertRedirect();
        $this->assertSame('confirmee', $commande->fresh()->statut);
        $payload = ['commande_id' => $commande->id, 'distributor_id' => $distributor->id, 'delivery_date' => now()->format('Y-m-d')];
        $this->post('/admin/deliveries', ['delivery_date' => now()->subDay()->format('Y-m-d')] + $payload)->assertSessionHasErrors('delivery_date');
        $this->post('/admin/deliveries', $payload)->assertRedirect();
        $this->post('/admin/deliveries', $payload)->assertSessionHasErrors('commande_id'); // une seule livraison par commande
        $delivery = Delivery::firstOrFail();
        $this->assertSame('planifiee', $delivery->status);
        $this->assertSame('10 rue Y, Tunis', $delivery->destination);
        $this->assertMatchesRegularExpression('/^LIV-\d{8}-0001$/', $delivery->reference);
        $this->delete("/admin/commandes/{$commande->id}")->assertSessionHas('error');

        // Le distributeur avance le statut, une étape à la fois.
        $this->actingAs($driverUser, 'web')->get('/espace-distributeur/livraisons')->assertOk()->assertSee($delivery->reference);
        $this->get("/espace-distributeur/livraisons/{$delivery->id}")->assertOk()->assertSee('10 rue Y, Tunis');
        $this->patch("/espace-distributeur/livraisons/{$delivery->id}/statut")->assertSessionHas('success');
        $this->assertSame('en_cours', $delivery->fresh()->status);
        $this->patch("/espace-distributeur/livraisons/{$delivery->id}/statut");
        $this->assertSame('livree', $delivery->fresh()->status);
        $this->patch("/espace-distributeur/livraisons/{$delivery->id}/statut")->assertSessionHas('error');
        $this->assertSame('livree', $delivery->fresh()->status);

        // Le client suit sa commande et sa livraison.
        $this->actingAs($client, 'web')->get("/commandes/{$commande->id}")->assertOk()->assertSee($delivery->reference)->assertSee('Livrée');
    }

    public function test_admin_refuses_order_with_reason_and_client_cancels_pending(): void
    {
        $client = User::factory()->create();
        $a = $this->commande($client);
        $b = $this->commande($client);
        $this->actingAs($this->admin(), 'admin');
        $this->patch("/admin/commandes/{$a->id}/refuser")->assertSessionHasErrors('motif_refus');
        $this->patch("/admin/commandes/{$a->id}/refuser", ['motif_refus' => 'Indisponible'])->assertRedirect();
        $this->assertSame('refusee', $a->fresh()->statut);
        $this->patch("/admin/commandes/{$a->id}/confirmer")->assertSessionHas('error');

        $this->actingAs($client, 'web')->patch("/commandes/{$b->id}/annuler")->assertRedirect();
        $this->assertSame('annulee', $b->fresh()->statut);
        $this->patch("/commandes/{$a->id}/annuler")->assertSessionHas('error');
    }

    public function test_users_cannot_access_other_users_data(): void
    {
        $client = User::factory()->create();
        $other = User::factory()->create();
        $commande = $this->commande($client);
        $driverUser = User::factory()->create();
        $mine = $this->distributor(['user_id' => $driverUser->id]);
        $theirs = $this->distributor(['name' => 'Autre', 'email' => 'b@t.test']);
        $delivery = $theirs->deliveries()->create(['destination' => 'X', 'delivery_date' => now(), 'status' => 'planifiee']);

        $this->actingAs($other, 'web');
        $this->get("/commandes/{$commande->id}")->assertNotFound();
        $this->patch("/commandes/{$commande->id}/annuler")->assertNotFound();
        $this->get('/commandes')->assertOk()->assertDontSee($commande->reference);
        $this->get('/espace-distributeur/livraisons')->assertForbidden(); // pas de compte distributeur lié

        $this->actingAs($driverUser, 'web');
        $this->get("/espace-distributeur/livraisons/{$delivery->id}")->assertNotFound();
        $this->patch("/espace-distributeur/livraisons/{$delivery->id}/statut")->assertNotFound();
        $mine->update(['actif' => false]);
        $this->get('/espace-distributeur/livraisons')->assertForbidden();

        auth('web')->logout();
        $this->get('/commandes')->assertRedirect(route('login'));
    }

    public function test_delivery_edit_rules_and_legacy_delivery(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $d = $this->distributor();
        $legacy = $d->deliveries()->create(['destination' => 'Marché', 'delivery_date' => '2026-11-01', 'status' => 'planifiee']);
        $payload = ['distributor_id' => $d->id, 'destination' => 'Marché', 'delivery_date' => '2026-11-01'];
        $this->put("/admin/deliveries/{$legacy->id}", ['status' => 'livree'] + $payload)->assertSessionHasErrors('status'); // pas de saut d'étape
        $this->put("/admin/deliveries/{$legacy->id}", ['status' => 'en_cours'] + $payload)->assertSessionHasNoErrors();
        $this->put("/admin/deliveries/{$legacy->id}", ['status' => 'livree'] + $payload)->assertSessionHasNoErrors();
        $this->put("/admin/deliveries/{$legacy->id}", ['status' => 'planifiee'] + $payload)->assertSessionHasErrors('status'); // pas de retour arrière
        $this->get('/admin/deliveries?status=livree')->assertSee($legacy->reference);
        $this->get("/admin/deliveries/{$legacy->id}")->assertOk();
        $this->delete("/admin/distributors/{$d->id}")->assertSessionHas('error');
        $this->delete("/admin/deliveries/{$legacy->id}")->assertRedirect();
        $this->assertDatabaseCount('deliveries', 0);
    }

    public function test_inactive_distributor_cannot_be_assigned(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $d = $this->distributor(['actif' => false]);
        $commande = $this->commande(User::factory()->create(), 'confirmee');
        $this->post('/admin/deliveries', ['commande_id' => $commande->id, 'distributor_id' => $d->id, 'delivery_date' => now()->format('Y-m-d')])->assertSessionHasErrors('distributor_id');
    }

    public function test_admin_commande_crud(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $client = User::factory()->create();
        $product = $this->product();
        $payload = ['user_id' => $client->id, 'product_id' => $product->id, 'quantite' => 2, 'adresse_livraison' => 'Rue Z', 'telephone' => '20 000 001'];
        $this->post('/admin/commandes', ['quantite' => 5000] + $payload)->assertSessionHasErrors('quantite');
        $this->post('/admin/commandes', $payload)->assertRedirect();
        $c = Commande::firstOrFail();
        $this->put("/admin/commandes/{$c->id}", ['quantite' => 7] + $payload)->assertSessionHasNoErrors();
        $this->assertSame(7, $c->fresh()->quantite);
        $this->get("/admin/commandes?q={$c->reference}")->assertSee($c->reference);
        $this->get("/admin/commandes/{$c->id}")->assertOk();
        $this->delete("/admin/commandes/{$c->id}")->assertRedirect();
        $this->assertDatabaseCount('commandes', 0);
    }

    public function test_no_public_distribution_pages(): void
    {
        $d = $this->distributor();
        $this->get('/distributeurs')->assertNotFound();
        $this->get("/distributeurs/{$d->id}")->assertNotFound();
        $this->get('/livraisons')->assertNotFound();
        $this->get('/')->assertDontSee('>Distributeurs<', false)->assertDontSee('>Livraisons<', false);
    }

    public function test_demo_seeder_is_idempotent_and_keeps_legacy_deliveries(): void
    {
        $this->seed();
        $this->seed();
        $this->assertDatabaseCount('deliveries', 9);
        $this->assertDatabaseCount('commandes', 4);
        $driver = User::where('username', 'distributeur_demo')->firstOrFail();
        $this->actingAs($driver, 'web')->get('/espace-distributeur/livraisons')->assertOk();
    }

    private function gemini(string $text): void
    {
        config(['nutrition_ai.key' => 'test-key']);
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => $text]]]]]])]);
    }

    private function sentMails(): array
    {
        return app('mail.manager')->mailer()->getSymfonyTransport()->messages()->all();
    }

    public function test_ai_assistant_fills_order_form(): void
    {
        $product = $this->product();
        $this->post('/commandes/assistant', ['texte' => 'x'])->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(), 'web');
        $this->gemini(json_encode(['product_id' => $product->id, 'quantite' => 5, 'adresse' => 'Rue Z, Tunis']));
        $this->postJson('/commandes/assistant', ['texte' => '5 tomates rue Z'])->assertOk()->assertJson(['product_id' => $product->id, 'quantite' => 5, 'adresse' => 'Rue Z, Tunis']);
        $this->gemini(json_encode(['product_id' => 0, 'quantite' => 1, 'adresse' => '']));
        $this->postJson('/commandes/assistant', ['texte' => 'des voitures'])->assertStatus(422);
        $this->postJson('/commandes/assistant', ['texte' => ''])->assertStatus(422);
        config(['nutrition_ai.key' => null]);
        $this->postJson('/commandes/assistant', ['texte' => '5 tomates'])->assertStatus(422); // IA non configurée : le formulaire reste utilisable
    }

    public function test_client_gets_ai_written_emails_with_fallback(): void
    {
        $client = User::factory()->create(['email' => 'client@t.test']);
        $driverUser = User::factory()->create();
        $d = $this->distributor(['user_id' => $driverUser->id]);
        $commande = $this->commande($client);

        $this->gemini('Bonjour, votre commande est confirmée !');
        $this->actingAs($this->admin(), 'admin')->patch("/admin/commandes/{$commande->id}/confirmer");
        $mails = $this->sentMails();
        $this->assertCount(1, $mails);
        $this->assertStringContainsString('confirmée', $mails[0]->getOriginalMessage()->getSubject());
        $this->assertStringContainsString('Bonjour, votre commande est confirmée !', $mails[0]->getOriginalMessage()->getTextBody());

        // IA indisponible : texte standard, l'action n'échoue pas.
        config(['nutrition_ai.key' => null]);
        $delivery = $d->deliveries()->create(['commande_id' => $commande->id, 'destination' => 'X', 'delivery_date' => now(), 'status' => 'planifiee']);
        $this->actingAs($driverUser, 'web')->patch("/espace-distributeur/livraisons/{$delivery->id}/statut")->assertSessionHas('success');
        $mails = $this->sentMails();
        $this->assertCount(2, $mails);
        $this->assertStringContainsString('en cours de livraison', $mails[1]->getOriginalMessage()->getSubject());
        $this->assertStringContainsString($commande->reference, $mails[1]->getOriginalMessage()->getTextBody());
    }
}
