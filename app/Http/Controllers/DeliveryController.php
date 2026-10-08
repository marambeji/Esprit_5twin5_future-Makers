<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeliveryRequest;
use App\Models\Delivery;
use App\Models\Distributor;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $deliveries = Delivery::with('distributor')->search($request->query('q'), $request->query('status'))->latest('delivery_date')->latest('id')->paginate(10)->withQueryString();

        return view('admin.deliveries.index', compact('deliveries'));
    }

    public function create(Request $request)
    {
        return view('admin.deliveries.form', ['delivery' => new Delivery(['status' => 'planifiee', 'distributor_id' => $request->query('distributor_id')]), 'distributors' => Distributor::orderBy('name')->get()]);
    }

    public function store(DeliveryRequest $request)
    {
        $delivery = Delivery::create($request->validated());

        return to_route('admin.deliveries.show', $delivery)->with('success', 'Livraison créée : '.$delivery->reference.'.');
    }

    public function show(Delivery $delivery)
    {
        return view('admin.deliveries.show', ['delivery' => $delivery->load('distributor')]);
    }

    public function edit(Delivery $delivery)
    {
        return view('admin.deliveries.form', ['delivery' => $delivery, 'distributors' => Distributor::orderBy('name')->get()]);
    }

    public function update(DeliveryRequest $request, Delivery $delivery)
    {
        $delivery->update($request->validated());

        return to_route('admin.deliveries.show', $delivery)->with('success', 'Livraison modifiée.');
    }

    public function destroy(Delivery $delivery)
    {
        $delivery->delete();

        return to_route('admin.deliveries.index')->with('success', 'Livraison supprimée.');
    }
}
