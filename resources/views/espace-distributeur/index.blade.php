@extends('layouts.layout')
@section('title', 'Mes livraisons — NutriTrace')
@section('body_class', 'main-layout inner_page')
@section('content')
<section class="container py-5">
    <h1 class="h2 mb-4">Mes livraisons</h1>
    @include('partials.flash')
    <div class="table-responsive"><table class="table">
        <thead><tr><th>Référence</th><th>Produit</th><th>Destination</th><th>Date</th><th>Statut</th><th></th></tr></thead>
        <tbody>
        @forelse ($deliveries as $delivery)
            <tr>
                <td>{{ $delivery->reference }}</td>
                <td>{{ $delivery->commande ? $delivery->commande->product->name.' × '.$delivery->commande->quantite : '—' }}</td>
                <td>{{ $delivery->destination }}</td>
                <td>{{ $delivery->delivery_date->format('d/m/Y') }}</td>
                <td>{{ $delivery->status_label }}</td>
                <td><a class="text-success" href="{{ route('distributor-space.show', $delivery) }}">Détails</a></td>
            </tr>
        @empty
            <tr><td colspan="6">Aucune livraison ne vous est affectée.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $deliveries->links('pagination::bootstrap-4') }}
</section>
@endsection
