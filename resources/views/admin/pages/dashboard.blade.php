@extends('admin.layouts.layout')

@section('title', 'Tableau de bord — NutriTrace')

@section('content')
<div class="d-sm-flex justify-content-between align-items-center mb-4">
    <div><h1 class="h3 mb-2">Tableau de bord NutriTrace</h1><p class="text-muted mb-0">Suivre l’origine des aliments et rendre leurs engagements transparents.</p></div>
    <a href="{{ route('home') }}" class="btn btn-primary mt-3 mt-sm-0">Consulter le Front Office</a>
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
    <div class="col-md-6 mb-4 stretch-card" id="environnement"><div class="card"><div class="card-body"><h2 class="card-title">Transparence environnementale</h2><p>Pour chaque estimation d’empreinte, indiquer l’unité, le périmètre et la méthode utilisée.</p><p class="text-muted mb-0">Le tableau de bord est prêt à accueillir les données des futurs modules. Aucun indicateur chiffré n’est présenté avant leur intégration.</p></div></div></div>
</div>
@endsection
