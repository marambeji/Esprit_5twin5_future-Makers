<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use App\Models\Label;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminCertificationController extends Controller
{
    public function index()
    {
        $certifications = Certification::with('label')->latest()->paginate(10);

        return view('admin.certifications.index', compact('certifications'));
    }

    public function create()
    {
        $certification = new Certification();
        if (request()->filled('label_id')) {
            $certification->label_id = (int) request('label_id');
        }
        $labels = Label::orderBy('nom')->get();

        return view('admin.certifications.form', compact('certification', 'labels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'label_id' => 'required|exists:labels,id',
            'nom' => 'required|string|max:255',
            'numero_certificat' => 'required|string|max:255|unique:certifications,numero_certificat',
            'date_obtention' => 'required|date',
            'date_expiration' => 'nullable|date|after_or_equal:date_obtention',
            'statut' => 'required|in:valide,expiree,suspendue',
            'description' => 'nullable|string',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:15360',
        ], [
            'document.mimes' => 'Le document doit être une image (JPG, PNG, WEBP) ou un fichier PDF.',
            'document.max' => 'La taille du document ne peut pas dépasser 15 Mo.',
        ]);

        $data = $request->except(['document']);

        if ($request->hasFile('document')) {
            $data['document_path'] = $request->file('document')->store('certifications', 'public');
        }

        Certification::create($data);

        return redirect()
            ->route('admin.certifications.index')
            ->with('success', 'Certification ajoutée avec succès.');
    }

    public function show(Certification $certification)
    {
        $certification->load('label');

        return view('admin.certifications.show', compact('certification'));
    }

    public function edit(Certification $certification)
    {
        $labels = Label::orderBy('nom')->get();

        return view('admin.certifications.form', compact('certification', 'labels'));
    }

    public function update(Request $request, Certification $certification)
    {
        $request->validate([
            'label_id' => 'required|exists:labels,id',
            'nom' => 'required|string|max:255',
            'numero_certificat' => 'required|string|max:255|unique:certifications,numero_certificat,' . $certification->id,
            'date_obtention' => 'required|date',
            'date_expiration' => 'nullable|date|after_or_equal:date_obtention',
            'statut' => 'required|in:valide,expiree,suspendue',
            'description' => 'nullable|string',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:15360',
        ], [
            'document.mimes' => 'Le document doit être une image (JPG, PNG, WEBP) ou un fichier PDF.',
            'document.max' => 'La taille du document ne peut pas dépasser 15 Mo.',
        ]);

        $data = $request->except(['document', 'remove_document']);

        if ($request->boolean('remove_document')) {
            if ($certification->document_path && Storage::disk('public')->exists($certification->document_path)) {
                Storage::disk('public')->delete($certification->document_path);
            }
            $data['document_path'] = null;
        } elseif ($request->hasFile('document')) {
            if ($certification->document_path && Storage::disk('public')->exists($certification->document_path)) {
                Storage::disk('public')->delete($certification->document_path);
            }
            $data['document_path'] = $request->file('document')->store('certifications', 'public');
        }

        $certification->update($data);

        return redirect()
            ->route('admin.certifications.index')
            ->with('success', 'Certification modifiée avec succès.');
    }

    public function destroy(Certification $certification)
    {
        if ($certification->document_path && Storage::disk('public')->exists($certification->document_path)) {
            Storage::disk('public')->delete($certification->document_path);
        }

        $certification->delete();

        return redirect()
            ->route('admin.certifications.index')
            ->with('success', 'Certification supprimée avec succès.');
    }
}
