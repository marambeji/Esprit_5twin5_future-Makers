<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\Distributor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_admin_can_login(): void
    {
        $admin = $this->admin();
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'secret-pass-1'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_admin_area_requires_admin(): void
    {
        $this->get('/admin/distributors')->assertRedirect();
        $this->actingAs(User::factory()->create(['role' => 'user']), 'web')->get('/admin/distributors')->assertRedirect();
    }

    public function test_distributor_crud_and_search(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $this->post('/admin/distributors', ['name' => ''])->assertSessionHasErrors(['name', 'email', 'phone', 'city', 'address']);
        $this->post('/admin/distributors', ['name' => 'Agro Tunis', 'email' => 'a@t.test', 'phone' => '71 123 456', 'city' => 'Tunis', 'address' => '1 rue X'])->assertRedirect();
        $d = Distributor::firstOrFail();
        $this->put("/admin/distributors/{$d->id}", ['name' => 'Agro Bizerte', 'email' => 'a@t.test', 'phone' => '71 123 456', 'city' => 'Bizerte', 'address' => '1 rue X'])->assertSessionHasNoErrors();
        $this->get('/admin/distributors?q=Bizerte')->assertSee('Agro Bizerte');
        $this->get('/admin/distributors?q=zzz')->assertDontSee('Agro Bizerte');
        $this->get("/admin/distributors/{$d->id}")->assertOk();
        $this->delete("/admin/distributors/{$d->id}")->assertRedirect();
        $this->assertDatabaseCount('distributors', 0);
    }

    public function test_delivery_crud_auto_reference_and_relation(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $d = $this->distributor();
        $payload = ['distributor_id' => $d->id, 'destination' => 'Marché', 'delivery_date' => '2026-11-01', 'status' => 'planifiee'];
        $this->post('/admin/deliveries', ['status' => 'x'] + $payload)->assertSessionHasErrors('status');
        $this->post('/admin/deliveries', $payload)->assertRedirect();
        $this->post('/admin/deliveries', $payload);
        $refs = Delivery::orderBy('id')->pluck('reference')->all();
        $this->assertSame('LIV-'.now()->format('Ymd').'-0001', $refs[0]);
        $this->assertSame('LIV-'.now()->format('Ymd').'-0002', $refs[1]);
        $this->assertCount(2, $d->deliveries);
        $first = Delivery::first();
        $this->put("/admin/deliveries/{$first->id}", ['status' => 'livree'] + $payload)->assertSessionHasNoErrors();
        $this->assertSame('livree', $first->fresh()->status);
        $this->get('/admin/deliveries?status=livree')->assertSee($first->reference)->assertDontSee($refs[1]);
        $this->delete("/admin/distributors/{$d->id}")->assertSessionHas('error');
        $this->delete("/admin/deliveries/{$first->id}")->assertRedirect();
        $this->assertDatabaseCount('deliveries', 1);
    }

    public function test_front_is_read_only(): void
    {
        $d = $this->distributor();
        $l = $d->deliveries()->create(['destination' => 'Marché', 'delivery_date' => '2026-11-01', 'status' => 'livree']);
        $this->get('/distributeurs?q=Tunis')->assertOk()->assertSee('Agro Tunis');
        $this->get("/distributeurs/{$d->id}")->assertOk()->assertSee($l->reference);
        $this->get('/livraisons?q='.$l->reference)->assertOk()->assertSee($l->reference);
        $this->get("/livraisons/{$l->id}")->assertOk();
        $this->get('/')->assertSee('Distributeurs')->assertSee('Livraisons');
        $this->delete("/distributeurs/{$d->id}")->assertStatus(405);
        $this->post('/livraisons', [])->assertStatus(405);
    }
}
