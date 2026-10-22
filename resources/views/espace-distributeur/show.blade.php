@extends('layouts.layout')
@section('title', $delivery->reference.' — NutriTrace')
@section('body_class', 'main-layout inner_page')
@section('content')
<section class="container py-5">
    <a class="text-success d-inline-block mb-4" href="{{ route('distributor-space.index') }}">← Mes livraisons</a>
    <h1 class="h2 mb-4">Livraison {{ $delivery->reference }}</h1>
    @include('partials.flash')
    <div class="border-top border-bottom py-3 mb-4">
        <p class="mb-2"><strong>Date prévue :</strong> {{ $delivery->delivery_date->format('d/m/Y') }}</p>
        <p class="mb-2"><strong>Statut :</strong> {{ $delivery->status_label }}</p>
        <p class="mb-2"><strong>Adresse de livraison :</strong> {{ $delivery->destination }}</p>
        @if ($delivery->commande)
            <p class="mb-2"><strong>Client :</strong> {{ $delivery->commande->user->name }} — {{ $delivery->commande->telephone }}</p>
            <p class="mb-2"><strong>Produit :</strong> {{ $delivery->commande->product->name }} × {{ $delivery->commande->quantite }}</p>
        @endif
        @if ($delivery->notes)<p class="mb-0" style="white-space:pre-line"><strong>Notes :</strong> {{ $delivery->notes }}</p>@endif
    </div>
    @if ($next = $delivery->nextStatus())
        <form method="POST" action="{{ route('distributor-space.status', $delivery) }}">@csrf @method('PATCH')<button class="btn btn-success">Passer à « {{ \App\Models\Delivery::STATUSES[$next] }} »</button></form>
    @else
        <p class="text-muted">Cette livraison est {{ strtolower($delivery->status_label) }} : aucun changement possible.</p>
    @endif
</section>
@endsection
