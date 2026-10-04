@extends('layouts.layout')

@section('title', 'Certifications — Nutritrace')
@section('body_class', 'main-layout inner_page')

@section('content')
<div class="container py-5 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 font-weight-bold text-success mb-1">Module Certifications</h1>
            <p class="text-muted mb-0">Gestion des certifications et labels de traçabilité</p>
        </div>
        <a href="{{ route('certifications.create') }}" class="btn btn-success px-4 py-2 shadow-sm font-weight-bold">
            <i class="fa fa-plus-circle mr-1"></i> Ajouter une certification
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="bg-light text-dark font-weight-bold">
                        <tr>
                            <th class="py-3 text-left pl-4">Nom</th>
                            <th class="py-3">Label</th>
                            <th class="py-3">N° certificat</th>
                            <th class="py-3">Obtention</th>
                            <th class="py-3">Expiration</th>
                            <th class="py-3">Statut</th>
                            <th class="py-3 pr-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($certifications as $certification)
                            <tr>
                                <td class="text-left pl-4 font-weight-bold text-dark">
                                    <a href="{{ route('certifications.show', $certification) }}" class="text-dark text-decoration-none">
                                        {{ $certification->nom }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge badge-info px-2 py-1 font-weight-normal">
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
                                        <span class="badge badge-success px-2 py-1">Valide</span>
                                    @elseif ($certification->statut === 'expiree')
                                        <span class="badge badge-danger px-2 py-1">Expirée</span>
                                    @else
                                        <span class="badge badge-warning px-2 py-1 text-white">Suspendue</span>
                                    @endif
                                </td>
                                <td class="pr-4">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('certifications.show', $certification) }}" class="btn btn-sm btn-outline-info mr-1" title="Voir les détails">
                                            Voir
                                        </a>
                                        <a href="{{ route('certifications.edit', $certification) }}" class="btn btn-sm btn-outline-warning mr-1" title="Modifier">
                                            Modifier
                                        </a>
                                        <form method="POST" action="{{ route('certifications.destroy', $certification) }}" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette certification ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-5 text-muted">
                                    <i class="fa fa-certificate fa-3x mb-3 text-secondary d-block"></i>
                                    Aucune certification enregistrée pour le moment.
                                    <div class="mt-2">
                                        <a href="{{ route('certifications.create') }}" class="btn btn-sm btn-success">
                                            Créer une certification
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
</div>
@endsection
