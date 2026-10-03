@extends('layouts.layout')
@section('title', 'Catalogue — NutriTrace')
@section('body_class', 'main-layout inner_page')
@section('content')
<section class="container py-5"><div class="titlepage"><h1>Catalogue alimentaire</h1><p>Découvrez les produits et leur origine.</p></div>
<form method="GET" action="{{ route('catalog.index') }}" class="mb-4"><label for="category">Filtrer par catégorie</label><div class="d-flex"><select id="category" name="category" class="form-control mr-2"><option value="">Toutes les catégories</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>@endforeach</select><button class="btn btn-success">Filtrer</button></div>@error('category')<p class="text-danger">{{ $message }}</p>@enderror</form>
<div class="row">@forelse ($products as $product)<div class="col-md-4 mb-4"><article class="card h-100"><img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" style="width:100%;height:210px;object-fit:contain;background:#f6f7f1"><span class="small text-muted px-3 pt-2">{{ $product->image_path ? 'Photo du produit' : ($product->image_credit ? 'Photo illustrative du produit' : 'Photo à ajouter') }}</span><div class="card-body"><span class="badge badge-success">{{ $product->category->name }}</span><h2 class="h4 my-3">{{ $product->name }}</h2><p class="mb-2">Origine : {{ $product->origin }}</p><p class="mb-3">{{ $product->price }} TND</p><a class="btn btn-outline-success" href="{{ route('catalog.show', $product) }}">Voir le produit</a></div></article></div>@empty<div class="col-12"><p>Aucun produit dans cette sélection.</p></div>@endforelse</div>{{ $products->links('pagination::bootstrap-4') }}
</section>
@endsection
