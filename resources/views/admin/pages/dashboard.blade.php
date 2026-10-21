@extends('admin.layouts.layout')

@section('title', 'Tableau de bord — NutriTrace')

@section('content')
<div class="d-sm-flex justify-content-between align-items-center mb-4">
    <div><h1 class="h3 mb-2">Tableau de bord NutriTrace</h1><p class="text-muted mb-0">Suivre l’origine des aliments et rendre leurs engagements transparents.</p></div>
    <a href="{{ route('home') }}" class="btn btn-primary mt-3 mt-sm-0">Consulter le Site public</a>
</div>
<div class="row">
    @foreach ([['bi-flower1', 'Origine', 'Identifier le producteur et la région d’origine.'], ['bi-truck', 'Traçabilité', 'Documenter les étapes de transformation et de distribution.'], ['bi-patch-check', 'Certifications', 'Associer les déclarations aux preuves disponibles.'], ['bi-tree', 'Empreinte', 'Présenter les estimations et leur méthode de calcul.']] as [$icon, $label, $description])
    <div class="col-xl-3 col-md-6 mb-4 stretch-card"><div class="card h-100"><div class="card-body"><i class="bi {{ $icon }} text-success fs-1"></i><h2 class="h5 mt-3">{{ $label }}</h2><p class="text-muted mb-0">{{ $description }}</p></div></div></div>
    @endforeach
</div>
<div class="row" id="parcours"><div class="col-12 mb-4"><div class="card"><div class="card-body">
    <h2 class="card-title">De la ferme à l’assiette</h2>
    <div class="row">
        @foreach (['Producteur' => 'Origine, récolte et pratiques agricoles.', 'Transformateur' => 'Transformation du produit et composition.', 'Distributeur' => 'Transport, stockage et mise en vente.', 'Consommateur' => 'Accès aux informations pour un choix éclairé.'] as $actor => $description)
        <div class="col-md-3 mb-3"><div class="trace-step"><h3 class="h5">{{ $loop->iteration }}. {{ $actor }}</h3><p class="mb-0">{{ $description }}</p></div></div>
        @endforeach
    </div>
</div></div></div></div>
<div class="row">
    <div class="col-md-6 mb-4 stretch-card" id="certifications"><div class="card"><div class="card-body"><h2 class="card-title">Certifications et preuves</h2><p>Bio, local, équitable : chaque déclaration doit préciser sa source et les justificatifs disponibles.</p><div class="d-flex flex-wrap"><span class="badge bg-success me-2 mb-2">Bio</span><span class="badge bg-info me-2 mb-2">Local</span><span class="badge bg-warning text-dark mb-2">Équitable</span></div><p class="text-muted mt-3 mb-0">Ces labels illustrent les catégories du projet ; ils ne certifient aucun produit.</p></div></div></div>
    @php
        $env = \App\Models\Transformation::query()->whereNotNull('co2_kg')->selectRaw('COUNT(*) as n, SUM(co2_kg) as co2, SUM(eau_litres) as eau, SUM(energie_kwh) as kwh, SUM(quantite_kg) as kg')->first();
        $parScore = \App\Models\Transformation::whereNotNull('eco_score')->selectRaw('eco_score, COUNT(*) as n')->groupBy('eco_score')->pluck('n', 'eco_score');
        $alertes = \App\Models\Transformation::whereIn('verdict_allegation', ['exageree', 'trompeuse'])->count();
    @endphp
    <div class="col-md-6 mb-4 stretch-card" id="environnement"><div class="card"><div class="card-body"><h2 class="card-title">Transparence environnementale</h2>
        @if ($env->n)
        <p class="text-muted">Périmètre : étapes de transformation enregistrées ({{ $env->n }}), {{ number_format($env->kg, 0, ',', ' ') }} kg traités.</p>
        <div class="row text-center mb-3">
            <div class="col-4"><div class="h4 mb-0">{{ number_format($env->co2, 0, ',', ' ') }}</div><div class="small text-muted">kg CO₂e</div></div>
            <div class="col-4"><div class="h4 mb-0">{{ number_format($env->eau, 0, ',', ' ') }}</div><div class="small text-muted">litres d’eau</div></div>
            <div class="col-4"><div class="h4 mb-0">{{ number_format($env->kwh, 0, ',', ' ') }}</div><div class="small text-muted">kWh</div></div>
        </div>
        <div class="d-flex align-items-center gap-3 mb-3">@foreach (\App\Models\Transformation::SCORES as $s)<span class="d-inline-flex align-items-center gap-1"><x-eco-score :score="$s" size="sm" /> {{ $parScore[$s] ?? 0 }}</span>@endforeach</div>
        <p class="mb-0">@if ($alertes)<span class="badge bg-danger">{{ $alertes }} allégation(s) suspecte(s)</span> @endif<a href="{{ route('admin.transformations.index') }}">Voir les transformations</a></p>
        @else
        <p>Pour chaque estimation d’empreinte, indiquer l’unité, le périmètre et la méthode utilisée.</p><p class="text-muted mb-0">Aucune transformation avec empreinte n’est encore enregistrée. <a href="{{ route('admin.transformations.create') }}">Ajouter une transformation</a></p>
        @endif
    </div></div></div>
</div>
@endsection
