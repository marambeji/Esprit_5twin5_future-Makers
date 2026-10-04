<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use App\Models\Label;
use Illuminate\Http\Request;

class CertificationController extends Controller
{
    public function index()
    {
        $certifications = Certification::with('label')->get();

        return view('certifications.index', compact('certifications'));
    }

    public function create()
    {
        $labels = Label::all();

        return view('certifications.create', compact('labels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'label_id' => 'required|exists:labels,id',
            'nom' => 'required|string|max:255',
            'numero_certificat' => 'required|string|unique:certifications,numero_certificat',
            'date_obtention' => 'required|date',
            'date_expiration' => 'nullable|date|after_or_equal:date_obtention',
            'statut' => 'required|in:valide,expiree,suspendue',
            'description' => 'nullable|string'
        ]);

        Certification::create($request->all());

        return redirect()
            ->route('certifications.index')
            ->with('success', 'Certification ajoutée avec succès.');
    }

    public function show(Certification $certification)
    {
        $certification->load('label');
        return view('certifications.show', compact('certification'));
    }

    public function edit(Certification $certification)
    {
        $labels = Label::all();

        return view('certifications.edit', compact('certification', 'labels'));
    }

    public function update(Request $request, Certification $certification)
    {
        $request->validate([
            'label_id' => 'required|exists:labels,id',
            'nom' => 'required|string|max:255',
            'numero_certificat' => 'required|string|unique:certifications,numero_certificat,' . $certification->id,
            'date_obtention' => 'required|date',
            'date_expiration' => 'nullable|date|after_or_equal:date_obtention',
            'statut' => 'required|in:valide,expiree,suspendue',
            'description' => 'nullable|string'
        ]);

        $certification->update($request->all());

        return redirect()
            ->route('certifications.index')
            ->with('success', 'Certification modifiée avec succès.');
    }

    public function destroy(Certification $certification)
    {
        $certification->delete();

        return redirect()
            ->route('certifications.index')
            ->with('success', 'Certification supprimée.');
    }
}
