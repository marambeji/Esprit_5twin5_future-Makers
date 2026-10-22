<?php

namespace App\Http\Controllers;

use App\Models\Certification;

class CertificationController extends Controller
{
    /**
     * Affichage public de la liste des certifications (Frontoffice).
     */
    public function index()
    {
        $certifications = Certification::with('label')->latest()->paginate(9);

        return view('certifications.index', compact('certifications'));
    }

    /**
     * Affichage public du détail d'une certification (Frontoffice).
     */
    public function show(Certification $certification)
    {
        $certification->load('label');

        return view('certifications.show', compact('certification'));
    }

    /**
     * Téléchargement du QR Code officiel de la certification (format SVG vectoriel).
     */
    public function qrcode(Certification $certification)
    {
        $filename = 'qrcode-' . \Illuminate\Support\Str::slug($certification->numero_certificat) . '.svg';

        return response($certification->qr_code_svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Affichage direct du document original attaché (Photo ou PDF).
     */
    public function document(Certification $certification)
    {
        if (!$certification->has_document) {
            return redirect()->route('certifications.show', $certification)
                ->with('info', 'Aucun document physique n\'est attaché à cette certification.');
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->response($certification->document_path);
    }
}
