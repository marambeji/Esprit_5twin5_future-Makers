# Intégration Agropro dans Nutritrace

## Back Office Mazer
Mazer 2.3.1 remplace StarAdmin. Source officielle : https://github.com/zuramai/mazer/releases/tag/v2.3.1.
Les assets sont dans resources/mazer, compilés avec Vite. Le layout et les partials restent dans resources/views/admin. Le Front Office Agropro reste indépendant.
URL : http://127.0.0.1:8000/admin/dashboard.
Le menu mobile et le mode sombre utilisent les scripts officiels Mazer. Les contenus sont adaptés à NutriTrace. Les CRUD et l’authentification restent à implémenter.

Le template de l’archive `agropro_html.zip` est intégré selon l’annexe du TP.

- `resources/assets/` : CSS, JavaScript, images et polices du template.
- `resources/views/layouts/layout.blade.php` : structure commune, `@vite`, `@include` et `@yield`.
- `resources/views/partials/` : navbar et footer. Le partial aside est documenté et vide, car Agropro ne possède pas de barre latérale.
- `resources/views/pages/` : dashboard (accueil), about, service, testimonials, blog et contact, avec `@extends` et `@section`.
- `routes/web.php` : routes nommées pour toutes les pages.

Les images des vues utilisent `Vite::asset`. L’entrée `images.js` les inscrit dans le manifeste. Les chemins des images et polices dans les CSS sont traités par Vite.

Les anciens plugins sont regroupés dans `agropro.js` pour conserver leur ordre : jQuery, Bootstrap, Owl Carousel, datepicker, puis le code du template. Le second chargement de jQuery et le suivi Google Analytics de démonstration sont retirés. L’import du fichier normalize.css absent est retiré ; Bootstrap fournit déjà la normalisation de base. Les liens services.html et news.html sont dirigés vers les pages existantes.

## Démarrage

```powershell
npm.cmd install
npm.cmd run build
php artisan serve
```

Ouvrir http://127.0.0.1:8000/dashboard.

Pour modifier les assets avec rechargement automatique, lancer `npm.cmd run dev` dans un autre terminal. Après l’arrêt de Vite, lancer `npm.cmd run build` pour servir les fichiers compilés.

## Vérification

```powershell
php artisan test
```

Les formulaires, liens Login, recherche et réseaux sociaux restent les éléments de démonstration du template ; aucun traitement métier n’est ajouté par ce TP. Les polices Google et la carte demandent une connexion Internet. Le crédit original du template est conservé dans le footer.
