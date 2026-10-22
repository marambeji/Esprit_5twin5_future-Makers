@extends('admin.layouts.layout')
@section('title', 'Transformateurs — NutriTrace')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3">Transformateurs</h1><a class="btn btn-primary" href="{{ route('admin.transformateurs.create') }}">Ajouter un transformateur</a></div>

<div class="card mb-4"><div class="card-body">
    <form id="filter-form" method="GET" action="{{ route('admin.transformateurs.index') }}" class="row gx-3 gy-2 align-items-center">
        <div class="col-sm-4"><input type="text" class="form-control" name="q" placeholder="Nom ou e-mail..." value="{{ request('q') }}" oninput="clearTimeout(this.timer); this.timer = setTimeout(() => document.getElementById('filter-form').submit(), 500);"></div>
        <div class="col-sm-3"><select name="type_activite" class="form-select" onchange="this.form.submit()"><option value="">Toutes les activités</option>@foreach (\App\Models\Transformateur::TYPES as $key => $label)<option value="{{ $key }}" @selected(request('type_activite') === $key)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-sm-3"><select name="ville" class="form-select" onchange="this.form.submit()"><option value="">Toutes les villes</option>@foreach ($villes as $ville)<option value="{{ $ville }}" @selected(request('ville') === $ville)>{{ $ville }}</option>@endforeach</select></div>
        <div class="col-auto"><a href="{{ route('admin.transformateurs.index') }}" class="btn btn-outline-secondary">Réinitialiser</a></div>
    </form>
</div></div>

<div class="card"><div class="card-body"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Nom</th><th>Activité</th><th>Ville</th><th>Transformations</th><th>CO₂ total</th><th>Actions</th></tr></thead><tbody>
@forelse ($transformateurs as $transformateur)
<tr>
    <td><a href="{{ route('admin.transformateurs.show', $transformateur) }}">{{ $transformateur->nom }}</a><div class="small text-muted">{{ $transformateur->email }}</div></td>
    <td>{{ $transformateur->type_label }}</td>
    <td>{{ $transformateur->ville }}</td>
    <td>{{ $transformateur->transformations_count }}</td>
    <td>{{ $transformateur->transformations_sum_co2_kg !== null ? number_format($transformateur->transformations_sum_co2_kg, 1, ',', ' ').' kg' : '—' }}</td>
    <td class="d-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.transformateurs.edit', $transformateur) }}">Modifier</a><form method="POST" action="{{ route('admin.transformateurs.destroy', $transformateur) }}" onsubmit="return confirm('Supprimer ce transformateur et ses {{ $transformateur->transformations_count }} transformation(s) ?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Supprimer</button></form></td>
</tr>
@empty <tr><td colspan="6">Aucun transformateur. Créez votre premier transformateur.</td></tr> @endforelse
</tbody></table></div>{{ $transformateurs->links() }}</div></div>
@endsection
