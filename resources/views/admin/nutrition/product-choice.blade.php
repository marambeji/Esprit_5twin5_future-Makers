<div id="product-choice" class="border rounded p-3 mb-3">
    <h2 class="h5">À quel produit rattacher cette analyse ?</h2>
    <p>Choisissez un produit existant ci-dessous ou créez-le ici. Le produit et son analyse seront enregistrés ensemble.</p>
    <input type="hidden" name="create_product" value="0">
    <label><input id="create-product" name="create_product" type="checkbox" value="1" @checked(old('create_product'))> Créer un nouveau produit</label>
    @error('create_product')<p class="text-danger">{{ $message }}</p>@enderror
    <fieldset id="new-product-fields" class="mt-3" @disabled(!old('create_product')) @if(!old('create_product')) hidden @endif>
        @if($categories->isEmpty())<div class="alert alert-warning">Une catégorie est nécessaire. <a href="{{ route('admin.categories.create') }}" target="_blank" rel="noopener">Créer une catégorie dans un nouvel onglet</a>, puis rechargez ce formulaire.</div>@endif
        <div class="mb-3"><label for="new-product-name" class="form-label">Nom du produit</label><input id="new-product-name" name="new_product[name]" class="form-control" value="{{ old('new_product.name') }}" maxlength="150" required>@error('new_product.name')<p class="text-danger">{{ $message }}</p>@enderror</div>
        <div class="mb-3"><label for="new-product-description" class="form-label">Description</label><textarea id="new-product-description" name="new_product[description]" class="form-control" maxlength="5000" required>{{ old('new_product.description') }}</textarea>@error('new_product.description')<p class="text-danger">{{ $message }}</p>@enderror</div>
        <div class="row">
            <div class="col-md-6 mb-3"><label for="new-product-price" class="form-label">Prix (TND)</label><input id="new-product-price" name="new_product[price]" type="number" min="0" max="99999999.99" step="0.01" class="form-control" value="{{ old('new_product.price') }}" required>@error('new_product.price')<p class="text-danger">{{ $message }}</p>@enderror</div>
            <div class="col-md-6 mb-3"><label for="new-product-origin" class="form-label">Origine</label><input id="new-product-origin" name="new_product[origin]" class="form-control" maxlength="150" value="{{ old('new_product.origin') }}" required>@error('new_product.origin')<p class="text-danger">{{ $message }}</p>@enderror</div>
        </div>
        <div class="mb-3"><label for="new-product-category" class="form-label">Catégorie</label><select id="new-product-category" name="new_product[category_id]" class="form-select" required><option value="">Choisir une catégorie</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('new_product.category_id') == $category->id)>{{ $category->name }}</option>@endforeach</select>@error('new_product.category_id')<p class="text-danger">{{ $message }}</p>@enderror</div>
        <p class="small text-muted">Seul le nom peut être proposé par l’IA. Renseignez vous-même le prix, l’origine et la description. La photo de reconnaissance n’est pas automatiquement publiée.</p>
    </fieldset>
</div>
@push('scripts')
<script>
(() => {
    const toggle = document.getElementById('create-product');
    function updateChoice() {
        const fields = document.getElementById('new-product-fields');
        fields.disabled = !toggle.checked; fields.hidden = !toggle.checked;
        document.getElementById('existing-product').hidden = toggle.checked;
        document.getElementById('product_id').required = !toggle.checked;
        document.getElementById('product_id').disabled = toggle.checked;
        const review = document.getElementById('ai-reviewed');
        if (review) review.checked = false;
    }
    toggle.addEventListener('change', updateChoice);
    document.getElementById('product_id').addEventListener('change', () => {
        document.getElementById('ai-reviewed').checked = false;
    });
    updateChoice();
})();
</script>
@endpush
