@extends('admin.layouts.layout')
@section('title', 'Commande — NutriTrace')
@section('content')
<h1 class="h3 mb-4">{{ $commande->exists ? 'Modifier la commande '.$commande->reference : 'Ajouter une commande' }}</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ $commande->exists ? route('admin.commandes.update', $commande) : route('admin.commandes.store') }}">
@csrf @if ($commande->exists) @method('PUT') @endif
@unless ($commande->exists)
<div class="mb-3"><label for="user_id" class="form-label">Client *</label><select id="user_id" name="user_id" class="form-select @error('user_id') is-invalid @enderror" required><option value="">Choisir…</option>@foreach ($users as $user)<option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>{{ $user->name }} ({{ $user->username }})</option>@endforeach</select>@error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
@endunless
<div class="mb-3"><label for="product_id" class="form-label">Produit *</label><select id="product_id" name="product_id" class="form-select @error('product_id') is-invalid @enderror" required><option value="">Choisir…</option>@foreach ($products as $product)<option value="{{ $product->id }}" @selected(old('product_id', $commande->product_id) == $product->id)>{{ $product->name }}</option>@endforeach</select>@error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label for="quantite" class="form-label">Quantité *</label><input id="quantite" name="quantite" type="number" min="1" max="1000" class="form-control @error('quantite') is-invalid @enderror" value="{{ old('quantite', $commande->quantite ?? 1) }}" required>@error('quantite')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label for="adresse_livraison" class="form-label">Adresse de livraison *</label><input id="adresse_livraison" name="adresse_livraison" maxlength="255" class="form-control @error('adresse_livraison') is-invalid @enderror" value="{{ old('adresse_livraison', $commande->adresse_livraison) }}" required>@error('adresse_livraison')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label for="telephone" class="form-label">Téléphone *</label><input id="telephone" name="telephone" maxlength="30" class="form-control @error('telephone') is-invalid @enderror" value="{{ old('telephone', $commande->telephone) }}" required>@error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<button class="btn btn-primary">Enregistrer</button><a class="btn btn-light" href="{{ route('admin.commandes.index') }}">Annuler</a>
</form></div></div>
@endsection
