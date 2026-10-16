# Reconnaissance d’aliments et valeurs de référence

Dans **Administration → Analyses nutritionnelles → Ajouter / Modifier** :

1. Importer une photo JPEG, PNG ou WebP de 2 Mo maximum.
2. Cliquer sur **Identifier l’aliment** : Gemini propose un nom français, une requête anglaise pour USDA et des incertitudes. Il ne fournit aucune valeur nutritionnelle.
3. Vérifier ou corriger le nom de recherche, choisir cru, cuit ou transformé, cocher la confirmation. Aucun produit enregistré n’est nécessaire à cette étape.
4. Rechercher les références USDA et choisir la préparation exacte. Leurs intitulés officiels restent en anglais afin de ne pas masquer des différences de préparation.
5. Appliquer les valeurs, choisir un produit existant ou cocher « Créer un nouveau produit » et renseigner ses champs. Vérifier la correspondance entre le produit et la référence, compléter les valeurs et enregistrer. Le produit et l’analyse sont enregistrés dans une transaction : un formulaire invalide ne crée aucun produit.

La reconnaissance d’une photo ne prouve ni l’espèce, ni la comestibilité, ni une certification ou une empreinte carbone. En cas d’ambiguïté, utiliser une autre photo ou la saisie manuelle. La lecture d’étiquettes reste accessible dans une section repliable.

## Origine des données

Les valeurs pour 100 g de partie comestible proviennent exclusivement de **USDA FoodData Central, SR Legacy**. Les résultats crus et cuits sont filtrés par préparation ; la sélection finale appartient à l’utilisateur. L’état « transformé » nécessite de choisir exactement la préparation décrite. Sans correspondance, aucune valeur n’est inventée.

Les champs absents restent vides. Le sel est calculé depuis le sodium en mg : sodium × 2,5 / 1000, puis arrondi à deux décimales. Cette conversion est affichée. Les valeurs éventuellement corrigées par l’utilisateur ne constituent pas une mesure du produit photographié.

L’identifiant FDC et la description de référence sont conservés en base et affichés côté administration et catalogue public. Un jeton chiffré, valable deux heures et lié à l’administrateur, protège la provenance lors de l’enregistrement. Le rattachement au produit se fait au moment de l’enregistrement, après confirmation. Une analyse de référence déjà enregistrée exige toujours une nouvelle référence pour changer de produit.

## Configuration et quotas

Gemini utilise les variables existantes `GEMINI_API_KEY` et `GEMINI_VISION_MODEL`. Les photos sont envoyées à Google, jamais à USDA ; elles ne sont pas conservées par NutriTrace. Le palier gratuit de Gemini est soumis aux conditions et quotas du compte.

La recherche USDA utilise par défaut `USDA_API_KEY=DEMO_KEY` pour permettre un premier essai : **30 appels/heure et 50 appels/jour par IP**, selon la documentation USDA. Les recherches identiques sont mises en cache pendant 24 heures. Pour une utilisation régulière, obtenir une clé personnelle gratuite sur le site USDA et la placer dans `.env`, puis lancer `php artisan config:clear`. Aucun secret dans Git.

La migration `2026_10_16_000003_add_reference_to_nutritional_analyses.php` ajoute deux champs de provenance sans supprimer les analyses existantes.

Sources :
- https://fdc.nal.usda.gov/api-guide/
- https://www.cuh.nhs.uk/patient-information/no-added-salt-diet/

Les tests automatisés simulent les réponses Gemini/USDA pour vérifier l’identification, l’absence d’enregistrement automatique, la sélection, les champs manquants, la conversion sodium/sel, les quotas et la provenance. Une recherche réelle USDA a également été vérifiée pendant l’intégration.
