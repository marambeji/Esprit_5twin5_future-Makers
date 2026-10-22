@extends('layouts.layout')
@section('title', 'Mes commandes — NutriTrace')
@section('body_class', 'main-layout inner_page')
@section('content')
<section class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h2 mb-0">Mes commandes</h1><a class="btn btn-success" href="{{ route('commandes.create') }}">Passer une commande</a></div>
    @include('partials.flash')
    <form id="filtre" method="GET" class="form-inline mb-4">
        <input type="search" name="q" value="{{ $q }}" class="form-control mr-2 mb-2" style="min-width:280px" placeholder="Rechercher (produit, n° de commande)">
        <select name="statut" class="form-control mr-2 mb-2">
            <option value="">Tous les statuts</option>
            @foreach (\App\Models\Commande::STATUSES as $key => $label)<option value="{{ $key }}" @selected($statut === $key)>{{ $label }}</option>@endforeach
        </select>
        <button class="btn btn-success mr-2 mb-2">Filtrer</button>
        @if ($q !== '' || $statut)<a class="btn btn-outline-secondary mb-2" href="{{ route('commandes.index') }}">Réinitialiser</a>@endif
    </form>
    <div id="liste"><div class="table-responsive"><table class="table">
        <thead><tr><th>Référence</th><th>Produit</th><th>Quantité</th><th>Commande</th><th>Livraison</th><th></th></tr></thead>
        <tbody>
        @forelse ($commandes as $commande)
            <tr>
                <td>{{ $commande->reference }}</td>
                <td>{{ $commande->product->name }}</td>
                <td>{{ $commande->quantite }}</td>
                <td>{{ $commande->statut_label }}</td>
                <td>{{ $commande->delivery?->status_label ?? '—' }}</td>
                <td><a class="text-success" href="{{ route('commandes.show', $commande) }}">Suivre</a></td>
            </tr>
        @empty
            <tr><td colspan="6">Aucune commande trouvée.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $commandes->links('pagination::bootstrap-4') }}
    </div>
</section>
<script>
// Recherche dynamique : recharge uniquement la liste après 300 ms sans frappe.
const form = document.getElementById('filtre');
let timer;
async function refresh() {
    const url = form.action.split('?')[0] + '?' + new URLSearchParams(new FormData(form));
    const html = await (await fetch(url)).text();
    document.getElementById('liste').innerHTML = new DOMParser().parseFromString(html, 'text/html').getElementById('liste').innerHTML;
    history.replaceState(null, '', url);
}
form.addEventListener('submit', e => { e.preventDefault(); refresh(); });
form.q.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(refresh, 300); });
form.statut.addEventListener('change', refresh);
</script>
@endsection
