# Authentification — Atelier 3 Breeze

Laravel Breeze 2.4 est installé avec Composer. Ses contrôleurs, requêtes et routes officiels ont été intégrés depuis les stubs Blade, puis personnalisés. L’installation automatique des vues Tailwind n’a pas été lancée afin de conserver Agropro et Mazer.

- `app/Models/User.php` : champs `username`, `phone`, mot de passe haché et contrat `MustVerifyEmail`.
- `database/migrations/2026_10_16_000002_add_username_and_phone_to_users.php` : champs uniques. Les comptes existants reçoivent `utilisateur_ID`, sans changement du mot de passe. Leur téléphone reste vide jusqu’à sa saisie.
- `app/Http/Controllers/Auth/RegisteredUserController.php` : inscription, validation, événement `Registered`, envoi du lien de vérification et ouverture de session.
- `app/Http/Requests/Auth/LoginRequest.php` : connexion par **nom d’utilisateur**, limitation des tentatives, rôle utilisateur.
- `routes/auth.php` : connexion, inscription, récupération du mot de passe et vérification de l’e-mail.
- `/dashboard` : compte protégé par `auth:web` et `verified` ; `/profile` : modification du profil, du mot de passe et suppression du compte.
- L’administration conserve sa connexion par e-mail et sa session `admin` indépendante. Le catalogue reste public.

## Démonstration

Créer un compte sur `/register`, vérifier le message envoyé, ouvrir le lien signé, puis consulter `/dashboard` et `/profile`. Se déconnecter et se reconnecter avec le nom d’utilisateur. Tester également les doublons et le mot de passe oublié.

## Envoi des e-mails

En local, `MAIL_MAILER=log` écrit les messages dans `storage/logs/laravel.log` : aucun e-mail réel n’est envoyé. Pour Gmail, renseigner dans `.env` les valeurs personnelles suivantes, puis exécuter `php artisan config:clear` :

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=votre-adresse@gmail.com
MAIL_PASSWORD=mot-de-passe-application-google
MAIL_FROM_ADDRESS=votre-adresse@gmail.com
MAIL_FROM_NAME="NutriTrace"
APP_URL=http://127.0.0.1:8000
```

Utiliser un mot de passe d’application Google et conserver `.env` hors Git. Laravel 12 utilise ici `MAIL_SCHEME`, conformément à `config/mail.php`. L’envoi SMTP doit être testé après ajout de ces paramètres.

Sur une autre machine : `composer install`, `php artisan migrate`, puis `npm install` et `npm run build`. Ne pas utiliser `migrate:fresh` sur une base à conserver.
