@extends('admin.layouts.layout')
@section('title', 'Livraison — NutriTrace')
@section('content')
<h1 class="h3 mb-4">{{ $delivery->exists ? 'Modifier la livraison '.$delivery->reference : 'Ajouter une livraison' }}</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ $delivery->exists ? route('admin.deliveries.update', $delivery) : route('admin.deliveries.store') }}">
@csrf @if ($delivery->exists) @method('PUT') @endif
@unless ($delivery->exists)<p class="text-muted">La référence est générée automatiquement à l’enregistrement.</p>@endunless
<div class="mb-3"><label for="distributor_id" class="form-label">Distributeur *</label><select id="distributor_id" name="distributor_id" class="form-select @error('distributor_id') is-invalid @enderror" required><option value="">Choisir…</option>@foreach ($distributors as $distributor)<option value="{{ $distributor->id }}" @selected(old('distributor_id', $delivery->distributor_id) == $distributor->id)>{{ $distributor->name }}</option>@endforeach</select>@error('distributor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label for="destination" class="form-label">Destination *</label><input id="destination" name="destination" class="form-control @error('destination') is-invalid @enderror" value="{{ old('destination', $delivery->destination) }}" required maxlength="255">@error('destination')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label for="delivery_date" class="form-label">Date de livraison *</label><input id="delivery_date" name="delivery_date" type="date" class="form-control @error('delivery_date') is-invalid @enderror" value="{{ old('delivery_date', $delivery->delivery_date?->format('Y-m-d')) }}" required>@error('delivery_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label for="status" class="form-label">Statut *</label><select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>@foreach (\App\Models\Delivery::STATUSES as $key => $label)<option value="{{ $key }}" @selected(old('status', $delivery->status) === $key)>{{ $label }}</option>@endforeach</select>@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label for="notes" class="form-label">Notes</label><textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="4" maxlength="2000">{{ old('notes', $delivery->notes) }}</textarea>@error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<button class="btn btn-primary">Enregistrer</button><a class="btn btn-light" href="{{ route('admin.deliveries.index') }}">Annuler</a>
</form></div></div>
@endsection
