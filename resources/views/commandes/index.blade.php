@extends('layouts.layout')
@section('title', 'Mes commandes — NutriTrace')
@section('body_class', 'main-layout inner_page')
@section('content')
<section class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h2 mb-0">Mes commandes</h1><a class="btn btn-success" href="{{ route('commandes.create') }}">Passer une commande</a></div>
    @include('partials.flash')
    <div class="table-responsive"><table class="table">
        <thead><tr><th>Référence</th><th>Produit</th><th>Quantité</th><th>Commande</th><th>Livraison</th><th></th></tr></thead>
        <tbody>
        @forelse ($commandes as $commande)
            <tr>
                <td>{{ $commande->reference }}</td>
                <td>{{ $commande->product->name }}</td>
                <td>{{ $commande->quantite }}</td>
                <td>{{ $commande->statut_label }}</td>
                <td>{{ $commande->delivery?->status_label ?? '—' }}</td>
                <td><a class="text-success" href="{{ route('commandes.show', $commande) }}">Suivre</a></td>
            </tr>
        @empty
            <tr><td colspan="6">Vous n’avez pas encore passé de commande.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $commandes->links('pagination::bootstrap-4') }}
</section>
@endsection
