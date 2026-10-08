@extends('admin.layouts.layout')
@section('title', 'Livraisons — NutriTrace')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3">Livraisons</h1><a class="btn btn-primary" href="{{ route('admin.deliveries.create') }}">Ajouter une livraison</a></div>
<div class="card"><div class="card-body">
<form method="GET" class="d-flex gap-2 mb-3"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Rechercher (référence, destination, distributeur)" aria-label="Recherche"><select name="status" class="form-select w-auto" aria-label="Statut"><option value="">Tous les statuts</option>@foreach (\App\Models\Delivery::STATUSES as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select><button class="btn btn-outline-primary">Rechercher</button>@if (request()->hasAny(['q', 'status']))<a class="btn btn-light" href="{{ route('admin.deliveries.index') }}">Réinitialiser</a>@endif</form>
<div class="table-responsive"><table class="table"><thead><tr><th>Référence</th><th>Distributeur</th><th>Destination</th><th>Date</th><th>Statut</th><th>Actions</th></tr></thead><tbody>
@forelse ($deliveries as $delivery)
<tr><td><a href="{{ route('admin.deliveries.show', $delivery) }}">{{ $delivery->reference }}</a></td><td>{{ $delivery->distributor->name }}</td><td>{{ $delivery->destination }}</td><td>{{ $delivery->delivery_date->format('d/m/Y') }}</td><td>{{ $delivery->status_label }}</td><td class="d-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.deliveries.edit', $delivery) }}">Modifier</a><form method="POST" action="{{ route('admin.deliveries.destroy', $delivery) }}" onsubmit="return confirm('Supprimer cette livraison ?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Supprimer</button></form></td></tr>
@empty <tr><td colspan="6">Aucune livraison trouvée.</td></tr> @endforelse
</tbody></table></div>{{ $deliveries->links() }}</div></div>
@endsection
