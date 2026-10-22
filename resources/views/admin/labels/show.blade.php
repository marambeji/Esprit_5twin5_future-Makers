@extends('admin.layouts.layout')

@section('title', 'Label : ' . $label->nom . ' — NutriTrace Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Label : {{ $label->nom }}</h1>
        <p class="text-muted mb-0 small">Détails du label et certifications rattachées</p>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.labels.index') }}">
            <i class="bi bi-arrow-left me-1"></i> Retour aux labels
        </a>
        <a class="btn btn-primary btn-sm" href="{{ route('admin.labels.edit', $label) }}">
            <i class="bi bi-pencil me-1"></i> Modifier ce label
        </a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                    <i class="bi bi-award text-primary me-2"></i> Informations du label
                </h5>
            </div>
            <div class="card-body p-4">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Nom du label :</dt>
                    <dd class="col-sm-8 fw-bold fs-5 text-dark">{{ $label->nom }}</dd>

                    <dt class="col-sm-4 text-muted">Organisme :</dt>
                    <dd class="col-sm-8">
                        @if ($label->organisme)
                            <span class="badge bg-light text-dark border px-2 py-1">
                                <i class="bi bi-building me-1"></i> {{ $label->organisme }}
                            </span>
                        @else
                            <span class="text-muted">Non spécifié</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4 text-muted">Date d'enregistrement :</dt>
                    <dd class="col-sm-8 text-secondary">{{ $label->created_at ? $label->created_at->format('d/m/Y H:i') : '—' }}</dd>

                    <dt class="col-sm-4 text-muted">Dernière modification :</dt>
                    <dd class="col-sm-8 text-secondary">{{ $label->updated_at ? $label->updated_at->format('d/m/Y H:i') : '—' }}</dd>

                    <dt class="col-sm-4 text-muted mt-2">Description :</dt>
                    <dd class="col-sm-8 mt-2">
                        @if ($label->description)
                            <div class="p-3 bg-light rounded border text-secondary" style="white-space: pre-line;">{{ $label->description }}</div>
                        @else
                            <span class="text-muted fst-italic">Aucune description fournie.</span>
                        @endif
                    </dd>
                </dl>
            </div>
            <div class="card-footer bg-white py-3 border-top d-flex justify-content-end gap-2">
                <form method="POST" action="{{ route('admin.labels.destroy', $label) }}" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce label ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-trash me-1"></i> Supprimer ce label
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm h-100 bg-light border-0">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <h6 class="text-muted text-uppercase small font-weight-bold mb-3">Statistiques</h6>
                    <div class="d-flex align-items-center mb-3 p-3 bg-white rounded border">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success me-3">
                            <i class="bi bi-patch-check fs-3"></i>
                        </div>
                        <div>
                            <div class="fs-3 fw-bold text-dark">{{ $label->certifications->count() }}</div>
                            <div class="small text-muted">Certifications associées</div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="{{ route('admin.certifications.create', ['label_id' => $label->id]) }}" class="btn btn-success w-100">
                        <i class="bi bi-plus-circle me-1"></i> Nouvelle certification pour ce label
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-dark">
            <i class="bi bi-list-check text-success me-2"></i> Certifications rattachées à « {{ $label->nom }} »
        </h5>
        <a class="btn btn-sm btn-outline-success" href="{{ route('admin.certifications.create', ['label_id' => $label->id]) }}">
            <i class="bi bi-plus-lg me-1"></i> Ajouter une certification
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nom de la certification</th>
                        <th>N° certificat</th>
                        <th>Date d'obtention</th>
                        <th>Date d'expiration</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($label->certifications as $cert)
                        <tr>
                            <td class="ps-4 fw-bold">
                                <a href="{{ route('admin.certifications.show', $cert) }}" class="text-dark text-decoration-none">
                                    {{ $cert->nom }}
                                </a>
                            </td>
                            <td><code>{{ $cert->numero_certificat }}</code></td>
                            <td>{{ \Carbon\Carbon::parse($cert->date_obtention)->format('d/m/Y') }}</td>
                            <td>
                                @if ($cert->date_expiration)
                                    {{ \Carbon\Carbon::parse($cert->date_expiration)->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($cert->statut === 'valide')
                                    <span class="badge bg-success">Valide</span>
                                @elseif ($cert->statut === 'expiree')
                                    <span class="badge bg-danger">Expirée</span>
                                @else
                                    <span class="badge bg-warning text-dark">Suspendue</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a class="btn btn-outline-info" href="{{ route('admin.certifications.show', $cert) }}" title="Voir la certification">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a class="btn btn-outline-primary" href="{{ route('admin.certifications.edit', $cert) }}" title="Modifier la certification">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-info-circle fs-3 d-block mb-2 text-secondary"></i>
                                Aucune certification n'est encore rattachée à ce label.
                                <div class="mt-2">
                                    <a href="{{ route('admin.certifications.create', ['label_id' => $label->id]) }}" class="btn btn-sm btn-success">
                                        <i class="bi bi-plus-lg me-1"></i> Créer la première certification
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
