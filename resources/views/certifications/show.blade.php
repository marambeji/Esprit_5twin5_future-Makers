@extends('layouts.layout')

@section('title', 'Détails de la Certification — Nutritrace')
@section('body_class', 'main-layout inner_page')

@section('content')
<div class="container py-5 mt-4">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 font-weight-bold text-success mb-0">Détails de la Certification</h1>
                <a href="{{ route('certifications.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Retour à la liste
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-lg overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h4 font-weight-bold mb-1 text-dark">{{ $certification->nom }}</h2>
                        <span class="text-muted">Certificat N° <code>{{ $certification->numero_certificat }}</code></span>
                    </div>
                    <div>
                        @if ($certification->statut === 'valide')
                            <span class="badge badge-success px-3 py-2 font-weight-normal font-size-base">Statut: Valide</span>
                        @elseif ($certification->statut === 'expiree')
                            <span class="badge badge-danger px-3 py-2 font-weight-normal font-size-base">Statut: Expirée</span>
                        @else
                            <span class="badge badge-warning px-3 py-2 text-white font-weight-normal font-size-base">Statut: Suspendue</span>
                        @endif
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <h5 class="text-muted text-uppercase small font-weight-bold mb-2">Informations Générales</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    <span class="text-muted">Nom:</span>
                                    <span class="font-weight-bold">{{ $certification->nom }}</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    <span class="text-muted">Date d'obtention:</span>
                                    <span>{{ \Carbon\Carbon::parse($certification->date_obtention)->format('d/m/Y') }}</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    <span class="text-muted">Date d'expiration:</span>
                                    <span>
                                        @if ($certification->date_expiration)
                                            {{ \Carbon\Carbon::parse($certification->date_expiration)->format('d/m/Y') }}
                                        @else
                                            <span class="text-muted">Non définie (indéterminée)</span>
                                        @endif
                                    </span>
                                </li>
                            </ul>
                        </div>

                        <div class="col-md-6 mb-3">
                            <h5 class="text-muted text-uppercase small font-weight-bold mb-2">Label Associé</h5>
                            <div class="p-3 bg-light rounded">
                                @if ($certification->label)
                                    <h6 class="font-weight-bold text-success mb-1">{{ $certification->label->nom }}</h6>
                                    @if ($certification->label->organisme)
                                        <p class="text-muted small mb-2">
                                            <strong>Organisme:</strong> {{ $certification->label->organisme }}
                                        </p>
                                    @endif
                                    @if ($certification->label->description)
                                        <p class="small text-secondary mb-0">
                                            {{ $certification->label->description }}
                                        </p>
                                    @endif
                                @else
                                    <span class="text-muted">Aucun label associé</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($certification->description)
                        <div class="border-top pt-3 mt-2">
                            <h5 class="text-muted text-uppercase small font-weight-bold mb-2">Description / Notes</h5>
                            <p class="text-dark bg-light p-3 rounded mb-0">
                                {{ $certification->description }}
                            </p>
                        </div>
                    @endif
                </div>

                <div class="card-footer bg-white py-3 border-top d-flex justify-content-end gap-2">
                    <a href="{{ route('certifications.edit', $certification) }}" class="btn btn-warning mr-2 text-white font-weight-bold">
                        Modifier
                    </a>
                    <form method="POST" action="{{ route('certifications.destroy', $certification) }}" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette certification ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger font-weight-bold">
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
