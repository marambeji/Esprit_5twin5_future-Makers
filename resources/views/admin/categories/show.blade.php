@extends('admin.layouts.layout')
@section('title', $category->name . ' — NutriTrace')
@section('content')
<h1 class="h3">{{ $category->name }}</h1><div class="card"><div class="card-body"><p style="white-space: pre-line">{{ $category->description ?: 'Aucune description.' }}</p><a class="btn btn-primary" href="{{ route('admin.categories.edit', $category) }}">Modifier</a><a class="btn btn-light" href="{{ route('admin.categories.index') }}">Retour</a></div></div>
<h2 class="h4">Produits de cette catégorie</h2><div class="card"><div class="card-body"><ul class="list-group mb-3">@forelse ($products as $product)<li class="list-group-item"><a href="{{ route('admin.products.show', $product) }}">{{ $product->name }}</a> — {{ $product->price }} TND</li>@empty<li class="list-group-item">Aucun produit associé.</li>@endforelse</ul>{{ $products->links() }}</div></div>
@endsection
