@extends('admin.layouts.layout')
@section('title', 'Producteur — NutriTrace')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
<h1 class="h3">{{ $producteur->nom }} {{ $producteur->prenom }}</h1>
<div class="d-flex gap-2"><a class="btn btn-outline-primary" href="{{ route('admin.producteurs.edit', $producteur) }}">Modifier</a><form method="POST" action="{{ route('admin.producteurs.destroy', $producteur) }}" onsubmit="return confirm('Supprimer ce producteur ?');"> @csrf @method('DELETE')<button class="btn btn-outline-danger">Supprimer</button></form></div>
</div>
<div class="card mb-4"><div class="card-body">
<dl class="row mb-0">
<dt class="col-sm-3">Nom complet</dt><dd class="col-sm-9">{{ $producteur->prenom }} {{ $producteur->nom }}</dd>
<dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $producteur->email }}</dd>
<dt class="col-sm-3">Téléphone</dt><dd class="col-sm-9">{{ $producteur->telephone ?? '—' }}</dd>
<dt class="col-sm-3">Adresse</dt><dd class="col-sm-9">{{ $producteur->adresse ?? '—' }}</dd>
</dl>
</div></div>
<div class="card mb-4"><div class="card-body">
<div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">Qualité de la fiche</h2><span class="badge bg-{{ $dataQuality['score'] === 100 ? 'success' : 'warning' }}">{{ $dataQuality['score'] }} % complétée</span></div>
@if ($dataQuality['missing'])<p class="mb-2"><strong>À compléter :</strong> {{ implode(', ', $dataQuality['missing']) }}.</p>@endif
@foreach ($dataQuality['recommendations'] as $recommendation)<p class="text-muted mb-1">{{ $recommendation }}</p>@endforeach
@if (! $dataQuality['missing'])<p class="text-success mb-0">Les informations facultatives de cette fiche sont complètes.</p>@endif
</div></div>
<div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">Fermes ({{ $fermes->total() }})</h2><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.fermes.create', ['producteur_id' => $producteur->id]) }}">Ajouter une ferme</a></div>
<div class="card"><div class="card-body"><div class="table-responsive"><table class="table"><thead><tr><th>Nom</th><th>Localisation</th><th>Superficie (ha)</th><th>Actions</th></tr></thead><tbody>
@forelse ($fermes as $ferme)
<tr><td><a href="{{ route('admin.fermes.show', $ferme) }}">{{ $ferme->nom }}</a></td><td>{{ $ferme->localisation }}</td><td>{{ $ferme->superficie ?? '—' }}</td><td class="d-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.fermes.edit', $ferme) }}">Modifier</a><form method="POST" action="{{ route('admin.fermes.destroy', $ferme) }}" onsubmit="return confirm('Supprimer cette ferme ?');"> @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Supprimer</button></form></td></tr>
@empty <tr><td colspan="4">Aucune ferme pour ce producteur.</td></tr> @endforelse
</tbody></table></div>{{ $fermes->links() }}</div></div>
<div class="mt-3"><a href="{{ route('admin.producteurs.index') }}" class="btn btn-light">← Retour à la liste</a></div>
@endsection
