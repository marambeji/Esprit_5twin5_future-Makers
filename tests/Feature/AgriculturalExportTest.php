<?php

namespace Tests\Feature;

use App\Models\Ferme;
use App\Models\Producteur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgriculturalExportTest extends TestCase
{
    use RefreshDatabase;

    private function parseCsv(string $content): array
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, substr($content, 3));
        rewind($stream);
        $rows = [];

        while (($row = fgetcsv($stream, null, ';', '"', '')) !== false) {
            $rows[] = $row;
        }

        fclose($stream);

        return $rows;
    }

    public function test_admin_can_export_filtered_producers_as_safe_utf8_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $included = Producteur::factory()->create([
            'nom' => '=Malik', 'prenom' => 'Éric', 'email' => 'malik@example.test',
            'telephone' => '+216 55 123 456', 'adresse' => "Rue \"Verte\"; numéro 2\nNabeul",
        ]);
        Producteur::factory()->create(['nom' => 'Exclu', 'prenom' => 'Test']);
        Ferme::factory()->create(['producteur_id' => $included->id, 'nom' => "Ferme; \"Alpha\"\nNord", 'localisation' => "Béja; Tunisie\nNord"]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.producteurs.export', ['q' => 'Malik']));

        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $rows = $this->parseCsv($content);
        $this->assertSame(['Nom', 'Prénom', 'E-mail', 'Téléphone', 'Adresse', 'Nombre de fermes', 'Fermes', 'Localisations des fermes'], $rows[0]);
        $this->assertCount(8, $rows[1]);
        $this->assertSame("'=Malik", $rows[1][0]);
        $this->assertSame('Éric', $rows[1][1]);
        $this->assertSame('+216 55 123 456', $rows[1][3]);
        $this->assertSame("Rue \"Verte\"; numéro 2\nNabeul", $rows[1][4]);
        $this->assertSame("Ferme; \"Alpha\"\nNord", $rows[1][6]);
        $this->assertSame("Béja; Tunisie\nNord", $rows[1][7]);
        $this->assertCount(2, $rows, 'The active search filter must exclude unrelated producers.');
    }

    public function test_admin_can_export_filtered_farms(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $producer = Producteur::factory()->create(['nom' => 'Ben Salah', 'prenom' => 'Ahmed', 'email' => 'ahmed@example.test', 'telephone' => '+216 55 000 111', 'adresse' => "Rue des Oliviers; 5\nNabeul"]);
        Ferme::factory()->create(['producteur_id' => $producer->id, 'nom' => 'Ferme Béja', 'localisation' => 'Béja, Tunisie', 'superficie' => 12, 'latitude' => 36.7, 'longitude' => 9.2, 'description' => "Première ligne; avec séparateur\nDeuxième ligne, \"bio\"."]);
        Ferme::factory()->create(['nom' => 'Ferme Sfax', 'superficie' => 2]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.fermes.export', ['producteur_id' => $producer->id, 'superficie_min' => 10]));

        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $rows = $this->parseCsv($response->streamedContent());
        $this->assertSame(['Ferme', 'Prénom du producteur', 'Nom du producteur', 'E-mail du producteur', 'Téléphone du producteur', 'Adresse du producteur', 'Localisation', 'Superficie (ha)', 'Latitude', 'Longitude', 'Description'], $rows[0]);
        $this->assertCount(11, $rows[1]);
        $this->assertSame('Ferme Béja', $rows[1][0]);
        $this->assertSame('Ahmed', $rows[1][1]);
        $this->assertSame('Ben Salah', $rows[1][2]);
        $this->assertSame('+216 55 000 111', $rows[1][4]);
        $this->assertSame("Rue des Oliviers; 5\nNabeul", $rows[1][5]);
        $this->assertSame('36.7000000', $rows[1][8]);
        $this->assertSame('9.2000000', $rows[1][9]);
        $this->assertSame("Première ligne; avec séparateur\nDeuxième ligne, \"bio\".", $rows[1][10]);
        $this->assertCount(2, $rows, 'Both active farm filters must be applied.');
    }

    public function test_farm_export_preserves_sorting_and_guards_formula_values_after_whitespace(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Ferme::factory()->create(['nom' => 'Alpha', 'description' => "\t=HYPERLINK(\"https://example.test\")"]);
        Ferme::factory()->create(['nom' => 'Zulu']);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.fermes.export', ['sort' => 'nom', 'direction' => 'desc']));
        $rows = $this->parseCsv($response->streamedContent());
        $this->assertSame('Zulu', $rows[1][0]);
        $this->assertSame('Alpha', $rows[2][0]);
        $this->assertSame("'\t=HYPERLINK(\"https://example.test\")", $rows[2][10]);
        $this->assertCount(count($rows[0]), $rows[1]);
        $this->assertCount(count($rows[0]), $rows[2]);
    }

    public function test_empty_export_still_has_utf8_headers(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $content = $this->actingAs($admin, 'admin')->get(route('admin.producteurs.export'))->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $rows = $this->parseCsv($content);
        $this->assertSame(['Nom', 'Prénom', 'E-mail', 'Téléphone', 'Adresse', 'Nombre de fermes', 'Fermes', 'Localisations des fermes'], $rows[0]);
        $this->assertCount(1, $rows);
    }

    public function test_guest_cannot_export_agricultural_data(): void
    {
        $this->get(route('admin.producteurs.export'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.fermes.export'))->assertRedirect(route('admin.login'));
    }
}
