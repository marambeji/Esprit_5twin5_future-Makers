@extends('admin.layouts.layout')
@section('title', 'Analyse nutritionnelle — NutriTrace')
@section('content')
<h1 class="h3 mb-4">{{ $analysis->exists ? 'Modifier une analyse nutritionnelle' : 'Ajouter une analyse nutritionnelle' }}</h1>
@include('admin.nutrition.food-photo')
<details class="mb-4"><summary>Autre option : lire une étiquette nutritionnelle</summary>
@include('admin.nutrition.label-import')
</details>
<div class="card"><div class="card-body">
<p>Indiquez les valeurs pour <strong>100 g</strong> de produit. Les sucres sont compris dans les glucides.</p>
<form id="nutrition-form" method="POST" action="{{ $analysis->exists ? route('admin.nutrition.update', $analysis) : route('admin.nutrition.store') }}">@csrf @if($analysis->exists) @method('PUT') @endif
<p class="text-muted small">Le produit, la date et les six valeurs nutritionnelles sont obligatoires. Énergie : 0 à 900 kcal ; autres nutriments : 0 à 100 g. Deux décimales maximum. La date ne peut pas être future.</p>
@include('admin.nutrition.product-choice')
<div id="existing-product" @if(old('create_product')) hidden @endif><div class="mb-3"><label for="product_id" class="form-label">Produit</label><select id="product_id" name="product_id" class="form-select @error('product_id') is-invalid @enderror" @required(!old('create_product'))><option value="">Choisir un produit</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected(old('product_id', $analysis->product_id) == $product->id)>{{ $product->name }}</option>@endforeach</select>@error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
</div>
<div class="mb-3"><label for="analysed_at" class="form-label">Date d’analyse</label><input id="analysed_at" name="analysed_at" type="date" value="{{ old('analysed_at', $analysis->analysed_at?->format('Y-m-d')) }}" max="{{ today()->format('Y-m-d') }}" class="form-control @error('analysed_at') is-invalid @enderror" required>@error('analysed_at')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="row">@foreach(\App\Models\NutritionalAnalysis::NUTRIENTS as $field => $label)<div class="col-md-6 mb-3"><label for="{{ $field }}" class="form-label">{{ $label }}</label><input id="{{ $field }}" name="{{ $field }}" type="number" min="0" max="{{ $field === 'energy_kcal' ? 900 : 100 }}" step="0.01" value="{{ old($field, $analysis->$field) }}" class="form-control @error($field) is-invalid @enderror" required>@error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror</div>@endforeach</div>
<div class="mb-3"><label for="source" class="form-label">Source ou laboratoire (facultatif)</label><input id="source" name="source" maxlength="255" value="{{ old('source', $analysis->source) }}" class="form-control @error('source') is-invalid @enderror">@error('source')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label for="notes" class="form-label">Notes (facultatif)</label><textarea id="notes" name="notes" maxlength="2000" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $analysis->notes) }}</textarea>@error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="form-check mb-4"><input type="hidden" name="is_demo" value="0"><input type="checkbox" name="is_demo" id="is_demo" value="1" class="form-check-input" @checked(old('is_demo', $analysis->is_demo))><label for="is_demo" class="form-check-label">Données fictives de démonstration</label>@error('is_demo')<p class="text-danger">{{ $message }}</p>@enderror</div>
@error('reference_token')<div class="alert alert-danger">{{ $message }}</div>@enderror
<input type="hidden" name="clear_reference" id="clear-reference" value="{{ old('clear_reference', '0') }}">
<input type="hidden" name="reference_token" id="reference-token" value="{{ old('reference_token') }}">
<input type="hidden" name="ai_imported" id="ai-imported" value="{{ old('ai_imported', '0') }}">
<div id="ai-review" class="alert alert-warning" @if(!old('ai_imported')) hidden @endif>
    <label><input type="checkbox" id="ai-reviewed" name="ai_reviewed" value="1" @required(old('ai_imported'))> J’ai vérifié les valeurs proposées par l’IA avec la source ou l’étiquette et confirmé qu’elles correspondent au produit choisi ou créé.</label>
    @error('ai_reviewed')<p class="text-danger">{{ $message }}</p>@enderror
</div>
<button class="btn btn-primary">Enregistrer</button> <a class="btn btn-outline-secondary" href="{{ route('admin.nutrition.index') }}">Annuler</a>
</form></div></div>
@include('admin.nutrition.validation')
@endsection
