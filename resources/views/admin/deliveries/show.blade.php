@extends('admin.layouts.layout')
@section('title', $delivery->reference . ' — NutriTrace')
@section('content')
<h1 class="h3">Livraison {{ $delivery->reference }}</h1>
<div class="card"><div class="card-body"><p class="mb-1"><strong>Distributeur :</strong> <a href="{{ route('admin.distributors.show', $delivery->distributor) }}">{{ $delivery->distributor->name }}</a></p><p class="mb-1"><strong>Destination :</strong> {{ $delivery->destination }}</p><p class="mb-1"><strong>Date :</strong> {{ $delivery->delivery_date->format('d/m/Y') }}</p><p class="mb-3"><strong>Statut :</strong> {{ $delivery->status_label }}</p><p style="white-space: pre-line">{{ $delivery->notes ?: 'Aucune note.' }}</p><a class="btn btn-primary" href="{{ route('admin.deliveries.edit', $delivery) }}">Modifier</a><a class="btn btn-light" href="{{ route('admin.deliveries.index') }}">Retour</a></div></div>
@endsection
