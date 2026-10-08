<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Distributor;
use Illuminate\Http\Request;

// Consultation publique (lecture seule) des distributeurs et livraisons.
class DistributionController extends Controller
{
    public function distributors(Request $request)
    {
        return view('pages.distributors', ['distributors' => Distributor::withCount('deliveries')->search($request->query('q'))->orderBy('name')->paginate(9)->withQueryString()]);
    }

    public function distributor(Distributor $distributor)
    {
        return view('pages.distributor', ['distributor' => $distributor, 'deliveries' => $distributor->deliveries()->latest('delivery_date')->paginate(10)]);
    }

    public function deliveries(Request $request)
    {
        return view('pages.deliveries', ['deliveries' => Delivery::with('distributor')->search($request->query('q'), $request->query('status'))->latest('delivery_date')->latest('id')->paginate(9)->withQueryString()]);
    }

    public function delivery(Delivery $delivery)
    {
        return view('pages.delivery', ['delivery' => $delivery->load('distributor')]);
    }
}
