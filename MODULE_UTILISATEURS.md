# Utilisateurs et connexion

Le site public utilise Agropro pour `/login` et `/register`. Le Back Office utilise Mazer pour `/admin/login`. Les connexions sont indépendantes : guard `web` pour les utilisateurs, guard `admin` pour les administrateurs. Une connexion ou déconnexion dans une interface ne connecte ni ne déconnecte l’autre. Un utilisateur ordinaire ne peut pas accéder à `/admin/*`. L’inscription publique attribue toujours le rôle `user`. Seuls les administrateurs gèrent les comptes dans `/admin/users` (création, liste, détail, modification, suppression).

Les mots de passe sont hachés, les sessions renouvelées à la connexion et invalidées à la déconnexion. La connexion est limitée à cinq échecs par minute et par combinaison adresse e-mail/IP. Les formulaires utilisent CSRF, confirmation du mot de passe, validation et unicité de l’e-mail. Un administrateur ne peut ni supprimer son compte ni retirer son propre rôle. Le mot de passe vide à la modification conserve le précédent.

## Installation et démonstration

1. Définir `NUTRITRACE_ADMIN_EMAIL` et `NUTRITRACE_ADMIN_PASSWORD` dans `.env` (mot de passe fort, minimum 8 caractères).
2. Exécuter `php artisan migrate` puis `php artisan db:seed`.
3. Se connecter à `/admin/login`, ouvrir Utilisateurs et démontrer le CRUD.
4. Créer un compte sur `/register`, puis vérifier qu’il peut accéder au catalogue mais reçoit un refus sur `/admin/dashboard`.

Dans l’installation locale actuelle, l’adresse administrateur est `admin@nutritrace.test`. Le mot de passe initial aléatoire est enregistré uniquement dans `.env`, qui est ignoré par Git. Le seeder ne remplace pas les mots de passe des comptes existants. En local, cinq comptes de démonstration sont créés avec UserFactory et des mots de passe aléatoires ; les administrateurs peuvent leur attribuer un nouveau mot de passe via le CRUD.

La gestion User ne remplace pas les deux entités du CRUD individuel Catégorie–Produit. Les tests utilisent une base SQLite en mémoire, sans modifier les comptes locaux.
