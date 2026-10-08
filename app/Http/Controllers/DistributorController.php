<?php

namespace App\Http\Controllers;

use App\Http\Requests\DistributorRequest;
use App\Models\Distributor;
use Illuminate\Http\Request;

class DistributorController extends Controller
{
    public function index(Request $request)
    {
        $distributors = Distributor::withCount('deliveries')->search($request->query('q'))->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.distributors.index', compact('distributors'));
    }

    public function create()
    {
        return view('admin.distributors.form', ['distributor' => new Distributor]);
    }

    public function store(DistributorRequest $request)
    {
        $distributor = Distributor::create($request->validated());

        return to_route('admin.distributors.show', $distributor)->with('success', 'Distributeur créé.');
    }

    public function show(Distributor $distributor)
    {
        return view('admin.distributors.show', ['distributor' => $distributor, 'deliveries' => $distributor->deliveries()->latest('delivery_date')->paginate(10)]);
    }

    public function edit(Distributor $distributor)
    {
        return view('admin.distributors.form', compact('distributor'));
    }

    public function update(DistributorRequest $request, Distributor $distributor)
    {
        $distributor->update($request->validated());

        return to_route('admin.distributors.show', $distributor)->with('success', 'Distributeur modifié.');
    }

    public function destroy(Distributor $distributor)
    {
        if ($distributor->deliveries()->exists()) {
            return back()->with('error', 'Ce distributeur a des livraisons. Supprimez-les avant de supprimer le distributeur.');
        }
        $distributor->delete();

        return to_route('admin.distributors.index')->with('success', 'Distributeur supprimé.');
    }
}
