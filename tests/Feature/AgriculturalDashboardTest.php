<?php

namespace Tests\Feature;

use App\Models\Ferme;
use App\Models\Producteur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgriculturalDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_agricultural_statistics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $firstProducer = Producteur::factory()->create(['nom' => 'Ben Salah', 'prenom' => 'Ahmed']);
        $secondProducer = Producteur::factory()->create(['nom' => 'Trabelsi', 'prenom' => 'Fatma']);
        $producerWithoutFarm = Producteur::factory()->create(['nom' => 'Mansour', 'prenom' => 'Leila']);

        Ferme::factory()->create(['producteur_id' => $firstProducer->id, 'localisation' => 'Béja, Tunisie', 'superficie' => 10]);
        Ferme::factory()->create(['producteur_id' => $firstProducer->id, 'localisation' => 'Béja, Tunisie', 'superficie' => 5]);
        Ferme::factory()->create(['producteur_id' => $secondProducer->id, 'localisation' => 'Nabeul, Tunisie', 'superficie' => 20]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.agriculture.dashboard'))
            ->assertOk()
            ->assertSee('Tableau de bord agricole')
            ->assertViewHas('statistics', fn (array $statistics) => $statistics === [
                'total_producteurs' => 3,
                'total_fermes' => 3,
                'superficie_totale' => 35.0,
                'superficie_moyenne' => 11.67,
                'producteurs_sans_ferme' => 1,
            ])
            ->assertViewHas('fermesParRegion', fn ($regions) => $regions->contains(fn (array $region) => $region === ['region' => 'Béja', 'total_fermes' => 2]))
            ->assertSee('Ahmed Ben Salah')
            ->assertSee('Leila Mansour');
    }

    public function test_guest_cannot_access_agricultural_dashboard(): void
    {
        $this->get(route('admin.agriculture.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_cannot_access_agricultural_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user, 'admin')
            ->get(route('admin.agriculture.dashboard'))
            ->assertForbidden();
    }
}
