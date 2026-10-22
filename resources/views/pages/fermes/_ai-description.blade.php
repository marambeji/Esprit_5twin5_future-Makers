<div class="mt-2" data-farm-ai data-endpoint="{{ $endpoint }}">
    <p class="small mb-2 text-muted" data-ai-status role="status" aria-live="polite"></p>
    <section class="border rounded p-3 mb-3" data-ai-preview hidden aria-label="Suggestion de description à vérifier">
        <p class="mb-2" data-ai-preview-text style="white-space:pre-line;overflow-wrap:anywhere;"></p>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-primary" data-ai-use>Utiliser cette description</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-ai-cancel>Annuler</button>
        </div>
    </section>
</div>
<script src="{{ asset('js/farm-ai-assistant.js') }}" defer></script>
