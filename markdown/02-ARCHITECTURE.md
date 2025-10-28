# 🏗️ 02 - ARCHITECTURE MVC

## Vue d'ensemble

TomTroc utilise une **architecture MVC personnalisée** développée from scratch en PHP natif. Cette architecture sépare clairement la logique métier, l'accès aux données et la présentation.

```
┌─────────────┐      ┌──────────────┐      ┌─────────────┐
│   Browser   │─────▶│  Controller  │─────▶│    Model    │
│             │      │   (Logique)  │      │    (BDD)    │
└─────────────┘      └──────────────┘      └─────────────┘
       ▲                     │                     │
       │                     ▼                     │
       │              ┌──────────────┐             │
       └──────────────│     View     │◀────────────┘
                      │  (Template)  │
                      └──────────────┘
```

---

## Composants Core

### 1. App.php - Le Routeur

**Rôle** : Point central de l'application. Analyse les URLs et dispatche vers les bons contrôleurs.

**Fonctionnement** :

```php
// URL : /book/show/42
// Résultat :
// Controller: BookController
// Action: show()
// Params: [42]
```

**Caractéristiques** :
- Routes personnalisées depuis `config/routes.php`
- Routes dynamiques avec paramètres `{id}`
- Gestion des routes par défaut
- Conversion automatique en CamelCase

**Exemple de routes** :
```php
'nos-livres' => ['controller' => 'Book', 'action' => 'index']
'livre/{id}' => ['controller' => 'Book', 'action' => 'show']
'messages/conversation/{id}' => ['controller' => 'Message', 'action' => 'conversation']
```

### 2. Database.php - Connexion PDO

**Rôle** : Gestion de la connexion à la base de données.

**Pattern** : Singleton (une seule instance pour toute l'application)

**Avantages** :
- Une seule connexion réutilisée
- Économie de ressources
- Configuration centralisée

**Utilisation** :
```php
$db = Database::getInstance();
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
```

### 3. Controller.php - Contrôleur de base

**Rôle** : Classe abstraite dont héritent tous les contrôleurs.

**Méthodes fournies** :
- `render($view, $data)` : Afficher une vue
- `redirect($url)` : Rediriger vers une URL
- `json($data)` : Retourner du JSON (AJAX)
- `checkAuth()` : Vérifier si utilisateur connecté

**Exemple** :
```php
class BookController extends Controller {
    public function show($id) {
        $book = $this->bookManager->getBookById($id);
        $this->render('book/show', ['book' => $book]);
    }
}
```

### 4. Model.php - Manager de base

**Rôle** : Classe abstraite fournissant les opérations CRUD génériques.

**Méthodes** :
- `getAll()` : Récupérer tous les enregistrements
- `getById($id)` : Récupérer un enregistrement par ID
- `create($data)` : Créer un enregistrement
- `update($id, $data)` : Mettre à jour
- `delete($id)` : Supprimer

**Les managers enfants** peuvent :
- Utiliser ces méthodes par défaut
- Les surcharger pour personnaliser
- Ajouter des méthodes spécifiques

### 5. Entity.php - Entité de base

**Rôle** : Classe abstraite pour les objets métier.

**Fonctionnalité principale** : Hydratation automatique

```php
$user = new User();
$user->hydrate([
    'id' => 1,
    'username' => 'Alice',
    'email' => 'alice@example.com'
]);
// Appelle automatiquement setId(), setUsername(), setEmail()
```

### 6. Session.php - Gestion des sessions

**Rôle** : Gestionnaire centralisé pour les sessions.

**Fonctionnalités** :
- Démarrage automatique
- Configuration sécurisée (HttpOnly, SameSite)
- Messages flash
- Tokens CSRF
- Stockage données utilisateur

**Méthodes** :
```php
Session::set('user_id', 42);
$userId = Session::get('user_id');
Session::setFlash('success', 'Opération réussie');
$token = Session::generateCsrfToken();
Session::validateCsrfToken($token);
```

### 7. helpers.php - Fonctions utilitaires

**Rôle** : Fonctions globales utilisables partout.

**Exemples** :
```php
// Échapper HTML
echo h($userInput);

// Générer une URL
echo url('book/show/42');

// Redirection
redirect('home');

// Vérifier authentification
requireAuth();

// Formater une date
echo formatDate($timestamp);
```

---

## Structure des dossiers

### Dossier `app/`

```
app/
├── core/           # Classes système (ne jamais modifier)
├── controller/     # Contrôleurs métier
├── model/
│   ├── entity/    # Objets métier (User, Book, Message)
│   └── manager/   # Accès base de données
└── view/          # Templates HTML
```

### Dossier `config/`

```
config/
├── config.php          # Configuration générale
├── config.local.php    # Configuration locale (BDD)
└── routes.php          # Routes personnalisées
```

### Dossier `public/`

**⚠️ Seul dossier accessible via le web**

```
public/
├── index.php       # Point d'entrée unique
├── .htaccess       # Réécriture d'URL
├── css/            # Feuilles de style
├── assets/         # Images, JS
└── uploads/        # Uploads utilisateurs
```

---

## Flux de fonctionnement

### 1. Requête HTTP

```
Utilisateur → http://localhost/tests/Projet4/public/livre/42
```

### 2. .htaccess réécrit l'URL

```apache
RewriteRule ^(.*)$ index.php?url=$1
```

Devient : `index.php?url=livre/42`

### 3. index.php démarre l'application

```php
require_once '../app/core/App.php';
$app = new App();
```

### 4. App.php analyse l'URL

```php
// URL: livre/42
// Route trouvée: livre/{id} → Book::show
$controller = new BookController();
$controller->show(42);
```

### 5. Controller traite la requête

```php
public function show($id) {
    // 1. Récupérer les données
    $book = $this->bookManager->getBookById($id);
    
    // 2. Passer à la vue
    $this->render('book/show', ['book' => $book]);
}
```

### 6. Manager interroge la BDD

```php
public function getBookById($id) {
    $stmt = $this->db->prepare(
        "SELECT * FROM books WHERE id = ?"
    );
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    
    $book = new Book();
    $book->hydrate($data);
    return $book;
}
```

### 7. View affiche le template

```php
<!-- app/view/book/show.php -->
<h1><?= h($book->getTitle()) ?></h1>
<p><?= h($book->getDescription()) ?></p>
```

### 8. Layout encapsule la vue

```php
<!-- app/view/layouts/main.php -->
<html>
<header>...</header>
<main>
    <?= $content ?> <!-- Contenu de book/show.php -->
</main>
<footer>...</footer>
</html>
```

### 9. Réponse envoyée au navigateur

HTML complet affiché à l'utilisateur.

---

## Patterns et Principes

### Pattern MVC

**Modèle (Model)**
- Entités : Représentation des objets métier
- Managers : Logique d'accès aux données
- **Responsabilité** : Gestion des données

**Vue (View)**
- Templates HTML simples
- Aucune logique métier
- **Responsabilité** : Présentation

**Contrôleur (Controller)**
- Réception des requêtes
- Orchestration modèle ↔ vue
- **Responsabilité** : Logique applicative

### Singleton Pattern

**Database** : Une seule instance

Avantages :
- Pas de multiples connexions
- Partage de l'instance
- Économie mémoire

### Dependency Injection

Les contrôleurs reçoivent leurs dépendances :

```php
class BookController extends Controller {
    private $bookManager;
    
    public function __construct() {
        $this->bookManager = new BookManager();
    }
}
```

### Active Record Pattern (simplifié)

Les entités peuvent se sauvegarder :

```php
$book = new Book();
$book->setTitle("1984");
$manager->create($book); // Sauvegarde
```

---

## Conventions de nommage

### Fichiers

- **Contrôleurs** : `BookController.php` (PascalCase + Controller)
- **Managers** : `BookManager.php` (PascalCase + Manager)
- **Entités** : `Book.php` (PascalCase)
- **Vues** : `my-books.php` (kebab-case)

### Classes et méthodes

```php
// Classes : PascalCase
class BookController {}

// Méthodes : camelCase
public function myBooks() {}

// Constantes : UPPER_CASE
define('BASE_URL', '/');

// Variables : camelCase
$bookManager = new BookManager();
```

### Base de données

- **Tables** : pluriel minuscule (`users`, `books`, `messages`)
- **Colonnes** : snake_case (`created_at`, `is_available`)
- **Clés étrangères** : `user_id`, `book_id`

---

## Autoloading

### Système d'autoloading personnalisé

Dans `public/index.php` :

```php
spl_autoload_register(function ($class) {
    // Recherche dans core/
    $coreFile = '../app/core/' . $class . '.php';
    if (file_exists($coreFile)) {
        require_once $coreFile;
        return;
    }
    
    // Recherche dans controller/
    if (strpos($class, 'Controller') !== false) {
        $file = '../app/controller/' . $class . '.php';
        if (file_exists($file)) require_once $file;
    }
    
    // etc.
});
```

**Avantage** : Pas besoin de `require` manuel partout.

---

## Sécurité intégrée

### Protection CSRF

Tous les formulaires incluent :

```php
<input type="hidden" name="csrf_token" 
       value="<?= Session::generateCsrfToken() ?>">
```

Validation côté serveur :

```php
if (!Session::validateCsrfToken($_POST['csrf_token'])) {
    throw new Exception('Token invalide');
}
```

### Requêtes préparées

Toujours utiliser des requêtes préparées :

```php
// ❌ DANGEREUX
$db->query("SELECT * FROM users WHERE id = $id");

// ✅ SÉCURISÉ
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
```

### Échappement HTML

Dans les vues :

```php
// ❌ Risque XSS
<h1><?= $book->getTitle() ?></h1>

// ✅ SÉCURISÉ
<h1><?= h($book->getTitle()) ?></h1>
```

### Validation des données

Dans les contrôleurs :

```php
if (empty($title) || strlen($title) < 3) {
    Session::setFlash('error', 'Titre invalide');
    return $this->redirect('book/add');
}
```

---

## Gestion des erreurs

### Environnement development

```php
define('ENVIRONMENT', 'development');
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

Affiche toutes les erreurs directement.

### Environnement production

```php
define('ENVIRONMENT', 'production');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
```

Erreurs loggées, pages d'erreur personnalisées.

### Pages d'erreur personnalisées

- **404.php** : Page non trouvée
- **403.php** : Accès refusé

```php
$this->render('error/404', [], 'error');
```

---

## Points d'extension

### Ajouter un nouveau module

**1. Créer l'entité**
```php
// app/model/entity/Review.php
class Review extends Entity { ... }
```

**2. Créer le manager**
```php
// app/model/manager/ReviewManager.php
class ReviewManager extends Model { ... }
```

**3. Créer le contrôleur**
```php
// app/controller/ReviewController.php
class ReviewController extends Controller { ... }
```

**4. Créer les vues**
```
app/view/review/
  ├── index.php
  └── add.php
```

**5. Ajouter les routes**
```php
// config/routes.php
'reviews' => ['controller' => 'Review', 'action' => 'index']
```

### Personnaliser le routage

Modifier `app/core/App.php` pour :
- Ajouter des middlewares
- Gérer des sous-domaines
- Implémenter du caching
- Ajouter de la journalisation

---

## Bonnes pratiques

### ✅ À FAIRE

- Séparer clairement MVC
- Utiliser les helpers (h(), url())
- Valider toutes les entrées
- Utiliser les requêtes préparées
- Échapper les sorties HTML
- Vérifier les tokens CSRF
- Logger les erreurs importantes

### ❌ À ÉVITER

- Logique métier dans les vues
- Requêtes SQL directes dans les contrôleurs
- Variables globales
- Code dupliqué
- SQL non préparé
- Affichage non échappé

---

## Résumé

L'architecture TomTroc est :

✅ **Simple** : Structure claire et compréhensible
✅ **Modulaire** : Composants réutilisables
✅ **Sécurisée** : CSRF, prepared statements, échappement
✅ **Extensible** : Facile d'ajouter des fonctionnalités
✅ **Maintenable** : Séparation des responsabilités

**Prochaine étape** : Consulter **03-AUTHENTICATION.md** pour comprendre le système d'authentification.
