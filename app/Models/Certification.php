<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    use HasFactory;

    protected $fillable = [
        'label_id',
        'nom',
        'numero_certificat',
        'date_obtention',
        'date_expiration',
        'statut',
        'description',
        'document_path',
    ];

    public function label()
    {
        return $this->belongsTo(Label::class);
    }

    /**
     * Indique si un document justificatif (photo ou PDF) est attaché.
     */
    public function getHasDocumentAttribute(): bool
    {
        return !empty($this->document_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->document_path);
    }

    /**
     * URL publique d'accès direct au fichier du certificat (photo ou PDF).
     */
    public function getDocumentUrlAttribute(): ?string
    {
        return $this->has_document ? asset('storage/' . $this->document_path) : null;
    }

    /**
     * Indique si le document attaché est un fichier PDF.
     */
    public function getIsPdfAttribute(): bool
    {
        return $this->has_document && strtolower(pathinfo($this->document_path, PATHINFO_EXTENSION)) === 'pdf';
    }

    /**
     * Indique si le document attaché est une image (JPG, PNG, WEBP).
     */
    public function getIsImageAttribute(): bool
    {
        return $this->has_document && in_array(strtolower(pathinfo($this->document_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif']);
    }

    /**
     * Texte officiel autonome encodé dans le QR Code.
     * S'affiche instantanément sur l'écran du smartphone sans nécessiter de connexion ni de déploiement.
     */
    public function getQrCodeTextAttribute(): string
    {
        $labelNom = $this->label?->nom ?? 'Label Officiel';
        $organisme = $this->label?->organisme ? " ({$this->label->organisme})" : '';
        $statut = match ($this->statut) {
            'valide' => 'VALIDE ✅',
            'expiree' => 'EXPIRÉE ❌',
            'suspendue' => 'SUSPENDUE ⚠️',
            default => strtoupper($this->statut ?? 'VALIDE'),
        };
        $dateObtention = $this->date_obtention ? \Carbon\Carbon::parse($this->date_obtention)->format('d/m/Y') : 'N/A';
        $dateExpiration = $this->date_expiration ? \Carbon\Carbon::parse($this->date_expiration)->format('d/m/Y') : 'Indéterminée';
        $doc = $this->has_document
            ? ($this->is_pdf ? 'Document PDF officiel joint' : 'Photo officielle jointe')
            : 'Attestation numérique NutriTrace';

        $text = "📜 CERTIFICAT OFFICIEL NUTRITRACE\n";
        $text .= "------------------------------------\n";
        $text .= "Certification : {$this->nom}\n";
        $text .= "Label : {$labelNom}{$organisme}\n";
        $text .= "N° Certificat : {$this->numero_certificat}\n";
        $text .= "Statut : {$statut}\n";
        $text .= "Date obtention : {$dateObtention}\n";
        $text .= "Date expiration : {$dateExpiration}\n";
        $text .= "Document : {$doc}\n";
        if (!empty($this->description)) {
            $text .= "Observations : " . \Illuminate\Support\Str::limit($this->description, 90) . "\n";
        }
        $text .= "------------------------------------\n";
        $text .= "Traçabilité alimentaire garantie par NutriTrace";

        return $text;
    }

    /**
     * URL publique de vérification de la certification.
     */
    public function getVerificationUrlAttribute(): string
    {
        return route('certifications.show', $this);
    }

    /**
     * Génère le QR Code sous forme de Data URI (data:image/svg+xml;base64,...)
     * Prêt à être injecté directement dans une balise <img src="...">.
     */
    public function getQrCodeAttribute(): string
    {
        $options = new \chillerlan\QRCode\QROptions([
            'outputInterface' => \chillerlan\QRCode\Output\QRMarkupSVG::class,
            'outputBase64'    => true,
            'svgAddXmlHeader' => false,
            'scale'           => 6,
        ]);

        return (new \chillerlan\QRCode\QRCode($options))->render($this->qr_code_text);
    }

    /**
     * Génère le QR Code brut sous forme de chaîne SVG.
     */
    public function getQrCodeSvgAttribute(): string
    {
        $options = new \chillerlan\QRCode\QROptions([
            'outputInterface' => \chillerlan\QRCode\Output\QRMarkupSVG::class,
            'outputBase64'    => false,
            'svgAddXmlHeader' => false,
        ]);

        return (new \chillerlan\QRCode\QRCode($options))->render($this->qr_code_text);
    }
}
