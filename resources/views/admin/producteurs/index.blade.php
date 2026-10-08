@extends('admin.layouts.layout')
@section('title', 'Producteurs — NutriTrace')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3">Producteurs</h1><a class="btn btn-primary" href="{{ route('admin.producteurs.create') }}">Ajouter un producteur</a></div>

<div class="card mb-4">
    <div class="card-body">
        <form id="filter-form" method="GET" action="{{ route('admin.producteurs.index') }}" class="row gx-3 gy-2 align-items-center">
            <div class="col-sm-6">
                <input type="text" class="form-control" name="q" placeholder="Recherche par nom, prénom, email..." value="{{ request('q') }}" oninput="clearTimeout(this.timer); this.timer = setTimeout(() => { document.getElementById('filter-form').submit(); }, 500);">
            </div>
            <div class="col-sm-4">
                <select name="fermes_count" class="form-select" onchange="document.getElementById('filter-form').submit();">
                    <option value="">Tous (nb de fermes)</option>
                    <option value="0" @selected(request('fermes_count') === '0')>0 ferme</option>
                    <option value="1-3" @selected(request('fermes_count') === '1-3')>1 à 3 fermes</option>
                    <option value="4+" @selected(request('fermes_count') === '4+')>4 fermes et plus</option>
                </select>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.producteurs.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

<div class="card"><div class="card-body"><div class="table-responsive"><table class="table"><thead><tr><th>Nom</th><th>Prénom</th><th>Email</th><th>Téléphone</th><th>Fermes</th><th>Actions</th></tr></thead><tbody>
@forelse ($producteurs as $producteur)
<tr><td><a href="{{ route('admin.producteurs.show', $producteur) }}">{{ $producteur->nom }}</a></td><td>{{ $producteur->prenom }}</td><td>{{ $producteur->email }}</td><td>{{ $producteur->telephone ?? '—' }}</td><td>{{ $producteur->fermes_count }}</td><td class="d-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.producteurs.edit', $producteur) }}">Modifier</a><form method="POST" action="{{ route('admin.producteurs.destroy', $producteur) }}" onsubmit="return confirm('Supprimer ce producteur ? Les producteurs possédant des fermes sont conservés.');"> @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Supprimer</button></form></td></tr>
@empty <tr><td colspan="6">Aucun producteur. Créez votre premier producteur.</td></tr> @endforelse
</tbody></table></div>{{ $producteurs->links() }}</div></div>
@endsection
