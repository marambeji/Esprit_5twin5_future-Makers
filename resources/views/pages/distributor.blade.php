@extends('layouts.layout')
@section('title', $distributor->name . ' — NutriTrace')
@section('body_class', 'main-layout inner_page')
@section('content')
<section class="container py-5">
    <a class="text-success d-inline-block mb-4" href="{{ route('distributors.index') }}">← Retour aux distributeurs</a>
    <h1 class="h2 mb-4">{{ $distributor->name }}</h1>
    <div class="border-top border-bottom py-3 mb-4">
        <p class="mb-2"><strong>Adresse :</strong> {{ $distributor->address }}, {{ $distributor->city }}</p>
        <p class="mb-2"><strong>Téléphone :</strong> {{ $distributor->phone }}</p>
        <p class="mb-0"><strong>E-mail :</strong> {{ $distributor->email }}</p>
    </div>
    @if ($distributor->description)<p class="mb-4" style="white-space:pre-line;overflow-wrap:anywhere">{{ $distributor->description }}</p>@endif
    <h2 class="h4 mb-3">Livraisons</h2>
    <ul class="list-group mb-3">@forelse ($deliveries as $delivery)<li class="list-group-item"><a class="text-success" href="{{ route('deliveries.show', $delivery) }}">{{ $delivery->reference }}</a> — {{ $delivery->delivery_date->format('d/m/Y') }} — {{ $delivery->status_label }}</li>@empty<li class="list-group-item">Aucune livraison.</li>@endforelse</ul>
    {{ $deliveries->links('pagination::bootstrap-4') }}
</section>
@endsection
