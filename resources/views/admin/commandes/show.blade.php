@extends('admin.layouts.layout')
@section('title', $commande->reference.' — NutriTrace')
@section('content')
<h1 class="h3">Commande {{ $commande->reference }}</h1>
<div class="card"><div class="card-body">
<p class="mb-1"><strong>Client :</strong> {{ $commande->user->name }} ({{ $commande->user->email }})</p>
<p class="mb-1"><strong>Produit :</strong> {{ $commande->product->name }} × {{ $commande->quantite }}</p>
<p class="mb-1"><strong>Adresse :</strong> {{ $commande->adresse_livraison }}</p>
<p class="mb-1"><strong>Téléphone :</strong> {{ $commande->telephone }}</p>
<p class="mb-1"><strong>Statut :</strong> {{ $commande->statut_label }}@if ($commande->motif_refus) — motif : {{ $commande->motif_refus }}@endif</p>
<p class="mb-3"><strong>Livraison :</strong> @if ($commande->delivery)<a href="{{ route('admin.deliveries.show', $commande->delivery) }}">{{ $commande->delivery->reference }}</a> ({{ $commande->delivery->status_label }}, {{ $commande->delivery->distributor->name }})@else — @endif</p>
@if ($commande->isPending())
<div class="d-flex gap-2 mb-3">
<form method="POST" action="{{ route('admin.commandes.confirm', $commande) }}">@csrf @method('PATCH')<button class="btn btn-success">Confirmer</button></form>
<a class="btn btn-outline-primary" href="{{ route('admin.commandes.edit', $commande) }}">Modifier</a>
</div>
<form method="POST" action="{{ route('admin.commandes.refuse', $commande) }}" class="mb-3">@csrf @method('PATCH')
<label for="motif_refus" class="form-label">Motif du refus *</label><input id="motif_refus" name="motif_refus" maxlength="500" class="form-control mb-2 @error('motif_refus') is-invalid @enderror" required>@error('motif_refus')<div class="invalid-feedback">{{ $message }}</div>@enderror
<button class="btn btn-outline-danger">Refuser la commande</button></form>
@elseif ($commande->statut === 'confirmee' && ! $commande->delivery)
<a class="btn btn-primary mb-3" href="{{ route('admin.deliveries.create', ['commande_id' => $commande->id]) }}">Créer la livraison</a>
@endif
<a class="btn btn-light" href="{{ route('admin.commandes.index') }}">Retour</a>
</div></div>
@endsection
