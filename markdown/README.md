# TomTroc - Plateforme d'échange de livres

Plateforme web permettant aux utilisateurs d'échanger des livres entre particuliers. Développée en PHP natif avec une architecture MVC complète.

## ✨ Fonctionnalités

### ✅ Implémenté
- **Authentification** : Inscription, connexion, déconnexion sécurisée
- **Profils utilisateurs** : Profil privé, profil public, modification (avatar, bio)
- **Bibliothèque personnelle** : Ajout, modification, suppression de livres
- **Catalogue public** : Liste des livres disponibles, recherche
- **Pages détail** : Informations complètes sur chaque livre
- **Messagerie** : Conversations entre utilisateurs, messages en temps réel
- **Upload d'images** : Avatars et photos de livres
- **Design responsive** : Interface adaptée mobile/desktop

### 🔜 Évolutions possibles
- Système de gestion des échanges
- Notifications par email
- Système de notation
- Favoris et listes de souhaits

## 📁 Structure du projet

```
Projet4/
├── app/
│   ├── core/                    # Classes système
│   │   ├── App.php             # Routeur principal
│   │   ├── Controller.php      # Contrôleur de base
│   │   ├── Model.php           # Manager de base
│   │   ├── Entity.php          # Entité de base
│   │   ├── Database.php        # Connexion PDO (Singleton)
│   │   ├── Session.php         # Gestion des sessions
│   │   └── helpers.php         # Fonctions utilitaires
│   ├── controller/              # Contrôleurs
│   │   ├── HomeController.php
│   │   ├── ErrorController.php
│   │   ├── AuthController.php
│   │   ├── ProfileController.php
│   │   ├── BookController.php
│   │   └── MessageController.php
│   ├── model/
│   │   ├── entity/             # Entités métier
│   │   │   ├── User.php
│   │   │   ├── Book.php
│   │   │   └── Message.php
│   │   └── manager/            # Managers (accès BDD)
│   │       ├── UserManager.php
│   │       ├── BookManager.php
│   │       └── MessageManager.php
│   └── view/                   # Vues (templates)
│       ├── layouts/            # Templates de base
│       ├── home/
│       ├── auth/
│       ├── profile/
│       ├── book/
│       ├── message/
│       └── error/
├── config/
│   ├── config.php              # Configuration générale
│   ├── config.example.php      # Template config BDD
│   ├── config.local.php        # Config locale (gitignored)
│   └── routes.php              # Routes personnalisées
├── public/                      # Dossier public (seul accessible)
│   ├── index.php               # Point d'entrée
│   ├── .htaccess              # Réécriture d'URL
│   ├── css/                    # Feuilles de style
│   │   ├── style.css
│   │   ├── global.css
│   │   ├── auth.css
│   │   ├── profile.css
│   │   └── book-edit.css
│   ├── assets/                 # Assets statiques
│   └── uploads/                # Uploads utilisateurs
├── markdown/                    # Documentation
│   ├── README.md               # Ce fichier
│   ├── QUICKSTART.md           # Démarrage rapide
│   ├── STRUCTURE.txt           # Architecture détaillée
│   └── 01-07 guides par étape
└── sql/
    └── database.sql            # Script de création BDD
```

## 🚀 Installation

### Prérequis

- PHP 8.0 ou supérieur
- MySQL 5.7+ ou MariaDB 10.2+
- Apache avec mod_rewrite activé
- Composer (optionnel)

### Étapes d'installation

1. **Cloner le projet**
   ```bash
   git clone [url-du-repo]
   cd Projet4
   ```

2. **Configurer Apache**
   
   Assurez-vous que mod_rewrite est activé :
   ```bash
   # Sur Ubuntu/Debian
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

3. **Configurer la base de données**
   
   a. Créer la base de données :
   ```bash
   mysql -u root -p < sql/database.sql
   ```
   
   Ou via phpMyAdmin :
   - Accéder à phpMyAdmin
   - Créer une nouvelle base de données nommée `tomtroc`
   - Importer le fichier `sql/database.sql`

4. **Configurer les identifiants**
   
   Copier le fichier de configuration exemple :
   ```bash
   cp config/config.example.php config/config.local.php
   ```
   
   Éditer `config/config.local.php` avec vos identifiants :
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'tomtroc');
   define('DB_USER', 'votre_utilisateur');
   define('DB_PASS', 'votre_mot_de_passe');
   ```

5. **Configurer l'URL de base**
   
   Éditer `config/config.php` et ajuster `BASE_URL` selon votre configuration :
   ```php
   // Exemple XAMPP
   define('BASE_URL', '/tests/Projet4/public/');
   
   // Exemple serveur local
   define('BASE_URL', '/public/');
   ```

6. **Permissions des dossiers**
   ```bash
   chmod -R 755 public/uploads
   ```

7. **Accéder à l'application**
   ```
   http://localhost/tests/Projet4/public/
   ```

## 🗄️ Base de données

### Tables principales

- **users** : Utilisateurs de la plateforme
  - Authentification sécurisée (password_hash)
  - Profils avec bio et avatar
  - Dates d'inscription
  
- **books** : Livres des utilisateurs
  - Relation avec users (clé étrangère)
  - Statut de disponibilité pour l'échange
  - Images et descriptions
  
- **messages** : Messagerie entre utilisateurs
  - Conversations bidirectionnelles
  - Statut lu/non lu
  - Timestamps

### Données de test

La base de données contient des données de test :
- 3 utilisateurs (alice, bob, charlie)
- Mot de passe pour tous : `password123`
- 6 livres d'exemple
- Messages de conversation

## 🎨 Architecture MVC

### Modèle (Model)
- **Entities** : Objets métier (User, Book, Message)
- **Managers** : Accès base de données (UserManager, BookManager, MessageManager)

### Vue (View)
- Templates HTML sans logique métier
- Layout principal avec header/footer
- Vues organisées par module

### Contrôleur (Controller)
- Logique métier
- Orchestration entre modèles et vues
- Gestion des requêtes HTTP

### Core
- **App.php** : Routage dynamique avec paramètres
- **Database.php** : Connexion PDO singleton
- **Session.php** : Gestion sessions, CSRF, messages flash
- **helpers.php** : Fonctions utilitaires globales

## 🔧 Configuration

### Environnements

Dans `config/config.php` :
```php
define('ENVIRONMENT', 'development'); // ou 'production'
```

- **development** : Affichage complet des erreurs
- **production** : Masquage des erreurs

### Routes personnalisées

Les routes sont définies dans `config/routes.php` :
```php
'nos-livres' => ['controller' => 'Book', 'action' => 'list'],
```

Format d'URL standard : `/controller/action/param1/param2`

## 🔒 Sécurité

- **Requêtes préparées** : Protection contre les injections SQL
- **CSRF tokens** : Protection contre les attaques CSRF
- **Password hashing** : bcrypt pour les mots de passe
- **Validation** : Côté serveur et client
- **Échappement** : htmlspecialchars pour l'affichage
- **.htaccess** : Protection dossiers sensibles
- **Sessions sécurisées** : Configuration PHP optimisée

## 📖 Documentation

Pour plus de détails, consultez :
- **QUICKSTART.md** : Guide de démarrage rapide
- **STRUCTURE.txt** : Structure détaillée du projet
- **01-INSTALLATION.md** : Installation et configuration
- **02-ARCHITECTURE.md** : Architecture MVC détaillée
- **03-AUTHENTICATION.md** : Système d'authentification
- **04-PROFILS.md** : Gestion des profils
- **05-LIVRES.md** : Bibliothèque et catalogue
- **06-MESSAGERIE.md** : Système de messagerie
- **07-DEVELOPPEMENT.md** : Guide de développement

## 🧪 Tests

### Utilisateurs de test
```
Email: alice@example.com
Email: bob@example.com
Email: charlie@example.com
Mot de passe: password123
```

### Routes principales
- `/` - Page d'accueil
- `/login` - Connexion
- `/register` - Inscription
- `/mon-compte` - Mon profil
- `/nos-livres` - Catalogue
- `/book/my-books` - Ma bibliothèque
- `/messages` - Messagerie

## 🛠️ Technologies

- **Backend** : PHP 8.0+
- **Base de données** : MySQL 5.7+ / MariaDB 10.2+
- **Frontend** : HTML5, CSS3, JavaScript vanilla
- **Serveur** : Apache avec mod_rewrite
- **Architecture** : MVC personnalisé

## 📝 Licence

Projet éducatif - OpenClassrooms

---

**Statut** : ✅ Production ready (v1)
**Progression** : 85% (6/7 étapes complètes)
**Dernière mise à jour** : Octobre 2025