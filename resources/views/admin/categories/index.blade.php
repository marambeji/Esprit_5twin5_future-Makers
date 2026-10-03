@extends('admin.layouts.layout')
@section('title', 'Catégories — NutriTrace')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3">Catégories</h1><a class="btn btn-primary" href="{{ route('admin.categories.create') }}">Ajouter une catégorie</a></div>
<div class="card"><div class="card-body"><div class="table-responsive"><table class="table"><thead><tr><th>Nom</th><th>Produits</th><th>Actions</th></tr></thead><tbody>
@forelse ($categories as $category)
<tr><td><a href="{{ route('admin.categories.show', $category) }}">{{ $category->name }}</a></td><td>{{ $category->products_count }}</td><td class="d-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.categories.edit', $category) }}">Modifier</a><form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Supprimer cette catégorie ? Les catégories contenant des produits sont conservées.');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Supprimer</button></form></td></tr>
@empty <tr><td colspan="3">Aucune catégorie. Créez votre première catégorie.</td></tr> @endforelse
</tbody></table></div>{{ $categories->links() }}</div></div>
@endsection
