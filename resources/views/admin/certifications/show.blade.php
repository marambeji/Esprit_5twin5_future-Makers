@extends('admin.layouts.layout')

@section('title', 'Détails de la certification — NutriTrace Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Détails de la certification</h1>
        <p class="text-muted mb-0 small">N° <code>{{ $certification->numero_certificat }}</code></p>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.certifications.index') }}">
        <i class="bi bi-arrow-left me-1"></i> Retour à la liste
    </a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0 font-weight-bold text-dark">{{ $certification->nom }}</h2>
        <div>
            @if ($certification->statut === 'valide')
                <span class="badge bg-success">Statut: Valide</span>
            @elseif ($certification->statut === 'expiree')
                <span class="badge bg-danger">Statut: Expirée</span>
            @else
                <span class="badge bg-warning text-dark">Statut: Suspendue</span>
            @endif
        </div>
    </div>
    <div class="card-body p-4">
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <h6 class="text-muted text-uppercase small font-weight-bold mb-3">Informations de la certification</h6>
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted" style="width: 150px;">Nom:</td>
                        <td class="fw-bold">{{ $certification->nom }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">N° Certificat:</td>
                        <td><code>{{ $certification->numero_certificat }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date d'obtention:</td>
                        <td>{{ \Carbon\Carbon::parse($certification->date_obtention)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date d'expiration:</td>
                        <td>
                            @if ($certification->date_expiration)
                                {{ \Carbon\Carbon::parse($certification->date_expiration)->format('d/m/Y') }}
                            @else
                                <span class="text-muted">Indéterminée</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <div class="col-md-6">
                <h6 class="text-muted text-uppercase small font-weight-bold mb-3">Label Associé</h6>
                <div class="p-3 bg-light rounded border">
                    @if ($certification->label)
                        <h6 class="fw-bold text-success mb-1">{{ $certification->label->nom }}</h6>
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
            <div class="border-top pt-3 mt-3">
                <h6 class="text-muted text-uppercase small font-weight-bold mb-2">Description / Observations</h6>
                <div class="bg-light p-3 rounded text-dark border">
                    {{ $certification->description }}
                </div>
            </div>
        @endif
    </div>
    <div class="card-footer bg-white py-3 border-top d-flex justify-content-end gap-2">
        <a href="{{ route('admin.certifications.edit', $certification) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Modifier
        </a>
        <form method="POST" action="{{ route('admin.certifications.destroy', $certification) }}" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette certification ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="bi bi-trash me-1"></i> Supprimer
            </button>
        </form>
    </div>
</div>
@endsection
