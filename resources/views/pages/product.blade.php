@extends('layouts.layout')
@section('title', $product->name . ' — NutriTrace')
@section('body_class', 'main-layout inner_page')
@section('content')
<section class="container py-5">
    <a class="text-success d-inline-block mb-4" href="{{ route('catalog.index') }}">← Retour au catalogue</a>
    <div class="row align-items-start">
        <div class="col-lg-6 mb-4">
            <figure class="m-0">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="display:block;width:100%;aspect-ratio:4/3;object-fit:contain;background:#f6f7f1;border-radius:12px">
                @if (!$product->image_path)<figcaption class="small text-muted mt-2">Photo illustrative du produit.</figcaption>@endif
            </figure>
            @if ($credit = $product->image_credit)
                <p class="small text-muted mt-2">Photo : {{ $credit['author'] }} — <a href="{{ $credit['source'] }}" target="_blank" rel="noopener">Wikimedia Commons</a> · <a href="{{ $credit['license_url'] ?: $credit['source'] }}" target="_blank" rel="noopener">{{ $credit['license'] === 'Public domain' ? 'Domaine public' : $credit['license'] }}</a></p>
            @endif
        </div>
        <div class="col-lg-6 pl-lg-4">
            <span class="badge badge-success mb-3">{{ $product->category->name }}</span>
            <h1 class="h2 mb-4">{{ $product->name }}</h1>
            <p class="mb-4" style="white-space:pre-line;overflow-wrap:anywhere">{{ $product->description }}</p>
            <div class="border-top border-bottom py-3 mb-4">
                <p class="mb-2"><strong>Origine :</strong> {{ $product->origin }}</p>
                <p><strong>Prix :</strong> {{ $product->price }} TND</p>
            </div>
            <a class="btn btn-success mb-2 mr-2" href="{{ route('catalog.index', ['category' => $product->category_id]) }}">Produits de cette catégorie</a>
            <a class="btn btn-outline-success mb-2" href="{{ route('catalog.index') }}">Tous les produits</a>
        </div>
    </div>
</section>
@endsection
