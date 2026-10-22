@extends('admin.layouts.layout')
@section('title', 'Fermes — NutriTrace')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3">Fermes</h1><a class="btn btn-primary" href="{{ route('admin.fermes.create') }}">Ajouter une ferme</a></div>

<div class="card mb-4">
    <div class="card-body">
        <form id="filter-form" method="GET" action="{{ route('admin.fermes.index') }}" class="row gx-3 gy-2 align-items-center">
            <div class="col-sm-3">
                <input type="text" class="form-control" name="q" placeholder="Nom ou localisation..." value="{{ request('q') }}" oninput="clearTimeout(this.timer); this.timer = setTimeout(() => { document.getElementById('filter-form').submit(); }, 500);">
            </div>
            <div class="col-sm-3">
                <select name="producteur_id" class="form-select" onchange="document.getElementById('filter-form').submit();">
                    <option value="">Tous les producteurs</option>
                    @foreach($producteurs as $prod)
                        <option value="{{ $prod->id }}" @selected(request('producteur_id') == $prod->id)>{{ $prod->nom }} {{ $prod->prenom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <input type="number" step="0.01" class="form-control" name="superficie_min" placeholder="Superficie min" value="{{ request('superficie_min') }}" oninput="clearTimeout(this.timer); this.timer = setTimeout(() => { document.getElementById('filter-form').submit(); }, 800);">
            </div>
            <div class="col-sm-2">
                <input type="number" step="0.01" class="form-control" name="superficie_max" placeholder="Superficie max" value="{{ request('superficie_max') }}" oninput="clearTimeout(this.timer); this.timer = setTimeout(() => { document.getElementById('filter-form').submit(); }, 800);">
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.fermes.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

<div class="card"><div class="card-body"><div class="table-responsive"><table class="table"><thead><tr><th>Nom</th><th>Producteur</th><th>Localisation</th><th>Superficie (ha)</th><th>Actions</th></tr></thead><tbody>
@forelse ($fermes as $ferme)
<tr><td><a href="{{ route('admin.fermes.show', $ferme) }}">{{ $ferme->nom }}</a></td><td><a href="{{ route('admin.producteurs.show', $ferme->producteur) }}">{{ $ferme->producteur->nom }} {{ $ferme->producteur->prenom }}</a></td><td>{{ $ferme->localisation }}</td><td>{{ $ferme->superficie ?? '—' }}</td><td class="d-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.fermes.edit', $ferme) }}">Modifier</a><form method="POST" action="{{ route('admin.fermes.destroy', $ferme) }}" onsubmit="return confirm('Supprimer cette ferme ?');"> @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Supprimer</button></form></td></tr>
@empty <tr><td colspan="5">Aucune ferme. Créez votre première ferme.</td></tr> @endforelse
</tbody></table></div>{{ $fermes->links() }}</div></div>
@endsection
