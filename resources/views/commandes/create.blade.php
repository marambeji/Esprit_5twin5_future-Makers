@extends('layouts.layout')
@section('title', 'Passer une commande — NutriTrace')
@section('body_class', 'main-layout inner_page')
@section('content')
<section class="container py-5">
    <a class="text-success d-inline-block mb-4" href="{{ route('commandes.index') }}">← Mes commandes</a>
    <h1 class="h2 mb-4">Passer une commande</h1>
    <div class="mb-4" style="max-width:600px">
        <label for="ia_texte">Assistant IA : décrivez votre commande</label>
        <textarea id="ia_texte" class="form-control mb-2" rows="2" maxlength="500" placeholder="Ex : je voudrais 5 huiles de sésame livrées au 10 rue de la Liberté, Tunis"></textarea>
        <button type="button" id="ia_btn" class="btn btn-outline-success">Remplir avec l’IA</button>
        <span id="ia_msg" class="ml-2" role="status"></span>
    </div>
    <form method="POST" action="{{ route('commandes.store') }}" style="max-width:600px">
        @csrf
        <div class="form-group mb-3"><label for="product_id">Produit *</label>
            <select id="product_id" name="product_id" class="form-control @error('product_id') is-invalid @enderror" required>
                <option value="">Choisir…</option>
                @foreach ($products as $product)<option value="{{ $product->id }}" @selected(old('product_id', $selected) == $product->id)>{{ $product->name }} — {{ number_format($product->price, 2, ',', ' ') }} TND</option>@endforeach
            </select>@error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="form-group mb-3"><label for="quantite">Quantité *</label><input id="quantite" name="quantite" type="number" min="1" max="1000" class="form-control @error('quantite') is-invalid @enderror" value="{{ old('quantite', 1) }}" required>@error('quantite')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="form-group mb-3"><label for="adresse_livraison">Adresse de livraison *</label><input id="adresse_livraison" name="adresse_livraison" maxlength="255" class="form-control @error('adresse_livraison') is-invalid @enderror" value="{{ old('adresse_livraison') }}" required>@error('adresse_livraison')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="form-group mb-3"><label for="telephone">Téléphone *</label><input id="telephone" name="telephone" maxlength="30" class="form-control @error('telephone') is-invalid @enderror" value="{{ old('telephone', auth('web')->user()->phone) }}" required>@error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <button class="btn btn-success">Envoyer la commande</button>
    </form>
</section>
<script>
document.getElementById('ia_btn').addEventListener('click', async () => {
    const msg = document.getElementById('ia_msg'), btn = document.getElementById('ia_btn');
    msg.textContent = 'Analyse en cours…'; btn.disabled = true;
    try {
        const r = await fetch('{{ route('commandes.assist') }}', {method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}, body: JSON.stringify({texte: document.getElementById('ia_texte').value})});
        const d = await r.json();
        if (!r.ok) { msg.textContent = d.message || 'Assistant indisponible.'; return; }
        document.getElementById('product_id').value = d.product_id;
        document.getElementById('quantite').value = d.quantite;
        if (d.adresse) document.getElementById('adresse_livraison').value = d.adresse;
        msg.textContent = 'Formulaire rempli : vérifiez puis envoyez.';
    } catch (e) { msg.textContent = 'Assistant indisponible.'; } finally { btn.disabled = false; }
});
</script>
@endsection
