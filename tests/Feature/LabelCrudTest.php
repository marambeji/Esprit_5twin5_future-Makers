<?php

namespace Tests\Feature;

use App\Models\Certification;
use App\Models\Label;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabelCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_guest_cannot_access_label_routes(): void
    {
        $this->get(route('admin.labels.index'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_labels_index(): void
    {
        Label::create([
            'nom' => 'Label Ecologique',
            'organisme' => 'Ministère de la Transition',
            'description' => 'Garantit des normes écologiques strictes'
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.labels.index'));

        $response->assertOk()
            ->assertSee('Gestion des Labels')
            ->assertSee('Label Ecologique')
            ->assertSee('Ministère de la Transition');
    }

    public function test_admin_can_create_a_label(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.labels.store'), [
                'nom' => 'Label Rouge',
                'organisme' => 'INAO',
                'description' => 'Attestation de qualité supérieure',
            ]);

        $label = Label::where('nom', 'Label Rouge')->first();
        $this->assertNotNull($label);
        $this->assertSame('INAO', $label->organisme);

        $response->assertRedirect(route('admin.labels.show', $label));
    }

    public function test_label_validation_requires_unique_nom(): void
    {
        Label::create(['nom' => 'Bio Standard']);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.labels.store'), [
                'nom' => 'Bio Standard',
            ]);

        $response->assertSessionHasErrors('nom');
    }

    public function test_admin_can_update_a_label(): void
    {
        $label = Label::create([
            'nom' => 'Ancien Nom',
            'organisme' => 'Organisme A',
            'description' => 'Description initiale',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.labels.update', $label), [
                'nom' => 'Nouveau Nom',
                'organisme' => 'Organisme B',
                'description' => 'Description modifiée',
            ]);

        $response->assertRedirect(route('admin.labels.show', $label));

        $label->refresh();
        $this->assertSame('Nouveau Nom', $label->nom);
        $this->assertSame('Organisme B', $label->organisme);
        $this->assertSame('Description modifiée', $label->description);
    }

    public function test_admin_can_delete_a_label_without_certifications(): void
    {
        $label = Label::create(['nom' => 'Label Sans Certif']);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.labels.destroy', $label));

        $response->assertRedirect(route('admin.labels.index'));
        $this->assertDatabaseMissing('labels', ['id' => $label->id]);
    }

    public function test_admin_cannot_delete_a_label_with_associated_certifications(): void
    {
        $label = Label::create(['nom' => 'Label Avec Certif']);
        Certification::create([
            'label_id' => $label->id,
            'nom' => 'Certif Test',
            'numero_certificat' => 'CERT-TEST-001',
            'date_obtention' => '2026-01-01',
            'statut' => 'valide',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.labels.destroy', $label));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('labels', ['id' => $label->id]);
    }

    public function test_created_label_appears_in_certification_form_dropdown(): void
    {
        $label = Label::create([
            'nom' => 'Label Specifique Traçabilité',
            'organisme' => 'Organisme Test',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.certifications.create'));

        $response->assertOk()
            ->assertSee('Label Specifique Traçabilité')
            ->assertSee('Organisme Test')
            ->assertSee('value="' . $label->id . '"', false);
    }
}
