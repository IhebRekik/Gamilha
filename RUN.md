# Lancer le projet Gamilha

## Prérequis

- **PHP** 8.1 ou 8.2 (avec extensions : pdo_mysql, mbstring, xml, ctype, iconv, intl, json)
- **Composer**
- **MySQL** ou **MariaDB** avec la base `gamilha` créée et le fichier `__v__nement.sql` importé

## 1. Configurer la base de données

Le fichier `.env` contient déjà :

```
DATABASE_URL="mysql://root:@127.0.0.1:3306/gamilha?serverVersion=10.4.32-MariaDB&charset=utf8mb4"
```

- Si vous avez un **mot de passe** pour l’utilisateur `root`, modifiez ainsi :
  `mysql://root:VOTRE_MOT_DE_PASSE@127.0.0.1:3306/gamilha?serverVersion=10.4.32-MariaDB&charset=utf8mb4`
- Si vous utilisez **MySQL 8** au lieu de MariaDB, remplacez par :
  `serverVersion=8.0.32` dans l’URL.

## 2. Installer les dépendances

À la racine du projet :

```bash
composer install
```

## 3. (Optionnel) Générer une clé secrète

Si `APP_SECRET` est vide dans `.env`, générez une clé :

```bash
php bin/console secrets:set APP_SECRET
```

Ou ajoutez manuellement dans `.env` une ligne du type :

```
APP_SECRET=une_longue_chaine_aleatoire_32_caracteres
```

## 4. Vider le cache

```bash
php bin/console cache:clear
```

## 5. Lancer le serveur web

**Option A – Symfony CLI (recommandé si installé) :**

```bash
symfony server:start
```

Puis ouvrir l’URL affichée (souvent `https://127.0.0.1:8000`).

**Option B – PHP intégré :**

```bash
php -S localhost:8000 -t public
```

Puis ouvrir : **http://localhost:8000**

## 6. Tester le projet

- **Frontoffice (site public)** : http://localhost:8000/  
  Accueil, liste des événements, liste des équipes.

- **Backoffice (admin)** : http://localhost:8000/admin/dashboard  
  CRUD Événements, Matchs, Brackets, Équipes. Lien « Voir le site (front) » dans la barre pour revenir au front.

## Dépannage

- **Erreur de connexion MySQL** : vérifier que MySQL/MariaDB tourne, que la base `gamilha` existe et que le user/mot de passe dans `DATABASE_URL` sont corrects.
- **Page blanche** : vérifier les logs dans `var/log/dev.log` et lancer `php bin/console cache:clear`.
- **Assets (CSS/JS)** : en dev, ils sont servis depuis `public/` ; si vous utilisez encore Asset Mapper, exécuter `php bin/console importmap:install` si besoin.
