# Module Catégorie – Produit

## Entités et relation

Category : name (unique), description (facultative).
Product : name, description, price (TND, deux décimales), origin, category_id.

Category::products() définit hasMany ; Product::category() définit belongsTo. La clé étrangère protège l’intégrité. Une catégorie contenant des produits ne peut pas être supprimée. Les produits doivent d’abord être déplacés ou supprimés.

## Démarrer après un clone

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File database/database.sqlite -ErrorAction SilentlyContinue
php artisan migrate --seed
npm.cmd ci
npm.cmd run build
php artisan serve
```

Le fichier SQLite n’est pas suivi dans Git. Les seeders ne suppriment pas les données existantes et ne dupliquent pas les catégories de démonstration déjà présentes.

## Scénario de validation

1. Ouvrir `/admin/categories` et ajouter une catégorie.
2. Afficher son détail, puis modifier son nom et sa description.
3. Ouvrir `/admin/products` et ajouter un produit en sélectionnant cette catégorie.
4. Afficher sa fiche et modifier son prix ou sa catégorie.
5. Montrer le produit dans `/catalogue` et filtrer par catégorie.
6. Essayer un formulaire invalide : les erreurs sont affichées et les saisies conservées.
7. Essayer de supprimer la catégorie utilisée : un message explique le refus.
8. Supprimer le produit après confirmation, puis supprimer la catégorie vide.

Factories : CategoryFactory et ProductFactory. CatalogSeeder génère cinq catégories et quinze produits de démonstration liés. Les prix et origines sont fictifs.

Tests : `php artisan test` utilise une base SQLite en mémoire pour vérifier les CRUD, la validation, les relations, le filtrage public et les seeders.

L’administration reste accessible sans authentification pendant ce travail local. La gestion User et la protection des routes doivent être intégrées avec le module commun de l’équipe avant une mise en ligne.
# Photos du catalogue

Les 15 photos Wikimedia Commons sont associées aux noms des produits et incluses dans `public/images/products`. Le manifeste `resources/data/product-images.json` conserve les sources, auteurs et licences affichés sur les fiches publiques. Les photos sont illustratives. Les images téléversées dans le CRUD restent prioritaires ; un produit inconnu utilise « Photo à ajouter ».

Pour retélécharger un fichier manquant : `python tools/import_product_photos.py`.
