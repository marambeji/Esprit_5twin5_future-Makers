@extends('layouts.layout')

@section('title', 'Nos Certifications — NutriTrace')
@section('body_class', 'main-layout inner_page')

@section('content')
<div class="container py-5 mt-4">
    <div class="text-center mb-5">
        <h1 class="display-5 font-weight-bold text-success mb-2">Certifications & Labels</h1>
        <p class="lead text-muted max-w-2xl mx-auto">
            Découvrez l'ensemble de nos certifications officielles et de nos engagements qualité pour une traçabilité alimentaire exemplaire.
        </p>
    </div>

    <div class="row g-4">
        @forelse ($certifications as $certification)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow-sm rounded-lg overflow-hidden transition-all hover-shadow">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-start">
                        <span class="badge badge-info px-3 py-2 font-weight-normal text-uppercase">
                            {{ $certification->label->nom ?? 'Label Officiel' }}
                        </span>
                        @if ($certification->statut === 'valide')
                            <span class="badge badge-success px-2 py-1">Valide</span>
                        @elseif ($certification->statut === 'expiree')
                            <span class="badge badge-danger px-2 py-1">Expirée</span>
                        @else
                            <span class="badge badge-warning px-2 py-1 text-white">Suspendue</span>
                        @endif
                    </div>
                    <div class="card-body p-4">
                        <h3 class="h5 font-weight-bold text-dark mb-2">
                            <a href="{{ route('certifications.show', $certification) }}" class="text-dark text-decoration-none">
                                {{ $certification->nom }}
                            </a>
                        </h3>
                        <p class="text-muted small mb-3">
                            <i class="fa fa-id-card-o mr-1"></i> Certificat N° : <code>{{ $certification->numero_certificat }}</code>
                        </p>

                        @if ($certification->description)
                            <p class="text-secondary small mb-3 text-truncate-2">
                                {{ Str::limit($certification->description, 110) }}
                            </p>
                        @endif

                        <div class="border-top pt-3 text-muted small">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Obtention :</span>
                                <span class="font-weight-bold">{{ \Carbon\Carbon::parse($certification->date_obtention)->format('d/m/Y') }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Expiration :</span>
                                <span class="font-weight-bold">
                                    @if ($certification->date_expiration)
                                        {{ \Carbon\Carbon::parse($certification->date_expiration)->format('d/m/Y') }}
                                    @else
                                        <span class="text-muted">Indéterminée</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light border-0 p-3 text-center">
                        <a href="{{ route('certifications.show', $certification) }}" class="btn btn-outline-success btn-sm font-weight-bold btn-block">
                            Voir la fiche de certification &rarr;
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 py-5 text-center text-muted">
                <i class="fa fa-certificate fa-4x mb-3 text-secondary d-block"></i>
                <h4 class="h5 font-weight-bold">Aucune certification affichée</h4>
                <p class="text-muted">Toutes nos certifications et leurs preuves seront publiées sous peu.</p>
            </div>
        @endforelse
    </div>

    @if ($certifications->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $certifications->links() }}
        </div>
    @endif
</div>
@endsection
