@extends('admin.layouts.layout')
@section('title', $distributor->name . ' — NutriTrace')
@section('content')
<h1 class="h3">{{ $distributor->name }}</h1>
<div class="card"><div class="card-body"><p class="mb-1"><strong>E-mail :</strong> {{ $distributor->email }}</p><p class="mb-1"><strong>Téléphone :</strong> {{ $distributor->phone }}</p><p class="mb-3"><strong>Adresse :</strong> {{ $distributor->address }}, {{ $distributor->city }}</p><p style="white-space: pre-line">{{ $distributor->description ?: 'Aucune description.' }}</p><a class="btn btn-primary" href="{{ route('admin.distributors.edit', $distributor) }}">Modifier</a><a class="btn btn-light" href="{{ route('admin.distributors.index') }}">Retour</a></div></div>
<div class="d-flex justify-content-between align-items-center mb-2"><h2 class="h4 mb-0">Livraisons</h2><a class="btn btn-sm btn-primary" href="{{ route('admin.deliveries.create', ['distributor_id' => $distributor->id]) }}">Ajouter une livraison</a></div>
<div class="card"><div class="card-body"><ul class="list-group mb-3">@forelse ($deliveries as $delivery)<li class="list-group-item"><a href="{{ route('admin.deliveries.show', $delivery) }}">{{ $delivery->reference }}</a> — {{ $delivery->delivery_date->format('d/m/Y') }} — {{ $delivery->status_label }}</li>@empty<li class="list-group-item">Aucune livraison.</li>@endforelse</ul>{{ $deliveries->links() }}</div></div>
@endsection
