@extends('admin.layouts.layout')
@section('title', 'Détail de l’analyse — NutriTrace')
@section('content')
<h1 class="h3 mb-4">Analyse nutritionnelle de {{ $analysis->product->name }}</h1>
<div class="card"><div class="card-body"><p>Produit : <a href="{{ route('admin.products.show', $analysis->product) }}">{{ $analysis->product->name }}</a></p>
@include('partials.nutrition-values')
<a class="btn btn-primary" href="{{ route('admin.nutrition.edit', $analysis) }}">Modifier</a> <a class="btn btn-outline-primary" href="{{ route('admin.nutrition.index') }}">Retour à la liste</a>
</div></div>
@endsection
