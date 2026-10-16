# Module Produit – Analyse nutritionnelle

Un produit possède plusieurs analyses (`Product::nutritionalAnalyses`, hasMany) ; une analyse appartient à un produit (`NutritionalAnalysis::product`, belongsTo). Les catégories existantes restent disponibles et les données des autres modules sont préservées.

## Démarrer

Exécuter `php artisan migrate` puis `php artisan db:seed --class=NutritionalAnalysisSeeder`. Le seeder utilise une Factory avec relation et ajoute une analyse fictive aux produits sans analyse, uniquement en local ou en test. Les valeurs de démonstration sont explicitement signalées sur les pages publiques et administrateur et ne sont pas des mesures réelles.

## Démonstration

- Connexion administrateur puis menu Analyses nutritionnelles : `/admin/analyses-nutritionnelles`.
- Ajout : sélectionner un produit, date d’analyse et valeurs pour 100 g ; source, notes et indicateur de démonstration.
- Affichage : liste filtrable par produit (jointure SQL avec products), fiche, modification préremplie et suppression avec confirmation.
- La fiche produit administrateur permet d’ajouter une analyse avec le produit présélectionné et de consulter son historique.
- La fiche publique `/catalogue/{slug}` présente les mêmes analyses, de la plus récente à la plus ancienne, cinq par page, sans action d’écriture.
- Un produit associé à une analyse ne peut pas être supprimé tant que les analyses ne sont pas supprimées ou réaffectées. La clé étrangère renforce cette protection.

## Validation

Date valide, non future ; produit existant ; valeurs numériques positives ou nulles, deux décimales au maximum ; énergie jusqu’à 900 kcal/100 g ; nutriments jusqu’à 100 g/100 g ; sucres inférieurs ou égaux aux glucides ; somme protéines + glucides + lipides + sel au maximum 100 g (tolérance d’arrondi 0,01 g). Les sucres ne sont pas comptés deux fois. Source limitée à 255 caractères, notes à 2000. Ces contrôles ne constituent pas une certification des valeurs saisies.

Les vues utilisent Mazer et Agropro avec héritage Blade, partial partagé, CSRF, old() et @error. Toutes les routes d’écriture sont réservées aux administrateurs. Tests : `php artisan test --filter=NutritionalAnalysisTest`.
