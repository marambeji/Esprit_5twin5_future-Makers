@extends('admin.layouts.layout')
@section('title', 'Distributeurs — NutriTrace')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3">Distributeurs</h1><a class="btn btn-primary" href="{{ route('admin.distributors.create') }}">Ajouter un distributeur</a></div>
<div class="card"><div class="card-body">
<form method="GET" class="d-flex gap-2 mb-3"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Rechercher (nom, ville, e-mail)" aria-label="Recherche"><button class="btn btn-outline-primary">Rechercher</button>@if (request('q'))<a class="btn btn-light" href="{{ route('admin.distributors.index') }}">Réinitialiser</a>@endif</form>
<div class="table-responsive"><table class="table"><thead><tr><th>Nom</th><th>Ville</th><th>Téléphone</th><th>Livraisons</th><th>Actions</th></tr></thead><tbody>
@forelse ($distributors as $distributor)
<tr><td><a href="{{ route('admin.distributors.show', $distributor) }}">{{ $distributor->name }}</a></td><td>{{ $distributor->city }}</td><td>{{ $distributor->phone }}</td><td>{{ $distributor->deliveries_count }}</td><td class="d-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.distributors.edit', $distributor) }}">Modifier</a><form method="POST" action="{{ route('admin.distributors.destroy', $distributor) }}" onsubmit="return confirm('Supprimer ce distributeur ?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Supprimer</button></form></td></tr>
@empty <tr><td colspan="5">Aucun distributeur trouvé.</td></tr> @endforelse
</tbody></table></div>{{ $distributors->links() }}</div></div>
@endsection
