# 🛠️ Guide du développeur - TomTroc

## Architecture MVC Custom

### Flux de traitement d'une requête

```
1. index.php (point d'entrée)
   ↓
2. App::run() (core/App.php)
   ↓
3. Routage (config/routes.php)
   ↓
4. Contrôleur approprié
   ↓
5. Manager (accès BDD si nécessaire)
   ↓
6. Vue (rendu HTML)
```

### Système de routage

Les routes sont définies dans `config/routes.php` :

```php
// Route simple
$this->addRoute('GET', '/nos-livres', 'BookController', 'index');

// Route avec paramètre
$this->addRoute('GET', '/livre/:id', 'BookController', 'show');

// Route POST
$this->addRoute('POST', '/book/create', 'BookController', 'store');
```

## Conventions de code

### Nomenclature

- **Classes** : PascalCase (`BookController`, `UserManager`)
- **Méthodes** : camelCase (`findById`, `getUserBooks`)
- **Variables** : camelCase (`$userId`, `$bookData`)
- **Constantes** : UPPER_SNAKE_CASE (`BASE_URL`, `DB_HOST`)
- **Fichiers CSS** : kebab-case (`book-edit.css`, `messagerie.css`)

### Structure des contrôleurs

```php
class BookController extends Controller
{
    /**
     * Affiche la liste des livres
     */
    public function index()
    {
        // 1. Récupérer les données
        $bookManager = new BookManager();
        $books = $bookManager->findAvailableBooks();
        
        // 2. Préparer les données pour la vue
        $data = [
            'title' => 'Nos livres',
            'books' => $books
        ];
        
        // 3. Rendre la vue
        $this->render('book/index', $data);
    }
}
```

### Structure des managers

```php
class BookManager extends Model
{
    /**
     * Récupère un livre par son ID
     * 
     * @param int $id
     * @return Book|null
     */
    public function findById($id)
    {
        $this->table = 'books';
        $bookData = parent::findById($id);
        return $this->hydrateEntity('Book', $bookData);
    }
}
```

### Sécurité dans les vues

**Toujours échapper les données utilisateur** :

```php
<!-- ✅ Bon -->
<h1><?= e($book->getTitle()) ?></h1>

<!-- ❌ Mauvais -->
<h1><?= $book->getTitle() ?></h1>
```

**Utiliser les tokens CSRF** :

```php
<form method="POST">
    <?= csrf_field() ?>
    <!-- champs du formulaire -->
</form>
```

## Base de données

### Tables principales

- `users` - Utilisateurs
- `books` - Livres
- `messages` - Messages

### Relations

```
users (1) -----> (N) books
users (1) -----> (N) messages (sent)
users (1) -----> (N) messages (received)
```

### Exemples de requêtes

```php
// Récupérer avec JOIN
$sql = "SELECT b.*, u.username 
        FROM books b
        INNER JOIN users u ON b.user_id = u.id
        WHERE b.is_available = 1";

// Avec prepared statement
$query = $this->db->prepare($sql);
$query->execute();
```

## Gestion des fichiers uploadés

### Upload d'image

```php
// Vérifier le type
$allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
if (!in_array($_FILES['image']['type'], $allowedTypes)) {
    throw new Exception('Type de fichier non autorisé');
}

// Générer nom unique
$extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '.' . $extension;

// Déplacer le fichier
move_uploaded_file($_FILES['image']['tmp_name'], $destination);
```

### Protection des placeholders

```php
// Ne jamais supprimer les placeholders
if ($oldImage && $oldImage !== 'book_placeholder.png') {
    unlink($path . $oldImage);
}
```

## Helpers disponibles

### Fonctions globales (core/helpers.php)

```php
// Échappement HTML
e($string)

// URL de base
BASE_URL

// Génération token CSRF
csrf_token()
csrf_field()

// Vérification CSRF
csrf_check()

// Redirection
redirect($url)

// Messages flash
flash_set($type, $message)
flash_get($type)
```

## Styles CSS

### Variables CSS disponibles

```css
--primary-color: black;
--third-color: #00AC66;
--bg-color: #F5F3EF;
--bg-light: #FAF9F7;
--text-color: #333333;
--text-light: #666666;
--error-color: #C62828;
```

### Classes utilitaires

```css
.btn              /* Bouton de base */
.btn-primary      /* Bouton principal */
.btn-success      /* Bouton vert */
.btn-outline      /* Bouton avec bordure */

.card             /* Carte blanche */
.table            /* Tableau stylisé */
.badge            /* Badge de statut */
.modal-overlay    /* Modal popup */
```

## Débogage

### Activer les erreurs

```php
// config/config.local.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### Logger les requêtes SQL

```php
// Dans un Manager
echo $sql;
print_r($params);
```

### Vérifier les sessions

```php
// Afficher les données de session
var_dump($_SESSION);
```

## Tests manuels

### Checklist avant commit

- [ ] Vérifier qu'aucun `var_dump()` ne reste
- [ ] Tester les formulaires avec validation
- [ ] Vérifier l'échappement HTML (`e()`)
- [ ] Tester l'upload de fichiers
- [ ] Vérifier les permissions (connexion requise)
- [ ] Tester sur différents navigateurs
- [ ] Vérifier le responsive

## Ajout d'une nouvelle fonctionnalité

### 1. Créer la route

```php
// config/routes.php
$this->addRoute('GET', '/ma-nouvelle-page', 'MonController', 'maMethode');
```

### 2. Créer le contrôleur

```php
// app/controller/MonController.php
class MonController extends Controller
{
    public function maMethode()
    {
        $this->render('mon-dossier/ma-vue');
    }
}
```

### 3. Créer la vue

```php
// app/view/mon-dossier/ma-vue.php
<?php $activePage = 'ma-page' ?>
<h1>Ma nouvelle page</h1>
```

### 4. Ajouter le CSS (si nécessaire)

```css
/* public/css/ma-page.css */
.ma-classe {
    /* styles */
}
```

```php
<!-- Dans la vue -->
<link rel="stylesheet" href="<?= BASE_URL ?>css/ma-page.css">
```

## Ressources

- [Documentation PHP](https://www.php.net/manual/fr/)
- [PDO Documentation](https://www.php.net/manual/fr/book.pdo.php)
- [CSS Variables](https://developer.mozilla.org/fr/docs/Web/CSS/Using_CSS_custom_properties)

## Support

Pour toute question, consulter :
1. Ce guide
2. Le code source existant (exemples)
3. Les commentaires dans les fichiers

---

**Bonne programmation ! 🚀**
