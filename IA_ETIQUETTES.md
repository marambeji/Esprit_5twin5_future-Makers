# Lecture d’étiquettes nutritionnelles par IA

Dans l’administration, ouvrir **Analyses nutritionnelles → Ajouter** (ou Modifier), choisir une photo JPEG, PNG ou WebP de 2 Mo maximum et cliquer sur **Lire la photo avec l’IA**. Le même formulaire est accessible depuis la fiche d’un produit.

La photo est envoyée à l’API Google Gemini avec vision et sortie JSON structurée. Elle n’est pas sauvegardée par NutriTrace. Cela ne constitue pas une garantie d’absence de rétention chez le fournisseur. Aucun compte utilisateur ni autre donnée du catalogue n’est envoyé.

Les propositions apparaissent dans un tableau avec les incertitudes. **Appliquer au formulaire pour correction** remplace les six champs, après confirmation s’ils sont déjà remplis. Les valeurs absentes restent vides. Vérifier et corriger les champs, cocher la confirmation, puis enregistrer normalement. L’extraction ne crée ni ne modifie aucune ligne en base.

## Activation

Dans `.env`, renseigner une clé API personnelle et l’identifiant d’un modèle disponible sur le compte prenant en charge les images et les sorties structurées :

```dotenv
GEMINI_API_KEY=
GEMINI_VISION_MODEL=gemini-2.5-flash
```

Puis lancer `php artisan config:clear`. Ne pas publier la clé sur Git. Sans configuration, le bouton est désactivé et la saisie manuelle reste disponible. Les appels au fournisseur peuvent être facturés ; la route est limitée à cinq lectures par minute et par administrateur.

## Limites

Seule une colonne explicitement pour 100 g peut alimenter le formulaire actuel. Pas de conversion depuis 100 ml, une portion, les kJ ou le sodium. Les valeurs illisibles ou ambiguës ne sont pas estimées. Une lecture IA peut se tromper : vérifier la photo reste obligatoire. Les validations du CRUD (bornes, sucres/glucides, somme des nutriments) restent actives.

## Code et validation

- `app/Services/NutritionLabelReader.php` : appel au modèle et validation du résultat.
- `app/Http/Controllers/NutritionLabelController.php` : validation de la photo et erreurs en français.
- `resources/views/admin/nutrition/label-import.blade.php` : aperçu, propositions, application manuelle.
- `tests/Feature/NutritionLabelTest.php` : tests avec API simulée. Ils ne prouvent pas la qualité de reconnaissance d’un modèle réel.

Une vérification réelle avec une étiquette nette doit être effectuée après configuration de la clé et du modèle.

Documentation officielle : https://ai.google.dev/gemini-api/docs/image-understanding et https://ai.google.dev/gemini-api/docs/structured-output

Le palier gratuit de Gemini est soumis aux quotas du compte et du modèle ; les données envoyées peuvent être utilisées pour améliorer les produits Google. Ne pas activer la facturation pour rester sur un projet gratuit.
