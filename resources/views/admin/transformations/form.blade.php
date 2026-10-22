@extends('admin.layouts.layout')
@section('title', 'Transformation — NutriTrace')
@section('content')
@php($t = $transformation)
<h1 class="h3 mb-4">{{ $t->exists ? 'Modifier la transformation '.$t->reference : 'Ajouter une transformation' }}</h1>
@if ($transformateurs->isEmpty() || $products->isEmpty())
<div class="alert alert-info">Il faut au moins un transformateur et un produit avant d’ajouter une transformation.
    @if ($transformateurs->isEmpty())<a href="{{ route('admin.transformateurs.create') }}">Ajouter un transformateur</a>@endif
    @if ($products->isEmpty())<a href="{{ route('admin.products.create') }}">Ajouter un produit</a>@endif
</div>
@endif

<form id="transformation-form" method="POST" action="{{ $t->exists ? route('admin.transformations.update', $t) : route('admin.transformations.store') }}" novalidate>
@csrf @if ($t->exists) @method('PUT') @endif
<div class="row">
<div class="col-lg-7 mb-4"><div class="card h-100"><div class="card-body">
    <h2 class="h5 mb-3">Étape de transformation</h2>
    <div class="row">
    <div class="col-md-6 mb-3"><label for="transformateur_id" class="form-label">Transformateur *</label><select id="transformateur_id" name="transformateur_id" class="form-select @error('transformateur_id') is-invalid @enderror" required><option value="">Choisir</option>@foreach ($transformateurs as $tr)<option value="{{ $tr->id }}" @selected(old('transformateur_id', $t->transformateur_id) == $tr->id)>{{ $tr->nom }} ({{ $tr->ville }})</option>@endforeach</select>@error('transformateur_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-6 mb-3"><label for="product_id" class="form-label">Produit *</label><select id="product_id" name="product_id" class="form-select @error('product_id') is-invalid @enderror" required><option value="">Choisir</option>@foreach ($products as $p)<option value="{{ $p->id }}" @selected(old('product_id', $t->product_id) == $p->id)>{{ $p->name }}</option>@endforeach</select>@error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-6 mb-3"><label for="ferme_id" class="form-label">Ferme d’origine</label><select id="ferme_id" name="ferme_id" class="form-select @error('ferme_id') is-invalid @enderror"><option value="">Non renseignée</option>@foreach ($fermes as $f)<option value="{{ $f->id }}" @selected(old('ferme_id', $t->ferme_id) == $f->id)>{{ $f->nom }} — {{ $f->localisation }}</option>@endforeach</select>@error('ferme_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-6 mb-3"><label for="procede" class="form-label">Procédé *</label><select id="procede" name="procede" class="form-select @error('procede') is-invalid @enderror" required><option value="">Choisir</option>@foreach (\App\Models\Transformation::PROCEDES as $key => $p)<option value="{{ $key }}" @selected(old('procede', $t->procede) === $key)>{{ $p['label'] }}</option>@endforeach</select>@error('procede')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-6 mb-3"><label for="date_transformation" class="form-label">Date *</label><input id="date_transformation" name="date_transformation" type="date" max="{{ now()->toDateString() }}" class="form-control @error('date_transformation') is-invalid @enderror" value="{{ old('date_transformation', $t->date_transformation?->format('Y-m-d')) }}" required>@error('date_transformation')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-6 mb-3"><label for="quantite_kg" class="form-label">Quantité transformée (kg) *</label><input id="quantite_kg" name="quantite_kg" type="number" step="0.01" min="0.01" class="form-control @error('quantite_kg') is-invalid @enderror" value="{{ old('quantite_kg', $t->quantite_kg) }}" required>@error('quantite_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-12 mb-3"><label for="allegation" class="form-label">Allégation marketing du produit</label><input id="allegation" name="allegation" class="form-control @error('allegation') is-invalid @enderror" value="{{ old('allegation', $t->allegation) }}" maxlength="255" placeholder="Ex. : « Zéro impact carbone », « Produit éco-responsable »"><div class="form-text">L’IA vérifie si cette allégation est cohérente avec l’empreinte réelle (anti-greenwashing).</div>@error('allegation')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    </div>
</div></div></div>

<div class="col-lg-5 mb-4"><div class="card h-100"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0">Empreinte environnementale</h2>
        <x-eco-score :score="$t->eco_score" id="eco-preview" />
    </div>
    <button type="button" id="estimate-btn" class="btn btn-success w-100 mb-3" data-url="{{ route('admin.transformations.estimate') }}"><i class="bi bi-stars" aria-hidden="true"></i> Estimer avec l’IA</button>
    <div id="estimate-status" class="small mb-3" role="status" aria-live="polite"></div>
    <div class="mb-3"><label for="co2_kg" class="form-label">Émissions (kg CO₂e)</label><input id="co2_kg" name="co2_kg" type="number" step="0.01" min="0" class="form-control @error('co2_kg') is-invalid @enderror" value="{{ old('co2_kg', $t->co2_kg) }}">@error('co2_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="mb-3"><label for="eau_litres" class="form-label">Eau (litres)</label><input id="eau_litres" name="eau_litres" type="number" step="0.01" min="0" class="form-control @error('eau_litres') is-invalid @enderror" value="{{ old('eau_litres', $t->eau_litres) }}">@error('eau_litres')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="mb-3"><label for="energie_kwh" class="form-label">Énergie (kWh)</label><input id="energie_kwh" name="energie_kwh" type="number" step="0.01" min="0" class="form-control @error('energie_kwh') is-invalid @enderror" value="{{ old('energie_kwh', $t->energie_kwh) }}">@error('energie_kwh')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="mb-3"><label for="verdict_allegation" class="form-label">Verdict sur l’allégation</label><select id="verdict_allegation" name="verdict_allegation" class="form-select @error('verdict_allegation') is-invalid @enderror"><option value="">—</option>@foreach (\App\Models\Transformation::VERDICTS as $key => $label)<option value="{{ $key }}" @selected(old('verdict_allegation', $t->verdict_allegation) === $key)>{{ $label }}</option>@endforeach</select>@error('verdict_allegation')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="mb-0"><label for="analyse_ia" class="form-label">Analyse</label><textarea id="analyse_ia" name="analyse_ia" rows="5" maxlength="2000" class="form-control @error('analyse_ia') is-invalid @enderror">{{ old('analyse_ia', $t->analyse_ia) }}</textarea><div class="form-text">L’éco-score est calculé automatiquement à partir du CO₂ et de la quantité. Vérifiez les valeurs proposées avant d’enregistrer.</div>@error('analyse_ia')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
</div></div></div>
</div>
<button class="btn btn-primary" @disabled($transformateurs->isEmpty() || $products->isEmpty())>Enregistrer</button>
<a class="btn btn-light" href="{{ $t->exists ? route('admin.transformations.show', $t) : route('admin.transformations.index') }}">Annuler</a>
</form>
@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('transformation-form');
    const field = (name) => form.elements[name];
    const preview = document.getElementById('eco-preview');
    const status = document.getElementById('estimate-status');
    const btn = document.getElementById('estimate-btn');
    const scores = @json(\App\Models\Transformation::ECO_SCORES);
    const colors = {A:'#1e8f4e', B:'#7ac143', C:'#e0b400', D:'#ef8a17', E:'#e63e11'};

    // Aperçu instantané de l'éco-score (même barème que le serveur).
    const refreshScore = () => {
        const co2 = parseFloat(field('co2_kg').value), q = parseFloat(field('quantite_kg').value);
        let score = null;
        if (!isNaN(co2) && q > 0) {
            const i = co2 / q;
            score = Object.keys(scores).find(s => i <= scores[s]) || 'E';
        }
        preview.textContent = score || '?';
        preview.style.background = colors[score] || '#9aa0a6';
        preview.title = score ? 'Éco-score ' + score : 'Empreinte non renseignée';
    };
    ['co2_kg', 'quantite_kg'].forEach(n => field(n).addEventListener('input', refreshScore));

    btn.addEventListener('click', async () => {
        const data = new FormData();
        ['product_id', 'procede', 'quantite_kg', 'transformateur_id', 'ferme_id', 'allegation'].forEach(n => { if (field(n).value) data.append(n, field(n).value); });
        btn.disabled = true;
        status.className = 'small mb-3 text-muted';
        status.textContent = 'Analyse en cours…';
        try {
            const response = await fetch(btn.dataset.url, {method: 'POST', body: data, headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}});
            const json = await response.json();
            if (!response.ok) {
                status.className = 'small mb-3 text-danger';
                status.textContent = json.errors ? Object.values(json.errors).flat().join(' ') : (json.message || 'Estimation impossible.');
                return;
            }
            field('co2_kg').value = json.co2_kg;
            field('eau_litres').value = json.eau_litres;
            field('energie_kwh').value = json.energie_kwh;
            field('verdict_allegation').value = json.verdict_allegation || '';
            field('analyse_ia').value = json.explication + (json.commentaire_allegation ? '\n\nAllégation : ' + json.commentaire_allegation : '');
            refreshScore();
            status.className = 'small mb-3 ' + (json.source === 'ia' ? 'text-success' : 'text-warning');
            status.textContent = (json.source === 'ia' ? 'Estimation proposée par l’IA (Gemini).' : 'IA indisponible : estimation locale à partir des facteurs du procédé.') + ' Éco-score ' + json.eco_score + ' — vérifiez puis enregistrez.';
        } catch (e) {
            status.className = 'small mb-3 text-danger';
            status.textContent = 'Le service d’estimation ne répond pas. Réessayez.';
        } finally {
            btn.disabled = false;
        }
    });
})();
</script>
@endpush
