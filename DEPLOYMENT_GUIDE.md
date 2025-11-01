# 🚀 Guide de Déploiement - TomTroc

## Checklist Pré-Déploiement

### 1. Configuration

#### Fichier config.local.php
```php
<?php
// config/config.local.php

// Base de données PRODUCTION
define('DB_HOST', 'votre_host_production');
define('DB_NAME', 'votre_base_production');
define('DB_USER', 'votre_user_production');
define('DB_PASS', 'votre_password_production');

// URL de base PRODUCTION
define('BASE_URL', 'https://votredomaine.com/');

// Désactiver les erreurs
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Activer les logs
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-errors.log');
```

### 2. Sécurité

#### Headers HTTP à ajouter
Modifier le `.htaccess` du dossier `public/` :

```apache
<IfModule mod_headers.c>
    # Anti-clickjacking
    Header always set X-Frame-Options "SAMEORIGIN"
    
    # Protection XSS
    Header always set X-XSS-Protection "1; mode=block"
    
    # Empêcher le MIME sniffing
    Header always set X-Content-Type-Options "nosniff"
    
    # Referrer Policy
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    # Content Security Policy (à adapter selon vos besoins)
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:;"
</IfModule>
```

#### Forcer HTTPS
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

### 3. Base de Données

#### Création et Import
```bash
# Se connecter au serveur MySQL
mysql -u root -p

# Créer la base
CREATE DATABASE tomtroc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Créer un utilisateur dédié
CREATE USER 'tomtroc_user'@'localhost' IDENTIFIED BY 'mot_de_passe_fort';
GRANT ALL PRIVILEGES ON tomtroc.* TO 'tomtroc_user'@'localhost';
FLUSH PRIVILEGES;

# Importer la structure
mysql -u tomtroc_user -p tomtroc < sql/database.sql
```

### 4. Permissions Fichiers

```bash
# Permissions sur les dossiers
chmod 755 app/
chmod 755 config/
chmod 755 public/
chmod 777 public/uploads/
chmod 777 public/uploads/books/
chmod 777 public/uploads/avatars/
chmod 755 sql/

# Permissions sur les fichiers
find . -type f -exec chmod 644 {} \;
chmod 644 public/index.php
chmod 644 .htaccess
chmod 600 config/config.local.php
```

### 5. Dossiers à Créer

```bash
# Créer le dossier de logs
mkdir -p logs
chmod 777 logs

# Vérifier les dossiers uploads
mkdir -p public/uploads/books
mkdir -p public/uploads/avatars
chmod 777 public/uploads/books
chmod 777 public/uploads/avatars
```

### 6. Fichiers à Ne PAS Déployer

Créer un fichier `.gitignore` si pas encore fait :

```gitignore
# Configuration locale
config/config.local.php

# Uploads utilisateurs
public/uploads/books/*
!public/uploads/books/book_placeholder.png
public/uploads/avatars/*
!public/uploads/avatars/pp_placeholder.png

# Logs
logs/*
!logs/.gitkeep

# Fichiers système
.DS_Store
Thumbs.db
.htaccess.swp
*~

# Documentation de développement (optionnel)
DEVELOPER_GUIDE.md
OPTIMIZATION_REPORT.md
```

---

## Déploiement par Méthode

### Option A : FTP/SFTP

1. **Uploader les fichiers**
```
- Connecter via FileZilla ou WinSCP
- Uploader tous les fichiers SAUF config.local.php
- Créer config.local.php directement sur le serveur
```

2. **Configuration serveur**
```
- Créer config.local.php avec les bonnes données
- Vérifier les permissions (chmod)
- Tester l'accès
```

### Option B : Git

1. **Sur le serveur**
```bash
cd /var/www/html
git clone https://github.com/votre-repo/tomtroc.git
cd tomtroc
```

2. **Configuration**
```bash
cp config/config.example.php config/config.local.php
nano config/config.local.php
# Modifier les paramètres
```

3. **Permissions**
```bash
chmod -R 755 app/ config/ public/
chmod -R 777 public/uploads/
chmod 600 config/config.local.php
```

### Option C : Hébergement Mutualisé

1. **Via cPanel/Plesk**
   - Uploader le ZIP du projet
   - Extraire dans public_html/
   - Créer la base de données via l'interface
   - Modifier config.local.php

2. **Document Root**
   - Pointer vers `/public/` comme document root
   - Ou créer un `.htaccess` racine qui redirige vers public/

---

## Configuration Serveur

### Apache (Recommandé)

#### VirtualHost
```apache
<VirtualHost *:80>
    ServerName votredomaine.com
    ServerAlias www.votredomaine.com
    
    DocumentRoot /var/www/tomtroc/public
    
    <Directory /var/www/tomtroc/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/tomtroc-error.log
    CustomLog ${APACHE_LOG_DIR}/tomtroc-access.log combined
</VirtualHost>
```

#### Modules nécessaires
```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod expires
sudo a2enmod deflate
sudo systemctl restart apache2
```

### Nginx

```nginx
server {
    listen 80;
    server_name votredomaine.com;
    root /var/www/tomtroc/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\. {
        deny all;
    }
}
```

---

## Tests Post-Déploiement

### Checklist de Validation

- [ ] Page d'accueil s'affiche
- [ ] Inscription fonctionne
- [ ] Connexion fonctionne
- [ ] Upload d'avatar fonctionne
- [ ] Ajout de livre fonctionne
- [ ] Upload d'image livre fonctionne
- [ ] Messagerie fonctionne
- [ ] Envoi de message fonctionne
- [ ] Les images s'affichent
- [ ] Le CSS est appliqué
- [ ] HTTPS fonctionne (si activé)
- [ ] Redirections fonctionnent
- [ ] Pages d'erreur personnalisées fonctionnent

### Tests de Sécurité

```bash
# Vérifier les headers HTTP
curl -I https://votredomaine.com

# Tester XSS (doit être échappé)
curl https://votredomaine.com -d "username=<script>alert('xss')</script>"

# Vérifier SSL (si HTTPS)
openssl s_client -connect votredomaine.com:443
```

---

## Optimisations Production

### 1. Cache Navigateur

Ajouter dans `.htaccess` (public/) :

```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### 2. Compression

```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

### 3. Optimisation Images

```bash
# Installer optipng et jpegoptim
sudo apt-get install optipng jpegoptim

# Optimiser les images
find public/uploads/books -name "*.png" -exec optipng -o5 {} \;
find public/uploads/books -name "*.jpg" -exec jpegoptim --max=85 {} \;
```

---

## Monitoring et Maintenance

### Logs à Surveiller

1. **Logs PHP** : `logs/php-errors.log`
2. **Logs Apache** : `/var/log/apache2/tomtroc-error.log`
3. **Logs MySQL** : `/var/log/mysql/error.log`

### Commandes Utiles

```bash
# Voir les erreurs PHP
tail -f logs/php-errors.log

# Voir les erreurs Apache
tail -f /var/log/apache2/tomtroc-error.log

# Espace disque uploads
du -sh public/uploads/

# Taille base de données
mysql -u root -p -e "SELECT table_schema AS 'Database', ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' FROM information_schema.TABLES GROUP BY table_schema;"
```

### Backup

#### Script de backup automatique

```bash
#!/bin/bash
# backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/tomtroc"

# Backup BDD
mysqldump -u tomtroc_user -p'password' tomtroc > $BACKUP_DIR/db_$DATE.sql

# Backup fichiers
tar -czf $BACKUP_DIR/files_$DATE.tar.gz public/uploads/

# Nettoyer anciens backups (garder 30 jours)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
```

Ajouter au cron :
```bash
crontab -e
# Backup quotidien à 2h du matin
0 2 * * * /path/to/backup.sh
```

---

## Support et Dépannage

### Problèmes Courants

#### 1. Page blanche
- Vérifier logs PHP : `tail -f logs/php-errors.log`
- Vérifier display_errors désactivé en production
- Vérifier permissions fichiers

#### 2. CSS non appliqué
- Vérifier le BASE_URL dans config.local.php
- Vérifier les permissions du dossier public/css/
- Vider le cache navigateur

#### 3. Upload ne fonctionne pas
- Vérifier `chmod 777 public/uploads/`
- Vérifier `upload_max_filesize` dans php.ini
- Vérifier `post_max_size` dans php.ini

#### 4. Erreur BDD
- Vérifier credentials dans config.local.php
- Vérifier que MySQL est démarré
- Vérifier les permissions de l'utilisateur

---

## 🎉 Déploiement Réussi !

Une fois toutes ces étapes complétées, votre application TomTroc est prête pour la production !

**N'oubliez pas** :
- Surveiller les logs régulièrement
- Faire des backups automatiques
- Mettre à jour PHP/MySQL régulièrement
- Surveiller l'espace disque

---

**Bonne chance avec votre projet ! 🚀**
