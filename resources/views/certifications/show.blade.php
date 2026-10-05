@extends('layouts.layout')

@section('title', $certification->nom . ' — NutriTrace')
@section('body_class', 'main-layout inner_page')

@section('content')
<div class="container py-5 mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="mb-4">
                <a href="{{ route('certifications.index') }}" class="btn btn-outline-secondary btn-sm font-weight-bold">
                    &larr; Retour aux certifications
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-lg overflow-hidden mb-4">
                <div class="card-header bg-success text-white py-4 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge badge-light text-success text-uppercase mb-2 font-weight-bold">
                            {{ $certification->label->nom ?? 'Label Certifié' }}
                        </span>
                        <h1 class="h3 font-weight-bold mb-0">{{ $certification->nom }}</h1>
                    </div>
                    <div>
                        @if ($certification->statut === 'valide')
                            <span class="badge badge-light text-success px-3 py-2 font-weight-bold">Valide</span>
                        @elseif ($certification->statut === 'expiree')
                            <span class="badge badge-danger px-3 py-2 font-weight-bold">Expirée</span>
                        @else
                            <span class="badge badge-warning text-white px-3 py-2 font-weight-bold">Suspendue</span>
                        @endif
                    </div>
                </div>

                <div class="card-body p-4 p-md-5">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-4 mb-md-0">
                            <h5 class="text-success text-uppercase small font-weight-bold mb-3 border-bottom pb-2">
                                <i class="fa fa-info-circle mr-1"></i> Informations Générales
                            </h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Numéro de certificat :</span>
                                    <code>{{ $certification->numero_certificat }}</code>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Date d'obtention :</span>
                                    <span class="font-weight-bold">{{ \Carbon\Carbon::parse($certification->date_obtention)->format('d/m/Y') }}</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Date d'expiration :</span>
                                    <span class="font-weight-bold">
                                        @if ($certification->date_expiration)
                                            {{ \Carbon\Carbon::parse($certification->date_expiration)->format('d/m/Y') }}
                                        @else
                                            <span class="text-muted">Indéterminée</span>
                                        @endif
                                    </span>
                                </li>
                            </ul>
                        </div>

                        <div class="col-md-6">
                            <h5 class="text-success text-uppercase small font-weight-bold mb-3 border-bottom pb-2">
                                <i class="fa fa-tag mr-1"></i> Organisme & Label
                            </h5>
                            <div class="p-3 bg-light rounded-lg border">
                                @if ($certification->label)
                                    <h6 class="font-weight-bold text-dark mb-1">{{ $certification->label->nom }}</h6>
                                    @if ($certification->label->organisme)
                                        <p class="text-muted small mb-2">
                                            <strong>Organisme certificateur :</strong> {{ $certification->label->organisme }}
                                        </p>
                                    @endif
                                    @if ($certification->label->description)
                                        <p class="small text-secondary mb-0">
                                            {{ $certification->label->description }}
                                        </p>
                                    @endif
                                @else
                                    <span class="text-muted">Aucun organisme spécifié</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($certification->description)
                        <div class="pt-3 border-top">
                            <h5 class="text-success text-uppercase small font-weight-bold mb-3">
                                <i class="fa fa-align-left mr-1"></i> Description du certificat
                            </h5>
                            <div class="bg-light p-4 rounded-lg text-secondary">
                                {{ $certification->description }}
                            </div>
                        </div>
                    @endif
                </div>

                <div class="card-footer bg-light p-3 border-top text-right">
                    <a href="{{ route('certifications.index') }}" class="btn btn-outline-success font-weight-bold">
                        &larr; Retour à la liste des certifications
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
