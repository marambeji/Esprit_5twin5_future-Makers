@extends('admin.layouts.layout')

@section('title', 'Tableau de bord agricole — NutriTrace')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Tableau de bord agricole</h1>
        <p class="text-muted mb-0">Vue d’ensemble des producteurs et de leurs fermes.</p>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary" href="{{ route('admin.producteurs.index') }}">Voir les producteurs</a>
        <a class="btn btn-primary" href="{{ route('admin.fermes.index') }}">Voir les fermes</a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card h-100"><div class="card-body">
            <p class="text-muted mb-2">Producteurs</p>
            <p class="display-6 mb-0">{{ $statistics['total_producteurs'] }}</p>
        </div></div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card h-100"><div class="card-body">
            <p class="text-muted mb-2">Fermes</p>
            <p class="display-6 mb-0">{{ $statistics['total_fermes'] }}</p>
        </div></div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card h-100"><div class="card-body">
            <p class="text-muted mb-2">Superficie totale</p>
            <p class="display-6 mb-0">{{ number_format($statistics['superficie_totale'], 2, ',', ' ') }} <small class="fs-6">ha</small></p>
        </div></div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card h-100"><div class="card-body">
            <p class="text-muted mb-2">Producteurs sans ferme</p>
            <p class="display-6 mb-0">{{ $statistics['producteurs_sans_ferme'] }}</p>
        </div></div>
    </div>
</div>

<div class="row">
    <div class="col-lg-5 mb-4">
        <div class="card h-100"><div class="card-body">
            <h2 class="h5">Superficie moyenne</h2>
            <p class="display-6 mb-2">{{ number_format($statistics['superficie_moyenne'], 2, ',', ' ') }} <small class="fs-6">ha</small></p>
            <p class="text-muted mb-0">Calculée à partir des superficies renseignées.</p>
        </div></div>
    </div>
    <div class="col-lg-7 mb-4">
        <div class="card h-100"><div class="card-body">
            <h2 class="h5">Répartition des fermes par région</h2>
            @forelse($fermesParRegion as $region)
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <span>{{ $region['region'] }}</span>
                    <span class="badge bg-primary rounded-pill">{{ $region['total_fermes'] }} {{ \Illuminate\Support\Str::plural('ferme', $region['total_fermes']) }}</span>
                </div>
            @empty
                <p class="text-muted mb-0">Aucune ferme enregistrée.</p>
            @endforelse
        </div></div>
    </div>
</div>

<div class="card"><div class="card-body">
    <h2 class="h5 mb-3">Nombre de fermes par producteur</h2>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Producteur</th><th class="text-end">Fermes</th></tr></thead>
            <tbody>
            @forelse($fermesParProducteur as $producteur)
                <tr>
                    <td><a href="{{ route('admin.producteurs.show', $producteur) }}">{{ $producteur->prenom }} {{ $producteur->nom }}</a></td>
                    <td class="text-end">{{ $producteur->fermes_count }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="text-muted">Aucun producteur enregistré.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div></div>
@endsection
