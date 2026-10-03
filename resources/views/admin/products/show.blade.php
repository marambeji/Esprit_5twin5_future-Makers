@extends('admin.layouts.layout')
@section('title', $product->name . ' — NutriTrace')
@section('content')
<img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="width:100%;max-width:600px;height:300px;object-fit:contain;background:#f6f7f1;border-radius:12px;margin-bottom:24px">
@if (!$product->image_path)<p class="small text-muted">Photo illustrative du produit.</p>@endif
<h1 class="h3">{{ $product->name }}</h1><div class="card"><div class="card-body"><dl><dt>Catégorie</dt><dd><a href="{{ route('admin.categories.show', $product->category) }}">{{ $product->category->name }}</a></dd><dt>Description</dt><dd style="white-space: pre-line">{{ $product->description }}</dd><dt>Origine</dt><dd>{{ $product->origin }}</dd><dt>Prix</dt><dd>{{ $product->price }} TND</dd></dl><a class="btn btn-primary" href="{{ route('admin.products.edit', $product) }}">Modifier</a><a class="btn btn-outline-primary" href="{{ route('catalog.show', $product) }}">Voir la fiche publique</a><a class="btn btn-light" href="{{ route('admin.products.index') }}">Retour</a></div></div>
@endsection
