@extends('admin.layouts.layout')
@section('title', 'Commandes — NutriTrace')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3">Commandes</h1><a class="btn btn-primary" href="{{ route('admin.commandes.create') }}">Ajouter une commande</a></div>
<div class="card"><div class="card-body">
<form method="GET" class="d-flex gap-2 mb-3"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Rechercher (référence, client, produit)" aria-label="Recherche"><select name="statut" class="form-select w-auto" aria-label="Statut"><option value="">Tous les statuts</option>@foreach (\App\Models\Commande::STATUSES as $key => $label)<option value="{{ $key }}" @selected(request('statut') === $key)>{{ $label }}</option>@endforeach</select><button class="btn btn-outline-primary">Rechercher</button>@if (request()->hasAny(['q', 'statut']))<a class="btn btn-light" href="{{ route('admin.commandes.index') }}">Réinitialiser</a>@endif</form>
<div class="table-responsive"><table class="table"><thead><tr><th>Référence</th><th>Client</th><th>Produit</th><th>Qté</th><th>Statut</th><th>Livraison</th><th>Actions</th></tr></thead><tbody>
@forelse ($commandes as $commande)
<tr><td><a href="{{ route('admin.commandes.show', $commande) }}">{{ $commande->reference }}</a></td><td>{{ $commande->user->name }}</td><td>{{ $commande->product->name }}</td><td>{{ $commande->quantite }}</td><td>{{ $commande->statut_label }}</td><td>{{ $commande->delivery?->status_label ?? '—' }}</td>
<td class="d-flex gap-2">
@if ($commande->isPending())
<form method="POST" action="{{ route('admin.commandes.confirm', $commande) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-success">Confirmer</button></form>
<a class="btn btn-sm btn-outline-primary" href="{{ route('admin.commandes.edit', $commande) }}">Modifier</a>
@elseif ($commande->statut === 'confirmee' && ! $commande->delivery)
<a class="btn btn-sm btn-primary" href="{{ route('admin.deliveries.create', ['commande_id' => $commande->id]) }}">Livrer</a>
@endif
<form method="POST" action="{{ route('admin.commandes.destroy', $commande) }}" onsubmit="return confirm('Supprimer cette commande ?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Supprimer</button></form></td></tr>
@empty <tr><td colspan="7">Aucune commande trouvée.</td></tr> @endforelse
</tbody></table></div>{{ $commandes->links() }}</div></div>
@endsection
