@extends('layouts.layout')
@section('title', $commande->reference.' — NutriTrace')
@section('body_class', 'main-layout inner_page')
@section('content')
<section class="container py-5">
    <a class="text-success d-inline-block mb-4" href="{{ route('commandes.index') }}">← Mes commandes</a>
    <h1 class="h2 mb-4">Commande {{ $commande->reference }}</h1>
    @include('partials.flash')
    <div class="border-top border-bottom py-3 mb-4">
        <p class="mb-2"><strong>Produit :</strong> {{ $commande->product->name }} × {{ $commande->quantite }}</p>
        <p class="mb-2"><strong>Adresse :</strong> {{ $commande->adresse_livraison }}</p>
        <p class="mb-2"><strong>Téléphone :</strong> {{ $commande->telephone }}</p>
        <p class="mb-2"><strong>Passée le :</strong> {{ $commande->created_at->format('d/m/Y H:i') }}</p>
        <p class="mb-0"><strong>Statut de la commande :</strong> {{ $commande->statut_label }}@if ($commande->statut === 'refusee' && $commande->motif_refus) — motif : {{ $commande->motif_refus }}@endif</p>
    </div>
    <h2 class="h4 mb-3">Suivi de livraison</h2>
    @if ($commande->delivery)
        <p class="mb-2"><strong>Référence :</strong> {{ $commande->delivery->reference }}</p>
        <p class="mb-2"><strong>Distributeur :</strong> {{ $commande->delivery->distributor->name }} ({{ $commande->delivery->distributor->phone }})</p>
        <p class="mb-2"><strong>Date prévue :</strong> {{ $commande->delivery->delivery_date->format('d/m/Y') }}</p>
        <p class="mb-3"><strong>Statut :</strong> {{ $commande->delivery->status_label }}</p>
    @else
        <p class="mb-3">{{ $commande->statut === 'confirmee' ? 'Votre commande est confirmée, la livraison sera bientôt planifiée.' : 'Aucune livraison pour le moment.' }}</p>
    @endif
    @include('commandes.partials.avis')
    @if ($commande->isPending())
        <form method="POST" action="{{ route('commandes.cancel', $commande) }}" onsubmit="return confirm('Annuler cette commande ?');">@csrf @method('PATCH')<button class="btn btn-outline-danger">Annuler la commande</button></form>
    @endif
</section>
@endsection
