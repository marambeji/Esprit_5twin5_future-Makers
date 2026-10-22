@extends('layouts.layout')
@section('title', 'Panier nutritionnel — NutriTrace')
@section('body_class', 'main-layout inner_page')
@section('content')
<section class="container py-5">
    <h1 class="h2">Mon panier végétal</h1>
    <p>Composez votre panier et calculez ses apports nutritionnels.</p>
    <p class="text-muted">Saisissez le poids de la partie comestible en grammes. Choisissez une référence correspondant à l’état de votre aliment (cru, cuit ou séché). Les valeurs enregistrées sont exprimées pour 100 g.</p>
    @if($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if($analyses->isEmpty())
        <div class="alert alert-info">Aucune analyse disponible. Ajoutez des analyses nutritionnelles aux produits depuis l’administration pour utiliser le calculateur.</div>
    @else
    <form method="POST" action="{{ route('nutrition-basket.calculate') }}">
        @csrf
        <div id="basket-items">
        @foreach(old('items', $items) as $index => $item)
            <div class="basket-row border rounded p-3 mb-3">
                <div class="row align-items-end">
                    <div class="col-md-8"><label for="analysis-{{ $index }}">Produit et référence pour 100 g</label><select id="analysis-{{ $index }}" name="items[{{ $index }}][analysis_id]" class="form-control" required>
                        <option value="">Choisir un produit par catégorie</option>
                        @foreach($analyses as $category => $categoryAnalyses)
                        <optgroup label="{{ $category }}">
                        @foreach($categoryAnalyses->sortBy('product.name', SORT_NATURAL | SORT_FLAG_CASE) as $analysis)
                        <option value="{{ $analysis->id }}" @selected(($item['analysis_id'] ?? '') == $analysis->id)>{{ $analysis->product->name }} — {{ $analysis->analysed_at->format('d/m/Y') }} — {{ $analysis->reference_description ?: ($analysis->source ?: 'Source non renseignée') }}{{ $analysis->is_demo ? ' — DONNÉES FICTIVES' : '' }}</option>
                        @endforeach
                        </optgroup>
                        @endforeach
                    </select></div>
                    <div class="col-md-2"><label for="grams-{{ $index }}">Quantité (g)</label><input id="grams-{{ $index }}" name="items[{{ $index }}][grams]" value="{{ $item['grams'] ?? 100 }}" type="number" min="0.01" max="10000" step="0.01" required class="form-control"></div>
                    <div class="col-md-2"><button type="button" class="btn btn-outline-danger mt-2 remove-item">Retirer</button></div>
                </div>
            </div>
        @endforeach
        </div>
        <button type="button" id="add-item" class="btn btn-outline-success mb-3">Ajouter un produit</button>
        <div class="form-group"><label for="portions">Nombre de portions égales</label><input id="portions" name="portions" type="number" min="1" max="100" step="1" value="{{ old('portions', request('portions', 1)) }}" required class="form-control" style="max-width:180px"></div>
        <button class="btn btn-success">Calculer les apports</button>
        <a href="{{ route('nutrition-basket.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
    </form>
    @endif
    @if($result)
    <section id="calculated-result" class="mt-5" aria-labelledby="basket-result">
        <h2 id="basket-result" class="h3">Résultat du calcul</h2>
        <p>Poids total : <strong>{{ number_format($result['weight'], 2, ',', ' ') }} g</strong> · {{ $result['portions'] }} portion(s) de {{ number_format($result['weight'] / $result['portions'], 2, ',', ' ') }} g.</p>
        @if(collect($result['rows'])->contains(fn ($row) => $row['analysis']->is_demo))<div class="alert alert-warning">Ce calcul contient des données fictives de démonstration.</div>@endif
        <div class="table-responsive"><table class="table table-bordered"><caption>Estimations calculées à partir des références sélectionnées. Les sucres sont inclus dans les glucides.</caption><thead><tr><th scope="col">Nutriment</th><th scope="col">Panier entier</th><th scope="col">Par portion</th><th scope="col">Pour 100 g du mélange</th></tr></thead><tbody>
        @foreach($nutrients as $key => $label)<tr><th scope="row">{{ $label }}</th><td>{{ number_format($result['totals'][$key], 2, ',', ' ') }}</td><td>{{ number_format($result['totals'][$key] / $result['portions'], 2, ',', ' ') }}</td><td>{{ number_format($result['totals'][$key] * 100 / $result['weight'], 2, ',', ' ') }}</td></tr>@endforeach
        </tbody></table></div>
        <h3 class="h4 mt-4">Détail et sources</h3>
        @foreach($result['rows'] as $row)
        <article class="border rounded p-3 mb-3">
            <h4 class="h5"><a href="{{ route('catalog.show', $row['analysis']->product) }}">{{ $row['analysis']->product->name }}</a> · {{ number_format($row['grams'], 2, ',', ' ') }} g</h4>
            <p>Analyse du {{ $row['analysis']->analysed_at->format('d/m/Y') }} — {{ $row['analysis']->source ?: 'Source non renseignée' }}{{ $row['analysis']->is_demo ? ' — Données fictives' : '' }}</p>
            @if($row['analysis']->reference_fdc_id)<p>Référence USDA : {{ $row['analysis']->reference_description }} (FDC {{ $row['analysis']->reference_fdc_id }}).</p>@endif
            <ul>@foreach($nutrients as $key => $label)<li>{{ $label }} : {{ number_format($row['values'][$key], 2, ',', ' ') }}</li>@endforeach</ul>
        </article>
        @endforeach
        <p class="text-muted">Calcul : valeur pour 100 g × quantité ÷ 100. Aucune modification liée à la cuisson n’est calculée. Ce panier est une simulation et ne crée pas de commande.</p>
    </section>
    @endif
    <a href="{{ route('catalog.index') }}" class="d-inline-block mt-4">Retour au catalogue</a>
</section>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('basket-items');
    const add = document.getElementById('add-item');
    if (!list || !add) return;
    let next = Math.max(...Array.from(list.querySelectorAll('select')).map(select => Number(select.id.replace('analysis-', '')))) + 1;
    const invalidate = () => { const result = document.getElementById('calculated-result'); if (result) result.hidden = true; };
    list.closest('form').addEventListener('input', invalidate);
    list.closest('form').addEventListener('change', invalidate);
    const refresh = () => {
        add.disabled = list.children.length >= 20;
        list.querySelectorAll('.remove-item').forEach(button => button.disabled = list.children.length === 1);
    };
    add.addEventListener('click', () => {
        if (list.children.length >= 20) return;
        const row = list.firstElementChild.cloneNode(true);
        const select = row.querySelector('select');
        const input = row.querySelector('input');
        select.name = `items[${next}][analysis_id]`; select.id = `analysis-${next}`; select.value = '';
        input.name = `items[${next}][grams]`; input.id = `grams-${next}`; input.value = '100';
        const labels = row.querySelectorAll('label'); labels[0].htmlFor = select.id; labels[1].htmlFor = input.id;
        next++; list.append(row); refresh(); invalidate(); select.focus();
    });
    list.addEventListener('click', event => {
        if (event.target.closest('.remove-item') && list.children.length > 1) {
            event.target.closest('.basket-row').remove(); refresh(); invalidate();
        }
    });
    refresh();
});
</script>
@endsection
