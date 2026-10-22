<?php

namespace Tests\Feature;

use App\Models\Ferme;
use App\Models\Producteur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgriculturalListTest extends TestCase
{
    use RefreshDatabase;

    public function test_producer_search_matches_related_farm_name_and_location_with_quality_badges(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $included = Producteur::factory()->create(['nom' => 'Ben Salah', 'prenom' => 'Ahmed']);
        $excluded = Producteur::factory()->create(['nom' => 'Trabelsi', 'prenom' => 'Fatma']);
        Ferme::factory()->create(['producteur_id' => $included->id, 'nom' => 'Domaine Vert', 'localisation' => 'Béja, Tunisie']);
        Ferme::factory()->create(['producteur_id' => $excluded->id, 'localisation' => 'Sfax, Tunisie']);

        $this->actingAs($admin, 'admin')->get(route('admin.producteurs.index', ['q' => 'Béja']))
            ->assertOk()
            ->assertSee('Ben Salah')
            ->assertDontSee('Trabelsi')
            ->assertSee('Qualité')
            ->assertViewHas('producteurs', fn ($producteurs) => $producteurs->first()->data_quality['score'] === 100);
    }

    public function test_farm_list_combines_filters_searches_producer_and_sorts_safely(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $producer = Producteur::factory()->create(['nom' => 'Mansour', 'prenom' => 'Leila', 'email' => 'leila@example.test']);
        $first = Ferme::factory()->create(['producteur_id' => $producer->id, 'nom' => 'Ferme moyenne', 'superficie' => 20]);
        $second = Ferme::factory()->create(['producteur_id' => $producer->id, 'nom' => 'Grande ferme', 'superficie' => 60]);
        Ferme::factory()->create(['nom' => 'Ferme exclue', 'superficie' => 5]);

        $this->actingAs($admin, 'admin')->get(route('admin.fermes.index', [
            'q' => 'leila@example.test',
            'producteur_id' => $producer->id,
            'superficie_min' => 10,
            'superficie_max' => 80,
            'sort' => 'superficie',
            'direction' => 'desc',
        ]))->assertOk()
            ->assertSeeInOrder([$second->nom, $first->nom])
            ->assertDontSee('Ferme exclue')
            ->assertViewHas('fermes', fn ($fermes) => $fermes->count() === 2 && $fermes->first()->is($second));
    }

    public function test_unapproved_sort_field_falls_back_to_name_and_guest_is_redirected(): void
    {
        $this->get(route('admin.producteurs.index'))->assertRedirect(route('admin.login'));

        $admin = User::factory()->create(['role' => 'admin']);
        Ferme::factory()->create(['nom' => 'Zeta ferme']);
        Ferme::factory()->create(['nom' => 'Alpha ferme']);

        $this->actingAs($admin, 'admin')->get(route('admin.fermes.index', ['sort' => 'created_at; drop table fermes']))
            ->assertOk()
            ->assertSeeInOrder(['Alpha ferme', 'Zeta ferme']);
    }
}
