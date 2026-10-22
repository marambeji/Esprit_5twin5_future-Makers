<dialog id="nutrition-validation" aria-labelledby="nutrition-validation-title" style="width:min(560px, calc(100% - 32px));border:0;border-radius:12px;padding:24px;color:var(--bs-body-color);background:var(--bs-body-bg)">
    <h2 id="nutrition-validation-title" class="h4">Vérifiez votre formulaire</h2>
    <p>Complétez ou corrigez les champs suivants avant l’enregistrement :</p>
    <ul id="nutrition-validation-errors" style="max-height:45vh;overflow-y:auto"></ul>
    <button id="nutrition-validation-close" type="button" class="btn btn-primary">Corriger le formulaire</button>
</dialog>
@push('styles')
<style>#nutrition-validation::backdrop { background: rgba(0, 0, 0, .45); }</style>
@endpush
@push('scripts')
<script>
(() => {
    const form = document.getElementById('nutrition-form');
    const dialog = document.getElementById('nutrition-validation');
    const list = document.getElementById('nutrition-validation-errors');
    let firstInvalid = null;
    // Keep native validation available when JavaScript is disabled.
    form.noValidate = true;
    const fields = () => Array.from(form.querySelectorAll('input, select, textarea')).filter(field => !field.matches(':disabled') && field.type !== 'hidden');
    const label = field => field.id === 'ai-reviewed' ? 'Vérification des propositions IA' : (field.labels?.[0]?.textContent.trim() || field.name);
    const updateRules = () => {
        const active = fields();
        active.forEach(field => field.setCustomValidity(''));
        active.forEach(field => {
            if (field.required && field.type !== 'checkbox' && !field.value.trim()) {
                field.setCustomValidity('Ce champ est obligatoire.');
            } else if (field.type === 'number' && field.value && !/^\d+(?:[.,]\d{1,2})?$/.test(field.value)) {
                field.setCustomValidity('Saisissez un nombre positif ou nul, avec deux décimales maximum.');
            }
        });
        const value = id => {
            const field = document.getElementById(id);
            return field.value !== '' && field.validity.valid ? Number(field.value) : null;
        };
        const sugars = value('sugars'), carbs = value('carbohydrates');
        if (sugars !== null && carbs !== null && sugars > carbs) {
            document.getElementById('sugars').setCustomValidity('Les sucres ne peuvent pas dépasser les glucides.');
        }
        const macros = ['protein', 'carbohydrates', 'fat', 'salt'].map(value);
        if (macros.every(number => number !== null) && macros.reduce((a, b) => a + b, 0) > 100.01) {
            document.getElementById('protein').setCustomValidity('La somme protéines, glucides, lipides et sel ne doit pas dépasser 100 g. Les sucres sont déjà inclus dans les glucides.');
        }
        const review = document.getElementById('ai-reviewed');
        const needsReview = document.getElementById('ai-imported').value === '1' || document.getElementById('reference-token').value !== '' || document.getElementById('clear-reference').value === '1';
        review.required = needsReview;
        if (needsReview) document.getElementById('ai-review').hidden = false;
        if (needsReview && !review.checked) review.setCustomValidity('Confirmez la vérification des valeurs proposées par l’IA.');
        return active;
    };
    const message = field => {
        const validity = field.validity;
        if (validity.customError) return field.validationMessage;
        if (validity.valueMissing) return 'Ce champ est obligatoire.';
        if (validity.badInput) return 'Saisissez un nombre valide.';
        if (validity.rangeOverflow) return field.type === 'date' ? 'La date ne peut pas être dans le futur.' : `La valeur maximale est ${field.max}.`;
        if (validity.rangeUnderflow) return `La valeur minimale est ${field.min}.`;
        if (validity.stepMismatch) return 'Deux décimales maximum sont autorisées.';
        if (validity.tooLong) return `Maximum ${field.maxLength} caractères.`;
        return 'Vérifiez la valeur saisie.';
    };
    ['input', 'change'].forEach(event => form.addEventListener(event, () => {
        updateRules().forEach(field => {
            if (field.validity.valid) { field.classList.remove('is-invalid'); field.removeAttribute('aria-invalid'); }
        });
    }));
    form.addEventListener('submit', event => {
        const invalid = updateRules().filter(field => !field.validity.valid);
        if (!invalid.length) return;
        event.preventDefault();
        firstInvalid = invalid[0];
        list.replaceChildren();
        invalid.forEach(field => {
            field.classList.add('is-invalid'); field.setAttribute('aria-invalid', 'true');
            const item = document.createElement('li'); item.textContent = `${label(field)} : ${message(field)}`; list.append(item);
        });
        if (typeof dialog.showModal === 'function') dialog.showModal();
        else { window.alert(list.textContent); firstInvalid.focus(); }
    });
    document.getElementById('nutrition-validation-close').addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => {
        firstInvalid?.scrollIntoView({block: 'center', behavior: 'smooth'});
        firstInvalid?.focus({preventScroll: true});
    });
})();
</script>
@endpush
