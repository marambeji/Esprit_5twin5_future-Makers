@extends('admin.layouts.layout')
@section('title', 'Livraison — NutriTrace')
@section('content')
<h1 class="h3 mb-4">{{ $delivery->exists ? 'Modifier la livraison '.$delivery->reference : 'Ajouter une livraison' }}</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ $delivery->exists ? route('admin.deliveries.update', $delivery) : route('admin.deliveries.store') }}">
@csrf @if ($delivery->exists) @method('PUT') @endif
@unless ($delivery->exists)<p class="text-muted">La référence est générée automatiquement. L’adresse est reprise de la commande.</p>@endunless
@if ($delivery->exists)
<p class="mb-3"><strong>Commande :</strong> {{ $delivery->commande?->reference ?? 'Aucune (ancienne livraison)' }}</p>
@else
<div class="mb-3"><label for="commande_id" class="form-label">Commande confirmée *</label><select id="commande_id" name="commande_id" class="form-select @error('commande_id') is-invalid @enderror" required><option value="">Choisir…</option>@foreach ($commandes as $commande)<option value="{{ $commande->id }}" @selected(old('commande_id', $delivery->commande_id) == $commande->id)>{{ $commande->reference }} — {{ $commande->user->name }} — {{ $commande->product->name }} × {{ $commande->quantite }}</option>@endforeach</select>@error('commande_id')<div class="invalid-feedback">{{ $message }}</div>@enderror @if ($commandes->isEmpty())<div class="form-text">Aucune commande confirmée en attente de livraison.</div>@endif</div>
@endif
<div class="mb-3"><label for="distributor_id" class="form-label">Distributeur *</label><select id="distributor_id" name="distributor_id" class="form-select @error('distributor_id') is-invalid @enderror" required><option value="">Choisir…</option>@foreach ($distributors as $distributor)<option value="{{ $distributor->id }}" @selected(old('distributor_id', $delivery->distributor_id) == $distributor->id)>{{ $distributor->name }}{{ $distributor->actif ? '' : ' (désactivé)' }}</option>@endforeach</select>@error('distributor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
@unless ($delivery->exists)<div class="mt-2"><button type="button" id="ia_suggest" class="btn btn-outline-primary btn-sm">Suggérer un distributeur (IA)</button> <span id="ia_raison" class="small text-muted" role="status"></span></div>@endunless</div>
@if ($delivery->exists && ! $delivery->commande_id)
<div class="mb-3"><label for="destination" class="form-label">Destination *</label><input id="destination" name="destination" class="form-control @error('destination') is-invalid @enderror" value="{{ old('destination', $delivery->destination) }}" required maxlength="255">@error('destination')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
@endif
<div class="mb-3"><label for="delivery_date" class="form-label">Date de livraison *</label><input id="delivery_date" name="delivery_date" type="date" @unless ($delivery->exists) min="{{ now()->format('Y-m-d') }}" @endunless class="form-control @error('delivery_date') is-invalid @enderror" value="{{ old('delivery_date', $delivery->delivery_date?->format('Y-m-d')) }}" required>@error('delivery_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
@if ($delivery->exists)
@php($allowed = in_array($delivery->status, ['livree', 'annulee'], true) ? [$delivery->status] : array_unique([$delivery->status, ...(\App\Models\Delivery::TRANSITIONS[$delivery->status] ?? []), 'annulee']))
<div class="mb-3"><label for="status" class="form-label">Statut *</label><select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>@foreach ($allowed as $key)<option value="{{ $key }}" @selected(old('status', $delivery->status) === $key)>{{ \App\Models\Delivery::STATUSES[$key] }}</option>@endforeach</select>@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
@endif
<div class="mb-3"><label for="notes" class="form-label">Notes</label><textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="4" maxlength="2000">{{ old('notes', $delivery->notes) }}</textarea>@error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<button class="btn btn-primary">Enregistrer</button><a class="btn btn-light" href="{{ route('admin.deliveries.index') }}">Annuler</a>
</form></div></div>
@unless ($delivery->exists)
<script>
document.getElementById('ia_suggest').addEventListener('click', async () => {
    const raison = document.getElementById('ia_raison'), btn = document.getElementById('ia_suggest'), commande = document.getElementById('commande_id').value;
    if (!commande) { raison.textContent = 'Choisissez d’abord une commande.'; return; }
    raison.textContent = 'Analyse en cours…'; btn.disabled = true;
    try {
        const r = await fetch('{{ route('admin.deliveries.suggest') }}', {method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}, body: JSON.stringify({commande_id: commande})});
        const d = await r.json();
        if (!r.ok) { raison.textContent = d.message || 'Suggestion indisponible.'; return; }
        document.getElementById('distributor_id').value = d.distributor_id;
        raison.textContent = (d.ia ? 'IA : ' : '') + d.raison;
    } catch (e) { raison.textContent = 'Suggestion indisponible.'; } finally { btn.disabled = false; }
});
</script>
@endunless
@endsection
