<div class="card"><div class="card-body">
    <h2 class="h5">Lire une étiquette avec l’IA</h2>
    <p>Importez une photo nette du tableau nutritionnel pour <strong>100 g</strong>. La photo sera transmise à Google Gemini pour lecture ; elle ne sera pas conservée dans NutriTrace. Vérifiez les propositions avant de les appliquer au formulaire.</p>
    @if(!config('nutrition_ai.key') || !config('nutrition_ai.model'))
        <div class="alert alert-warning">Lecture IA indisponible : configurez GEMINI_API_KEY et GEMINI_VISION_MODEL dans .env. La saisie manuelle reste disponible.</div>
    @endif
    <form id="label-import" data-url="{{ route('admin.nutrition.read-label') }}">
        <label for="label-photo" class="form-label">Photo de l’étiquette (JPEG, PNG ou WebP, 2 Mo maximum)</label>
        <input id="label-photo" type="file" class="form-control mb-3" accept="image/jpeg,image/png,image/webp" required>
        <button class="btn btn-outline-primary" id="label-read" type="submit" @disabled(!config('nutrition_ai.key') || !config('nutrition_ai.model'))>Lire la photo avec l’IA</button>
    </form>
    <img id="label-preview" hidden alt="Photo de l’étiquette à vérifier" class="img-fluid mt-3" style="max-height:320px">
    <p id="label-status" class="mt-3" role="status" aria-live="polite"></p>
    <div id="label-result" hidden>
        <h3 class="h6">Propositions IA — à vérifier</h3>
        <ul id="label-warnings" class="text-warning"></ul>
        <div class="table-responsive"><table class="table"><thead><tr><th>Nutriment pour 100 g</th><th>Valeur proposée</th></tr></thead><tbody id="label-values"></tbody></table></div>
        <p>Les champs non lus resteront vides. Appliquer remplace les six valeurs du formulaire, sans enregistrer l’analyse.</p>
        <button id="label-apply" type="button" class="btn btn-primary">Appliquer au formulaire pour correction</button>
    </div>
</div></div>
@push('scripts')
<script>
(() => {
    const form = document.getElementById('label-import');
    const file = document.getElementById('label-photo');
    const status = document.getElementById('label-status');
    const result = document.getElementById('label-result');
    const read = document.getElementById('label-read');
    const apply = document.getElementById('label-apply');
    const labels = @json(\App\Models\NutritionalAnalysis::NUTRIENTS);
    let proposal = null, previewUrl = null;
    file.addEventListener('change', () => {
        proposal = null; result.hidden = true; status.textContent = '';
        const preview = document.getElementById('label-preview');
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        preview.hidden = true;
        if (file.files[0] && ['image/jpeg','image/png','image/webp'].includes(file.files[0].type) && file.files[0].size <= 2097152) {
            previewUrl = URL.createObjectURL(file.files[0]); preview.src = previewUrl; preview.hidden = false;
        }
    });
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const photo = file.files[0];
        if (!photo || photo.size > 2097152 || !['image/jpeg','image/png','image/webp'].includes(photo.type)) {
            status.textContent = 'Choisissez une photo JPEG, PNG ou WebP de 2 Mo maximum.'; return;
        }
        read.disabled = true; file.disabled = true; result.hidden = true; proposal = null;
        status.textContent = 'Lecture en cours… Cela peut prendre quelques secondes.';
        const data = new FormData(); data.append('photo', photo);
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 60000);
        try {
            const response = await fetch(form.dataset.url, {method: 'POST', body: data, signal: controller.signal, headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}});
            const body = await response.json();
            if (!response.ok) throw new Error(response.status === 429 ? 'Trop de lectures. Réessayez dans une minute.' : response.status === 419 ? 'Session expirée. Rechargez la page.' : (Object.values(body.errors || {}).flat()[0] || body.message || 'Lecture impossible.'));
            proposal = body.values;
            const rows = document.getElementById('label-values'); rows.replaceChildren();
            for (const [key, label] of Object.entries(labels)) {
                const tr = document.createElement('tr');
                for (const text of [label, proposal[key] === null ? 'Non lu — à compléter' : String(proposal[key])]) {
                    const td = document.createElement('td'); td.textContent = text; tr.append(td);
                }
                rows.append(tr);
            }
            const warnings = document.getElementById('label-warnings'); warnings.replaceChildren();
            for (const warning of body.warnings) { const li = document.createElement('li'); li.textContent = warning; warnings.append(li); }
            apply.disabled = body.basis !== '100g' || !Object.values(proposal).some(value => value !== null);
            result.hidden = false;
            status.textContent = 'Lecture terminée. Comparez les propositions avec la photo.';
        } catch (error) {
            status.textContent = error.name === 'AbortError' ? 'Le délai de lecture est dépassé. Réessayez.' : (error instanceof SyntaxError ? 'Réponse indisponible. Vérifiez votre connexion et votre session.' : error.message);
        } finally { clearTimeout(timeout); read.disabled = false; file.disabled = false; }
    });
    apply.addEventListener('click', () => {
        if (!proposal || apply.disabled) return;
        if (Object.keys(labels).some(key => document.getElementById(key).value !== '') && !confirm('Remplacer les valeurs nutritionnelles actuelles par les propositions IA ?')) return;
        for (const key of Object.keys(labels)) document.getElementById(key).value = proposal[key] ?? '';
        document.getElementById('reference-token').value = '';
        document.getElementById('clear-reference').value = '1';
        document.getElementById('source').value = 'Étiquette du produit — lecture IA vérifiée par l’utilisateur';
        document.getElementById('ai-imported').value = '1';
        const review = document.getElementById('ai-review'); review.hidden = false;
        const checkbox = document.getElementById('ai-reviewed'); checkbox.required = true; checkbox.checked = false;
        status.textContent = 'Propositions appliquées. Corrigez les champs, confirmez votre vérification puis cliquez sur Enregistrer.';
        document.getElementById('energy_kcal').focus();
    });
})();
</script>
@endpush
