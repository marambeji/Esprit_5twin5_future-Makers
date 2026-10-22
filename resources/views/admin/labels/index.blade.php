@extends('admin.layouts.layout')

@section('title', 'Labels — NutriTrace Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Gestion des Labels</h1>
        <p class="text-muted mb-0 small">Gérer les labels de qualité (Bio, Fairtrade, ISO...) et leurs certifications</p>
    </div>
    <a class="btn btn-primary" href="{{ route('admin.labels.create') }}">
        <i class="bi bi-plus-lg me-1"></i> Ajouter un label
    </a>
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

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.labels.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Rechercher par nom, organisme, mot-clé..." value="{{ $search ?? '' }}">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-primary">Filtrer</button>
                @if (!empty($search))
                    <a href="{{ route('admin.labels.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nom du label</th>
                        <th>Organisme certificateur</th>
                        <th>Description</th>
                        <th>Certifications liées</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($labels as $label)
                        <tr>
                            <td class="ps-4 fw-bold">
                                <a href="{{ route('admin.labels.show', $label) }}" class="text-dark text-decoration-none d-flex align-items-center">
                                    <span class="badge bg-light-primary text-primary me-2 p-2 rounded-circle">
                                        <i class="bi bi-award fs-6"></i>
                                    </span>
                                    <span>{{ $label->nom }}</span>
                                </a>
                            </td>
                            <td>
                                @if ($label->organisme)
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-building me-1"></i> {{ $label->organisme }}
                                    </span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($label->description)
                                    <span class="text-muted small" title="{{ $label->description }}">
                                        {{ \Illuminate\Support\Str::limit($label->description, 60) }}
                                    </span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.labels.show', $label) }}" class="badge rounded-pill {{ $label->certifications_count > 0 ? 'bg-success' : 'bg-secondary' }} text-decoration-none">
                                    <i class="bi bi-patch-check me-1"></i>
                                    {{ $label->certifications_count }} {{ \Illuminate\Support\Str::plural('certification', $label->certifications_count) }}
                                </a>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a class="btn btn-outline-info" href="{{ route('admin.labels.show', $label) }}" title="Voir les détails">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a class="btn btn-outline-primary" href="{{ route('admin.labels.edit', $label) }}" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.labels.destroy', $label) }}" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce label ?');">
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
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-award fs-1 d-block mb-2 text-secondary"></i>
                                @if (!empty($search))
                                    Aucun label ne correspond à votre recherche « {{ $search }} ».
                                    <div class="mt-2">
                                        <a href="{{ route('admin.labels.index') }}" class="btn btn-sm btn-outline-secondary">Effacer la recherche</a>
                                    </div>
                                @else
                                    Aucun label trouvé.
                                    <div class="mt-2">
                                        <a href="{{ route('admin.labels.create') }}" class="btn btn-sm btn-primary">
                                            Ajouter le premier label
                                        </a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($labels->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $labels->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
