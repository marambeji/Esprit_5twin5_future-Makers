@extends('admin.layouts.layout')
@section('title', 'Ferme — NutriTrace')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
<h1 class="h3">{{ $ferme->nom }}</h1>
<div class="d-flex gap-2"><a class="btn btn-outline-primary" href="{{ route('admin.fermes.edit', $ferme) }}">Modifier</a><form method="POST" action="{{ route('admin.fermes.destroy', $ferme) }}" onsubmit="return confirm('Supprimer cette ferme ?');"> @csrf @method('DELETE')<button class="btn btn-outline-danger">Supprimer</button></form></div>
</div>
<div class="card"><div class="card-body">
<dl class="row mb-0">
<dt class="col-sm-3">Nom</dt><dd class="col-sm-9">{{ $ferme->nom }}</dd>
<dt class="col-sm-3">Producteur</dt><dd class="col-sm-9"><a href="{{ route('admin.producteurs.show', $ferme->producteur) }}">{{ $ferme->producteur->prenom }} {{ $ferme->producteur->nom }}</a></dd>
<dt class="col-sm-3">Localisation</dt><dd class="col-sm-9">{{ $ferme->localisation }}</dd>
<dt class="col-sm-3">Superficie</dt><dd class="col-sm-9">{{ $ferme->superficie ? $ferme->superficie.' ha' : '—' }}</dd>
<dt class="col-sm-3">Description</dt><dd class="col-sm-9">{{ $ferme->description ?? '—' }}</dd>
</dl>
</div></div>
<div class="mt-3"><a href="{{ route('admin.fermes.index') }}" class="btn btn-light">← Retour à la liste</a></div>
@endsection
