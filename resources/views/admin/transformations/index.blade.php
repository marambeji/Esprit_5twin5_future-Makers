@extends('admin.layouts.layout')
@section('title', 'Transformations — NutriTrace')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3">Transformations</h1><a class="btn btn-primary" href="{{ route('admin.transformations.create') }}">Ajouter une transformation</a></div>

<div class="card mb-4"><div class="card-body">
    <form id="filter-form" method="GET" action="{{ route('admin.transformations.index') }}" class="row gx-3 gy-2 align-items-center">
        <div class="col-sm-2"><input type="text" class="form-control" name="q" placeholder="Référence..." value="{{ request('q') }}" oninput="clearTimeout(this.timer); this.timer = setTimeout(() => document.getElementById('filter-form').submit(), 500);"></div>
        <div class="col-sm-3"><select name="transformateur_id" class="form-select" onchange="this.form.submit()"><option value="">Tous les transformateurs</option>@foreach ($transformateurs as $tr)<option value="{{ $tr->id }}" @selected(request('transformateur_id') == $tr->id)>{{ $tr->nom }}</option>@endforeach</select></div>
        <div class="col-sm-2"><select name="product_id" class="form-select" onchange="this.form.submit()"><option value="">Tous les produits</option>@foreach ($products as $p)<option value="{{ $p->id }}" @selected(request('product_id') == $p->id)>{{ $p->name }}</option>@endforeach</select></div>
        <div class="col-sm-2"><select name="procede" class="form-select" onchange="this.form.submit()"><option value="">Tous les procédés</option>@foreach (\App\Models\Transformation::PROCEDES as $key => $p)<option value="{{ $key }}" @selected(request('procede') === $key)>{{ $p['label'] }}</option>@endforeach</select></div>
        <div class="col-sm-2"><select name="eco_score" class="form-select" onchange="this.form.submit()"><option value="">Tous les éco-scores</option>@foreach (\App\Models\Transformation::SCORES as $s)<option value="{{ $s }}" @selected(request('eco_score') === $s)>Éco-score {{ $s }}</option>@endforeach<option value="aucun" @selected(request('eco_score') === 'aucun')>Non renseigné</option></select></div>
        <div class="col-auto"><a href="{{ route('admin.transformations.index') }}" class="btn btn-outline-secondary">Réinitialiser</a></div>
    </form>
</div></div>

<div class="card"><div class="card-body"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Référence</th><th>Date</th><th>Transformateur</th><th>Produit</th><th>Procédé</th><th>Quantité</th><th>CO₂</th><th>Éco-score</th><th>Actions</th></tr></thead><tbody>
@forelse ($transformations as $t)
<tr>
    <td><a href="{{ route('admin.transformations.show', $t) }}">{{ $t->reference }}</a>@if ($t->verdict_allegation && $t->verdict_allegation !== 'fiable' && $t->verdict_allegation !== 'non_evaluee')<span class="badge bg-danger ms-1" title="{{ \App\Models\Transformation::VERDICTS[$t->verdict_allegation] }}">Greenwashing</span>@endif</td>
    <td>{{ $t->date_transformation->format('d/m/Y') }}</td>
    <td><a href="{{ route('admin.transformateurs.show', $t->transformateur) }}">{{ $t->transformateur->nom }}</a></td>
    <td>{{ $t->product->name }}</td>
    <td>{{ $t->procede_label }}</td>
    <td>{{ number_format($t->quantite_kg, 0, ',', ' ') }} kg</td>
    <td>{{ $t->co2_kg !== null ? number_format($t->co2_kg, 1, ',', ' ').' kg' : '—' }}</td>
    <td><x-eco-score :score="$t->eco_score" size="sm" /></td>
    <td class="d-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.transformations.edit', $t) }}">Modifier</a><form method="POST" action="{{ route('admin.transformations.destroy', $t) }}" onsubmit="return confirm('Supprimer cette transformation ?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Supprimer</button></form></td>
</tr>
@empty <tr><td colspan="9">Aucune transformation.</td></tr> @endforelse
</tbody></table></div>{{ $transformations->links() }}</div></div>
@endsection
