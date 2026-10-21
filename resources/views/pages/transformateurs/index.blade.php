@extends('layouts.layout')
@section('title', 'Transformateurs — NutriTrace')
@section('body_class', 'main-layout inner_page')

@section('content')
<section class="container py-5 transformateurs-page">

    <div class="titlepage text_align_left">
        <span>De la ferme à l’assiette</span>
        <h1>TRANSFORMATEURS</h1>
        <p>Découvrez où sont transformés vos aliments et l’empreinte environnementale de chaque étape : émissions de CO₂, eau et énergie, résumées par un éco-score de A à E.</p>
    </div>

    <form id="filter-form" method="GET" action="{{ route('transformateurs.index') }}" class="mb-5">
        <div class="row">
            <div class="col-md-4 mb-2"><input type="text" class="form-control" name="q" placeholder="Nom ou ville..." value="{{ request('q') }}" oninput="clearTimeout(this.timer); this.timer = setTimeout(() => document.getElementById('filter-form').submit(), 500);"></div>
            <div class="col-md-3 mb-2"><select name="type_activite" class="form-control" onchange="this.form.submit()"><option value="">Toutes les activités</option>@foreach (\App\Models\Transformateur::TYPES as $key => $label)<option value="{{ $key }}" @selected(request('type_activite') === $key)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-md-3 mb-2"><select name="eco_score" class="form-control" onchange="this.form.submit()"><option value="">Tous les éco-scores</option>@foreach (\App\Models\Transformation::SCORES as $s)<option value="{{ $s }}" @selected(request('eco_score') === $s)>Au moins une étape {{ $s }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-2 d-flex"><a href="{{ route('transformateurs.index') }}" class="btn btn-outline-secondary" style="padding:10px 15px;">X</a></div>
        </div>
    </form>

    <div class="row">
        @forelse ($transformateurs as $transformateur)
        @php($intensite = $transformateur->intensiteMoyenne())
        <div class="col-md-4 mb-5">
            <article class="services_box_main">
                <div class="services_box text_align_left">
                    <figure style="display:flex;align-items:center;justify-content:center;gap:1rem;background:#f6f7f1;min-height:180px;border-radius:8px 8px 0 0;">
                        <i class="fa fa-industry" aria-hidden="true" style="font-size:64px;color:#7aac2b;"></i>
                        <x-eco-score :score="\App\Models\Transformation::scoreFor($intensite)" size="lg" />
                    </figure>
                    <div class="veget">
                        <h2 style="font-size:1.15rem;">{{ $transformateur->nom }}</h2>
                        <p class="mb-1"><i class="fa fa-cogs" aria-hidden="true"></i> {{ $transformateur->type_label }}</p>
                        <p class="mb-1"><i class="fa fa-map-marker" aria-hidden="true"></i> {{ $transformateur->ville }}</p>
                        <p class="mb-0"><i class="fa fa-leaf" aria-hidden="true"></i> {{ $intensite !== null ? number_format($intensite, 2, ',', ' ').' kg CO₂e / kg' : 'Empreinte non renseignée' }} · {{ $transformateur->transformations_count }} étape(s)</p>
                    </div>
                </div>
                <a class="read_more" href="{{ route('transformateurs.show', $transformateur) }}">Voir le transformateur</a>
            </article>
        </div>
        @empty
        <div class="col-12"><p>Aucun transformateur ne correspond à votre recherche.</p></div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">{{ $transformateurs->links('pagination::bootstrap-4') }}</div>

    <p class="small text-muted mt-4">Éco-score de l’étape de transformation uniquement (hors culture, transport et emballage) : A ≤ 0,2 · B ≤ 0,5 · C ≤ 1 · D ≤ 2 · E &gt; 2 kg CO₂e par kg transformé. Les valeurs sont des estimations déclarées par les transformateurs ou calculées par NutriTrace.</p>
</section>
@endsection
