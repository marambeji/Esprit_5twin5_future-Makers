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
}
