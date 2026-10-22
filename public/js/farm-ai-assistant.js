(() => {
    const button = document.querySelector('[data-ai-generate]');
    const panel = document.querySelector('[data-farm-ai]');
    const form = button?.closest('form');
    const description = form?.querySelector('#description');
    if (!button || !panel || !form || !description) return;

    const status = panel.querySelector('[data-ai-status]');
    const preview = panel.querySelector('[data-ai-preview]');
    const previewText = panel.querySelector('[data-ai-preview-text]');
    let proposedText = '';
    let previousText = description.value;

    button.addEventListener('click', async () => {
        const nom = form.querySelector('#nom')?.value.trim() ?? '';
        const localisation = form.querySelector('#localisation')?.value.trim() ?? '';
        if (!nom || !localisation) {
            status.textContent = 'Renseignez le nom et la localisation de la ferme avant de générer sa description.';
            status.className = 'small mb-2 text-danger';
            return;
        }

        previousText = description.value;
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        preview.hidden = true;
        status.textContent = 'Génération de la description en cours…';
        status.className = 'small mb-2 text-muted';

        try {
            const response = await fetch(panel.dataset.endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]')?.value ?? '',
                },
                body: JSON.stringify({
                    nom,
                    localisation,
                    superficie: form.querySelector('#superficie')?.value ?? '',
                    draft: description.value,
                    mode: 'generate',
                }),
            });
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(response.status === 429
                    ? 'Limite de génération atteinte. Réessayez dans une minute.'
                    : payload.message || 'La description n’a pas pu être générée. Votre texte est conservé.');
            }
            if (typeof payload.description !== 'string' || !payload.description.trim()) {
                throw new Error('Aucune description exploitable n’a été reçue. Votre texte est conservé.');
            }

            proposedText = payload.description;
            previewText.textContent = proposedText;
            preview.hidden = false;
            status.textContent = 'Suggestion prête. Votre description ne sera remplacée qu’après confirmation.';
            status.className = 'small mb-2 text-success';
        } catch (error) {
            status.textContent = error.message || 'Erreur lors de la génération. Votre texte est conservé.';
            status.className = 'small mb-2 text-danger';
        } finally {
            button.disabled = false;
            button.removeAttribute('aria-busy');
        }
    });

    panel.querySelector('[data-ai-use]').addEventListener('click', () => {
        description.value = proposedText;
        description.dispatchEvent(new Event('input', { bubbles: true }));
        status.textContent = 'Description remplacée par la suggestion.';
        status.className = 'small mb-2 text-success';
    });

    panel.querySelector('[data-ai-cancel]').addEventListener('click', () => {
        description.value = previousText;
        description.dispatchEvent(new Event('input', { bubbles: true }));
        preview.hidden = true;
        status.textContent = 'Suggestion annulée. Votre description précédente est conservée.';
        status.className = 'small mb-2 text-muted';
    });
})();
