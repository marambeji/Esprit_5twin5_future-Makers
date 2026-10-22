<?php

namespace Tests\Feature;

use App\Models\Certification;
use App\Models\Label;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificationQrCodeTest extends TestCase
{
    use RefreshDatabase;

    private Certification $certification;

    protected function setUp(): void
    {
        parent::setUp();

        $label = Label::create([
            'nom' => 'Label Ecologique Test',
            'organisme' => 'Organisme Test'
        ]);

        $this->certification = Certification::create([
            'label_id' => $label->id,
            'nom' => 'Certification Bio Test',
            'numero_certificat' => 'CERT-QR-2026-99',
            'date_obtention' => '2026-01-01',
            'statut' => 'valide',
            'description' => 'Certification de test avec QR code.'
        ]);
    }

    public function test_certification_model_generates_qr_code_data_uri(): void
    {
        $qrCode = $this->certification->qr_code;
        $qrText = $this->certification->qr_code_text;

        $this->assertStringContainsString('CERTIFICAT OFFICIEL NUTRITRACE', $qrText);
        $this->assertStringContainsString($this->certification->numero_certificat, $qrText);
        $this->assertStringContainsString('VALIDE', $qrText);
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $qrCode);
        $this->assertNotEmpty($this->certification->qr_code_svg);
        $this->assertStringContainsString('<svg', $this->certification->qr_code_svg);
    }

    public function test_front_certifications_index_displays_qr_codes(): void
    {
        $response = $this->get(route('certifications.index'));

        $response->assertOk()
            ->assertSee('Certification Bio Test')
            ->assertSee('QR Code traçabilité')
            ->assertSee('data:image/svg+xml;base64,', false);
    }

    public function test_front_certification_show_displays_prominent_qr_code_block(): void
    {
        $response = $this->get(route('certifications.show', $this->certification));

        $response->assertOk()
            ->assertSee('QR Code Officiel')
            ->assertSee('Vérification de traçabilité en direct')
            ->assertSee($this->certification->numero_certificat)
            ->assertSee('data:image/svg+xml;base64,', false)
            ->assertSee(route('certifications.qrcode', $this->certification));
    }

    public function test_certification_qr_code_svg_can_be_downloaded(): void
    {
        $response = $this->get(route('certifications.qrcode', $this->certification));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertStringContainsString('<svg', $response->getContent());
    }

    public function test_admin_can_upload_photo_certificate_and_front_displays_it(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        $photo = \Illuminate\Http\UploadedFile::fake()->create('certificat_ab.jpg', 500, 'image/jpeg');

        $response = $this->actingAs($admin, 'admin')->post(route('admin.certifications.store'), [
            'label_id' => $this->certification->label_id,
            'nom' => 'Certificat avec Photo',
            'numero_certificat' => 'CERT-IMG-001',
            'date_obtention' => '2026-02-01',
            'statut' => 'valide',
            'document' => $photo,
        ]);

        $response->assertRedirect(route('admin.certifications.index'));

        $cert = Certification::where('numero_certificat', 'CERT-IMG-001')->first();
        $this->assertNotNull($cert);
        $this->assertTrue($cert->has_document);
        $this->assertTrue($cert->is_image);
        $this->assertFalse($cert->is_pdf);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($cert->document_path);

        // Test display on front (when phone scans QR code)
        $frontResponse = $this->get(route('certifications.show', $cert));
        $frontResponse->assertOk()
            ->assertSee('Document Officiel du Certificat (Photo)')
            ->assertSee($cert->document_url, false)
            ->assertSee('Télécharger la photo originale');

        // Test document route
        $docResponse = $this->get(route('certifications.document', $cert));
        $docResponse->assertOk();
    }

    public function test_admin_can_upload_pdf_certificate_and_front_displays_it(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        $pdf = \Illuminate\Http\UploadedFile::fake()->create('diplome_certificat.pdf', 500, 'application/pdf');

        $response = $this->actingAs($admin, 'admin')->post(route('admin.certifications.store'), [
            'label_id' => $this->certification->label_id,
            'nom' => 'Certificat avec PDF',
            'numero_certificat' => 'CERT-PDF-001',
            'date_obtention' => '2026-03-01',
            'statut' => 'valide',
            'document' => $pdf,
        ]);

        $response->assertRedirect(route('admin.certifications.index'));

        $cert = Certification::where('numero_certificat', 'CERT-PDF-001')->first();
        $this->assertNotNull($cert);
        $this->assertTrue($cert->has_document);
        $this->assertTrue($cert->is_pdf);
        $this->assertFalse($cert->is_image);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($cert->document_path);

        // Test display on front (when phone scans QR code)
        $frontResponse = $this->get(route('certifications.show', $cert));
        $frontResponse->assertOk()
            ->assertSee('Document Officiel du Certificat (PDF)')
            ->assertSee('Consulter le certificat PDF')
            ->assertSee($cert->document_url, false);
    }
}
