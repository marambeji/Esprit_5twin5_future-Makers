<?php

namespace App\Http\Controllers;

use App\Http\Requests\DistributorRequest;
use App\Models\Distributor;
use App\Models\User;
use Illuminate\Http\Request;

class DistributorController extends Controller
{
    public function index(Request $request)
    {
        $distributors = Distributor::withCount(['deliveries', 'avis'])->withAvg('avis', 'note')->search($request->query('q'))->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.distributors.index', compact('distributors'));
    }

    public function create()
    {
        $distributor = new Distributor(['actif' => true]);

        return view('admin.distributors.form', ['distributor' => $distributor, 'users' => $this->users($distributor)]);
    }

    public function store(DistributorRequest $request)
    {
        $distributor = Distributor::create($request->distributorData());

        return to_route('admin.distributors.show', $distributor)->with('success', 'Distributeur créé.');
    }

    public function show(Distributor $distributor)
    {
        return view('admin.distributors.show', ['distributor' => $distributor->load('user')->loadCount('avis')->loadAvg('avis', 'note'), 'negativeReviews' => $distributor->avis()->with('delivery')->where('sentiment', 'negatif')->latest('avis_livraisons.created_at')->paginate(10, ['avis_livraisons.*'], 'avis_page'), 'deliveries' => $distributor->deliveries()->latest('delivery_date')->paginate(10)]);
    }

    public function edit(Distributor $distributor)
    {
        return view('admin.distributors.form', ['distributor' => $distributor, 'users' => $this->users($distributor)]);
    }

    public function update(DistributorRequest $request, Distributor $distributor)
    {
        $distributor->update($request->distributorData());

        return to_route('admin.distributors.show', $distributor)->with('success', 'Distributeur modifié.');
    }

    public function destroy(Distributor $distributor)
    {
        if ($distributor->deliveries()->exists()) {
            return back()->with('error', 'Ce distributeur a des livraisons : désactivez-le plutôt que de le supprimer.');
        }
        $distributor->delete();

        return to_route('admin.distributors.index')->with('success', 'Distributeur supprimé.');
    }

    /** Comptes utilisateurs disponibles : pas déjà liés à un autre distributeur. */
    private function users(Distributor $distributor)
    {
        $taken = Distributor::whereNotNull('user_id')->where('id', '!=', $distributor->id ?? 0)->pluck('user_id');

        return User::where('role', 'user')->whereNotIn('id', $taken)->orderBy('username')->get(['id', 'name', 'username', 'email']);
    }
}
