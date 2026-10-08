@extends('admin.layouts.layout')

@section('title', 'Certifications — NutriTrace Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Gestion des Certifications</h1>
        <p class="text-muted mb-0 small">Gérer les certifications et labels de traçabilité</p>
    </div>
    <a class="btn btn-primary" href="{{ route('admin.certifications.create') }}">
        <i class="bi bi-plus-lg me-1"></i> Ajouter une certification
    </a>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nom</th>
                        <th>Label</th>
                        <th>N° certificat</th>
                        <th>Date d'obtention</th>
                        <th>Date d'expiration</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($certifications as $certification)
                        <tr>
                            <td class="ps-4 fw-bold">
                                <a href="{{ route('admin.certifications.show', $certification) }}" class="text-dark text-decoration-none">
                                    {{ $certification->nom }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    {{ $certification->label->nom ?? 'N/A' }}
                                </span>
                            </td>
                            <td><code>{{ $certification->numero_certificat }}</code></td>
                            <td>{{ \Carbon\Carbon::parse($certification->date_obtention)->format('d/m/Y') }}</td>
                            <td>
                                @if ($certification->date_expiration)
                                    {{ \Carbon\Carbon::parse($certification->date_expiration)->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($certification->statut === 'valide')
                                    <span class="badge bg-success">Valide</span>
                                @elseif ($certification->statut === 'expiree')
                                    <span class="badge bg-danger">Expirée</span>
                                @else
                                    <span class="badge bg-warning text-dark">Suspendue</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a class="btn btn-outline-info" href="{{ route('admin.certifications.show', $certification) }}" title="Voir les détails">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a class="btn btn-outline-primary" href="{{ route('admin.certifications.edit', $certification) }}" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.certifications.destroy', $certification) }}" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette certification ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger" type="submit" title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-patch-check fs-1 d-block mb-2 text-secondary"></i>
                                Aucune certification trouvée.
                                <div class="mt-2">
                                    <a href="{{ route('admin.certifications.create') }}" class="btn btn-sm btn-primary">
                                        Ajouter la première certification
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($certifications->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $certifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
