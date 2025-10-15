# TomTroc - Plateforme d'échange de livres

Plateforme web permettant aux utilisateurs d'échanger des livres entre particuliers. Développée en PHP natif avec une architecture MVC.

## 📋 Fonctionnalités

### Version actuelle
- ✅ Architecture MVC complète
- ✅ Système de routage avec URLs propres
- ✅ Base de données relationnelle MySQL
- ✅ Pattern Entité/Manager
- ✅ Gestion des sessions
- ✅ Page Hello World fonctionnelle

### Fonctionnalités à venir
- 🔐 Inscription et connexion des membres
- 👤 Pages de profil utilisateurs (consultation et modification)
- 📚 Bibliothèque personnelle (CRUD livres)
- 🔄 Page "Nos livres à l'échange" avec recherche
- 📖 Page détail d'un livre
- 💬 Système de messagerie

## 🛠️ Technologies utilisées

- **Backend** : PHP 8+ (sans framework)
- **Base de données** : MySQL / MariaDB
- **Frontend** : HTML5, CSS3, JavaScript vanilla
- **Serveur** : Apache avec mod_rewrite

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
│   │   └── Session.php         # Gestion des sessions
│   ├── controllers/             # Contrôleurs
│   │   ├── HomeController.php
│   │   └── ErrorController.php
│   ├── models/
│   │   ├── entities/           # Entités métier
│   │   └── managers/           # Managers (accès BDD)
│   └── views/                  # Vues (templates)
│       ├── layouts/            # Templates de base
│       ├── home/
│       ├── auth/
│       ├── user/
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
│   ├── css/
│   ├── js/
│   └── uploads/                # Uploads utilisateurs
├── sql/
│   └── database.sql            # Script de création BDD
├── .gitignore
└── README.md
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
  
- **books** : Livres des utilisateurs
  - Relation avec users (clé étrangère)
  - Statut de disponibilité pour l'échange
  
- **messages** : Messagerie entre utilisateurs
  - Conversations entre membres
  - Statut lu/non lu

### Données de test

La base de données contient des données de test :
- 3 utilisateurs (alice, bob, charlie)
- Mot de passe pour tous : `password123`
- 6 livres d'exemple
- Messages de conversation

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

## 📝 Normes de code

- ✅ Architecture MVC stricte
- ✅ Pas de CSS inline (uniquement des classes)
- ✅ Prepared statements pour toutes les requêtes SQL
- ✅ Mots de passe hashés (password_hash)
- ✅ Protection CSRF pour les formulaires
- ✅ Validation et nettoyage des inputs
- ✅ Échappement des outputs (htmlspecialchars)
- ✅ Nommage cohérent et explicite
- ✅ Commentaires en français

## 🔐 Sécurité

- **Mots de passe** : Hashés avec `password_hash()`
- **Sessions** : Protection CSRF, régénération d'ID
- **SQL** : Requêtes préparées PDO uniquement
- **XSS** : Échappement systématique avec `htmlspecialchars()`
- **Uploads** : Validation des types et tailles de fichiers
- **Config** : Fichiers sensibles exclus du versioning (.gitignore)

## 🧪 Développement

### Ajouter un contrôleur

```php
<?php
// app/controllers/MonController.php
class MonController extends Controller
{
    public function index()
    {
        $data = ['title' => 'Ma page'];
        $this->render('mon/index', $data);
    }
}
```

### Ajouter un manager

```php
<?php
// app/models/managers/MonManager.php
class MonManager extends Model
{
    protected $table = 'ma_table';
    
    public function findActive()
    {
        return $this->findBy(['status' => 'active']);
    }
}
```

### Ajouter une entité

```php
<?php
// app/models/entities/MonEntite.php
class MonEntite extends Entity
{
    private $name;
    
    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }
}
```

## 📚 Documentation

- [Architecture MVC](docs/architecture.md) (à venir)
- [Guide de contribution](docs/contributing.md) (à venir)
- [API Documentation](docs/api.md) (à venir)

## 🐛 Débogage

En cas de problème :

1. Vérifier les logs Apache/PHP
2. Vérifier que mod_rewrite est activé
3. Vérifier les permissions des dossiers
4. Vérifier la configuration de la base de données
5. Activer le mode développement pour voir les erreurs

## 📄 Licence

Projet éducatif - Libre d'utilisation

## 👥 Auteur

Développé dans le cadre d'un projet de formation

---

**Note importante** : Le fichier `config/config.local.php` contient des données sensibles et ne doit JAMAIS être versionné. Utilisez toujours `config.example.php` comme template.
# OC-4---Mettez-en-place-un-site-de-mise-en-relation-avec-PHP
