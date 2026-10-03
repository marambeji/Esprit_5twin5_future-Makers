@extends('admin.layouts.layout')
@section('title', 'Produits — NutriTrace')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3">Produits alimentaires</h1><a class="btn btn-primary" href="{{ route('admin.products.create') }}">Ajouter un produit</a></div>
<div class="card"><div class="card-body"><div class="table-responsive"><table class="table"><thead><tr><th>Nom</th><th>Catégorie</th><th>Origine</th><th>Prix</th><th>Actions</th></tr></thead><tbody>
@forelse ($products as $product)
<tr><td><a href="{{ route('admin.products.show', $product) }}">{{ $product->name }}</a></td><td><a href="{{ route('admin.categories.show', $product->category) }}">{{ $product->category->name }}</a></td><td>{{ $product->origin }}</td><td>{{ $product->price }} TND</td><td class="d-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.products.edit', $product) }}">Modifier</a><form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Supprimer ce produit ? Cette action est définitive.');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Supprimer</button></form></td></tr>
@empty<tr><td colspan="5">Aucun produit enregistré.</td></tr>@endforelse
</tbody></table></div>{{ $products->links() }}</div></div>
@endsection
