# 📦 01 - INSTALLATION ET CONFIGURATION

## Prérequis

### Logiciels requis
- **PHP** 8.0 ou supérieur
- **MySQL** 5.7+ ou **MariaDB** 10.2+
- **Apache** avec mod_rewrite activé
- **Git** (pour cloner le projet)

### Environnements recommandés
- **Windows** : XAMPP, WampServer
- **macOS** : MAMP, XAMPP
- **Linux** : LAMP stack

---

## Installation étape par étape

### 1. Cloner ou télécharger le projet

**Avec Git :**
```bash
git clone [url-du-repo]
cd Projet4
```

**Sans Git :**
- Télécharger le ZIP
- Extraire dans votre dossier web (htdocs, www, etc.)

### 2. Configurer Apache

#### Activer mod_rewrite

**Sur Ubuntu/Debian :**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

**Sur XAMPP/WAMP :**
Déjà activé par défaut normalement.

#### Vérifier le fichier .htaccess

Le fichier `public/.htaccess` doit contenir :
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
```

### 3. Créer la base de données

#### Option A : phpMyAdmin (Recommandé pour débutants)

1. Accéder à phpMyAdmin : `http://localhost/phpmyadmin`
2. Cliquer sur "Nouvelle base de données"
3. Nom : `tomtroc`
4. Encodage : `utf8mb4_unicode_ci`
5. Cliquer sur "Créer"
6. Sélectionner la base `tomtroc`
7. Onglet "Importer"
8. Sélectionner le fichier `sql/database.sql`
9. Cliquer sur "Exécuter"

#### Option B : Ligne de commande

```bash
# Se connecter à MySQL
mysql -u root -p

# Créer la base
CREATE DATABASE tomtroc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Quitter MySQL
exit;

# Importer le fichier SQL
mysql -u root -p tomtroc < sql/database.sql
```

#### Vérification

La base doit contenir :
- ✅ Table `users` (3 utilisateurs)
- ✅ Table `books` (6 livres)
- ✅ Table `messages` (quelques messages)

### 4. Configurer les identifiants de connexion

#### Créer le fichier de configuration locale

```bash
cp config/config.example.php config/config.local.php
```

#### Éditer config/config.local.php

Ouvrir `config/config.local.php` et modifier :

```php
<?php

// Configuration base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'tomtroc');
define('DB_USER', 'root');           // Votre utilisateur MySQL
define('DB_PASS', '');               // Votre mot de passe MySQL

// Pour XAMPP par défaut :
// User: root
// Pass: (vide)

// Pour MAMP par défaut :
// User: root
// Pass: root
```

**⚠️ Important** : Ce fichier est dans `.gitignore` et ne sera pas versionné.

### 5. Configurer l'URL de base

Éditer `config/config.php` et ajuster `BASE_URL` :

```php
// XAMPP Windows
define('BASE_URL', '/tests/Projet4/public/');

// XAMPP macOS
define('BASE_URL', '/Projet4/public/');

// Serveur de développement PHP
define('BASE_URL', '/');

// Production
define('BASE_URL', '/');
```

### 6. Configurer les permissions

#### Sur Linux/macOS

```bash
# Rendre le dossier uploads accessible en écriture
chmod -R 755 public/uploads

# Si nécessaire, donner les droits à Apache
sudo chown -R www-data:www-data public/uploads
```

#### Sur Windows (XAMPP)

Généralement aucune action requise. Si problème :
- Clic droit sur `public/uploads`
- Propriétés → Sécurité
- Autoriser "Écriture" pour tous les utilisateurs

### 7. Vérifier l'installation

#### Accéder à l'application

```
http://localhost/tests/Projet4/public/
```

Remplacer le chemin selon votre configuration.

#### Que devez-vous voir ?

✅ **Page d'accueil TomTroc** avec :
- Header avec navigation
- Titre de bienvenue
- Footer

❌ **Si erreur** :
- Page blanche → Vérifier erreurs PHP (voir section Dépannage)
- 404 → Vérifier BASE_URL et .htaccess
- Erreur BDD → Vérifier config.local.php

---

## Première connexion

### Utilisateurs de test

Trois utilisateurs sont disponibles :

| Email | Mot de passe | Description |
|-------|--------------|-------------|
| alice@example.com | password123 | 2 livres disponibles |
| bob@example.com | password123 | 1 livre disponible |
| charlie@example.com | password123 | 2 livres disponibles |

### Test de connexion

1. Aller sur `http://localhost/tests/Projet4/public/login`
2. Email : `alice@example.com`
3. Mot de passe : `password123`
4. Cliquer "Se connecter"

✅ Vous devriez être redirigé vers votre compte avec le message "Connexion réussie"

---

## Configuration avancée

### Environnement de développement

Dans `config/config.php` :

```php
define('ENVIRONMENT', 'development');
```

**Mode development :**
- Affichage complet des erreurs
- Messages de débogage
- Pas de cache

**Mode production :**
```php
define('ENVIRONMENT', 'production');
```
- Masquage des erreurs
- Logs dans fichiers
- Optimisations

### Limites d'upload

Dans `config/config.php`, les limites sont définies :

```php
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2 Mo
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);
```

**⚠️ Attention** : Vérifier aussi `php.ini` :
```ini
upload_max_filesize = 2M
post_max_size = 8M
```

### Sécurité des sessions

Configuration automatique dans `Session.php` :
- HttpOnly
- SameSite=Lax
- Durée de vie personnalisable

---

## Dépannage

### Page blanche

**Cause** : Erreur PHP non affichée

**Solution** :
1. Activer l'affichage des erreurs dans `config/config.php` :
```php
define('ENVIRONMENT', 'development');
```

2. Vérifier les logs Apache :
- XAMPP : `xampp/apache/logs/error.log`
- Linux : `/var/log/apache2/error.log`

### Erreur "404 Not Found"

**Cause** : .htaccess non fonctionnel ou BASE_URL incorrecte

**Solutions** :
1. Vérifier mod_rewrite activé
2. Vérifier BASE_URL dans `config/config.php`
3. Vérifier fichier `public/.htaccess` présent

### Erreur connexion base de données

**Messages** :
- "Could not connect to database"
- "Access denied for user"

**Solutions** :
1. Vérifier identifiants dans `config/config.local.php`
2. Vérifier que MySQL est démarré
3. Tester la connexion manuellement :
```bash
mysql -u root -p
```

### Erreur "Table doesn't exist"

**Cause** : Base de données non importée

**Solution** :
Réimporter `sql/database.sql` (voir étape 3)

### Problème upload d'images

**Cause** : Permissions insuffisantes

**Solutions** :
1. Vérifier permissions dossier `public/uploads/`
2. Vérifier limites PHP (voir Configuration avancée)
3. Vérifier erreurs dans les logs

### CSS ne charge pas

**Cause** : Chemin BASE_URL incorrect

**Solution** :
Ajuster BASE_URL dans `config/config.php` pour qu'il corresponde à votre structure de dossiers.

---

## Vérification finale

### Checklist d'installation

- [ ] Apache démarré et mod_rewrite activé
- [ ] MySQL démarré
- [ ] Base de données `tomtroc` créée
- [ ] Fichier `sql/database.sql` importé
- [ ] Fichier `config/config.local.php` créé et configuré
- [ ] BASE_URL correcte dans `config/config.php`
- [ ] Permissions `public/uploads/` correctes
- [ ] Page d'accueil accessible
- [ ] Connexion avec utilisateur test réussie

### Commandes de vérification rapide

```bash
# Vérifier la structure des dossiers
ls -la app/ config/ public/ sql/

# Vérifier les permissions uploads
ls -la public/uploads/

# Tester la connexion MySQL
mysql -u root -p -e "SHOW DATABASES;"

# Vérifier les tables
mysql -u root -p tomtroc -e "SHOW TABLES;"
```

---

## Prochaines étapes

✅ Installation terminée !

Consulter maintenant :
- **02-ARCHITECTURE.md** : Comprendre la structure MVC
- **QUICKSTART.md** : Tester toutes les fonctionnalités
- **03-AUTHENTICATION.md** : Détails du système d'authentification

---

**Besoin d'aide ?**
- Vérifier les logs : `xampp/apache/logs/error.log`
- Activer le mode development
- Consulter la documentation PHP/MySQL
