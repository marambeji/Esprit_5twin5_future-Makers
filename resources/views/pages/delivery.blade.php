@extends('layouts.layout')
@section('title', $delivery->reference . ' — NutriTrace')
@section('body_class', 'main-layout inner_page')
@section('content')
<section class="container py-5">
    <a class="text-success d-inline-block mb-4" href="{{ route('deliveries.index') }}">← Retour aux livraisons</a>
    <span class="badge badge-success mb-3">{{ $delivery->status_label }}</span>
    <h1 class="h2 mb-4">Livraison {{ $delivery->reference }}</h1>
    <div class="border-top border-bottom py-3 mb-4">
        <p class="mb-2"><strong>Distributeur :</strong> <a class="text-success" href="{{ route('distributors.show', $delivery->distributor) }}">{{ $delivery->distributor->name }}</a></p>
        <p class="mb-2"><strong>Destination :</strong> {{ $delivery->destination }}</p>
        <p class="mb-0"><strong>Date de livraison :</strong> {{ $delivery->delivery_date->format('d/m/Y') }}</p>
    </div>
    @if ($delivery->notes)<p style="white-space:pre-line;overflow-wrap:anywhere">{{ $delivery->notes }}</p>@endif
</section>
@endsection
