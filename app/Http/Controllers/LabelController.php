<?php

namespace App\Http\Controllers;

use App\Http\Requests\LabelRequest;
use App\Models\Label;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    /**
     * Display a listing of labels.
     */
    public function index(Request $request)
    {
        $search = $request->query('q');

        $labels = Label::withCount('certifications')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('organisme', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('nom')
            ->paginate(10)
            ->withQueryString();

        return view('admin.labels.index', compact('labels', 'search'));
    }

    /**
     * Show the form for creating a new label.
     */
    public function create()
    {
        $label = new Label();

        return view('admin.labels.form', compact('label'));
    }

    /**
     * Store a newly created label in storage.
     */
    public function store(LabelRequest $request)
    {
        $label = Label::create($request->validated());

        return redirect()
            ->route('admin.labels.show', $label)
            ->with('success', 'Label créé avec succès.');
    }

    /**
     * Display the specified label along with its certifications.
     */
    public function show(Label $label)
    {
        $label->load(['certifications' => function ($query) {
            $query->latest();
        }]);

        return view('admin.labels.show', compact('label'));
    }

    /**
     * Show the form for editing the specified label.
     */
    public function edit(Label $label)
    {
        return view('admin.labels.form', compact('label'));
    }

    /**
     * Update the specified label in storage.
     */
    public function update(LabelRequest $request, Label $label)
    {
        $label->update($request->validated());

        return redirect()
            ->route('admin.labels.show', $label)
            ->with('success', 'Label mis à jour avec succès.');
    }

    /**
     * Remove the specified label from storage.
     */
    public function destroy(Label $label)
    {
        $certificationsCount = $label->certifications()->count();

        if ($certificationsCount > 0) {
            return back()->with('error', "Impossible de supprimer le label « {$label->nom} » car il est lié à {$certificationsCount} certification(s). Veuillez réaffecter ou supprimer ces certifications avant.");
        }

        $label->delete();

        return redirect()
            ->route('admin.labels.index')
            ->with('success', 'Label supprimé avec succès.');
    }
}
