<div class="card"><div class="card-body" id="food-photo-panel" data-identify="{{ route('admin.nutrition.identify') }}" data-search="{{ route('admin.nutrition.references') }}">
    <h2 class="h5">Reconnaître un aliment avec l’IA</h2>
    <p>1. Importez une photo. 2. Confirmez l’aliment et son état. 3. Choisissez une référence nutritionnelle.</p>
    <p class="small text-muted">La photo est envoyée à Google Gemini pour identification et n’est pas conservée par NutriTrace. La composition provient de la base USDA, jamais de la photo. Aucune certification ni empreinte carbone n’est déduite.</p>
    <label for="food-photo" class="form-label">Photo de l’aliment (JPEG, PNG, WebP, 2 Mo maximum)</label>
    <input type="file" id="food-photo" accept="image/jpeg,image/png,image/webp" class="form-control mb-3">
    <button type="button" id="food-identify" class="btn btn-primary" @disabled(!config('nutrition_ai.key'))>Identifier l’aliment</button>
    <img id="food-preview" alt="Aliment à identifier" hidden class="img-fluid mt-3" style="max-height:260px">
    <p id="food-status" role="status" aria-live="polite" class="mt-3"></p>
    <div id="food-confirm" hidden>
        <p id="food-name" class="fw-bold"></p><p id="food-warning"></p>
        <label for="food-query" class="form-label">Nom de recherche proposé en anglais (modifiable pour la base USDA)</label>
        <input id="food-query" class="form-control mb-3" maxlength="150">
        <label for="food-state" class="form-label">État du produit à confirmer</label>
        <select id="food-state" class="form-select mb-3"><option value="">Choisir l’état</option><option value="raw">Cru</option><option value="cooked">Cuit</option><option value="processed">Transformé (sélectionner ensuite la préparation exacte)</option></select>
        <label class="d-block mb-3"><input type="checkbox" id="food-confirmed"> Je confirme l’aliment, son état .</label>
        <button type="button" id="food-search" class="btn btn-outline-primary">Rechercher les valeurs de référence</button>
    </div>
    <div id="food-results" class="mt-3"></div>
</div></div>
@push('scripts')
<script>
(() => {
    const panel = document.getElementById('food-photo-panel');
    const el = id => document.getElementById(id);
    const status = el('food-status'), results = el('food-results');
    const labels = @json(\App\Models\NutritionalAnalysis::NUTRIENTS);
    let previewUrl, busy = false;
    async function request(url, data) {
        const controller = new AbortController(), timer = setTimeout(() => controller.abort(), 60000);
        try {
            const response = await fetch(url, {method:'POST', body:data, signal:controller.signal, headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}});
            const body = await response.json();
            if (!response.ok) throw new Error(response.status === 429 ? 'Trop de demandes. Réessayez dans une minute.' : (Object.values(body.errors || {}).flat()[0] || body.message || 'Service indisponible.'));
            return body;
        } finally { clearTimeout(timer); }
    }
    function errorText(error) { return error.name === 'AbortError' ? 'Le service met trop de temps à répondre. Réessayez.' : error instanceof SyntaxError ? 'Session expirée ou service indisponible. Rechargez la page.' : error.message; }
    function clearResults() { results.replaceChildren(); el('food-confirmed').checked = false; }
    ['food-query','food-state'].forEach(id => el(id).addEventListener('change',clearResults));
    el('food-photo').addEventListener('change', () => {
        clearResults(); el('food-confirm').hidden = true; status.textContent = '';
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        const photo = el('food-photo').files[0]; el('food-preview').hidden = true;
        if (photo && ['image/jpeg','image/png','image/webp'].includes(photo.type) && photo.size <= 2097152) {
            previewUrl = URL.createObjectURL(photo); el('food-preview').src = previewUrl; el('food-preview').hidden = false;
        }
    });
    el('food-identify').addEventListener('click', async () => {
        if (busy) return;
        const photo = el('food-photo').files[0];
        if (!photo || photo.size > 2097152 || !['image/jpeg','image/png','image/webp'].includes(photo.type)) { status.textContent = 'Choisissez une image JPEG, PNG ou WebP de 2 Mo maximum.'; return; }
        busy = true; el('food-photo').disabled = true; el('food-identify').disabled = true;
        clearResults(); el('food-confirm').hidden = true; status.textContent = 'Identification en cours…';
        try {
            const data = new FormData(); data.append('photo',photo);
            const body = await request(panel.dataset.identify,data);
            el('food-name').textContent = 'Identification proposée : '+body.name_fr;
            el('food-query').value = body.search_en;
            if (!el('new-product-name').value) el('new-product-name').value = body.name_fr; el('food-warning').textContent = body.warning_fr; el('food-state').value = '';
            el('food-confirm').hidden = !body.search_en;
            status.textContent = body.search_en ? 'Vérifiez l’identification et choisissez l’état du produit.' : (body.warning_fr || 'Identification incertaine : utilisez une autre photo.');
        } catch(error) { status.textContent = errorText(error); }
        finally { busy = false; el('food-photo').disabled = false; el('food-identify').disabled = false; }
    });
    el('food-search').addEventListener('click', async () => {
        if (busy) return;
        const query = el('food-query').value.trim(), state = el('food-state').value;
        if (!query || !state || !el('food-confirmed').checked) { status.textContent = 'Confirmez l’aliment et choisissez son état.'; return; }
        busy = true; el('food-search').disabled = true; results.replaceChildren(); status.textContent = 'Recherche dans USDA FoodData Central…';
        try {
            const data = new FormData(); Object.entries({query,state,confirmed:'1'}).forEach(([k,v])=>data.append(k,v));
            const body = await request(panel.dataset.search,data);
            if (query !== el('food-query').value.trim() || state !== el('food-state').value) { status.textContent = 'La sélection a changé. Relancez la recherche.'; return; }
            status.textContent = body.foods.length ? 'Choisissez la préparation exacte. Les noms des références USDA sont affichés en anglais.' : 'Aucune référence adaptée. Précisez le nom de recherche ou utilisez la saisie manuelle.';
            body.foods.forEach(food => {
                const card = document.createElement('div'); card.className = 'border rounded p-3 mb-3';
                const title = document.createElement('h3'); title.className = 'h6'; title.textContent = food.description;
                const link = document.createElement('a'); link.href = food.url; link.target = '_blank'; link.rel = 'noopener noreferrer'; link.textContent = 'Consulter la source USDA';
                const note = document.createElement('p'); note.textContent = food.note;
                const values = document.createElement('p'); values.textContent = Object.entries(labels).map(([key,label])=>label+' : '+(food.values[key] === null ? 'absent — à compléter' : food.values[key])).join(' ; ');
                const button = document.createElement('button'); button.type='button'; button.className='btn btn-outline-primary'; button.textContent='Choisir cette référence et remplir le formulaire';
                button.addEventListener('click',()=>{
                    if (query !== el('food-query').value.trim() || state !== el('food-state').value || !el('food-confirmed').checked) { status.textContent='Confirmez à nouveau votre sélection et relancez la recherche.'; return; }
                    if (!confirm('Cette référence correspond-elle bien à votre produit et à sa préparation ? Les valeurs actuelles du formulaire seront remplacées.')) return;
                    Object.keys(labels).forEach(key=>el(key).value=food.values[key] === null ? '' : Math.round(food.values[key]*100)/100);
                    el('reference-token').value=food.token; el('clear-reference').value='0';
                    el('source').value='USDA FoodData Central — FDC '+food.id+' (valeurs de référence)';
                    el('ai-imported').value='1'; el('ai-review').hidden=false; el('ai-reviewed').required=true; el('ai-reviewed').checked=false;
                    status.textContent='Valeurs de référence appliquées. Vérifiez et complétez les champs, puis confirmez avant d’enregistrer.';
                    el('product-choice').scrollIntoView({behavior:'smooth', block:'center'});
                });
                card.append(title,link,note,values,button); results.append(card);
            });
        } catch(error) { status.textContent=errorText(error); }
        finally { busy=false; el('food-search').disabled=false; }
    });
})();
</script>
@endpush
