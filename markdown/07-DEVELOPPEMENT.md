# 🛠️ 07 - GUIDE DE DÉVELOPPEMENT

## Bonnes pratiques

### Structure du code

#### ✅ À FAIRE

**Séparation MVC stricte**
```php
// ❌ Mauvais : Logique dans la vue
<?php
$books = $db->query("SELECT * FROM books");
while ($book = $books->fetch()) {
    echo $book['title'];
}
?>

// ✅ Bon : Vue reçoit données du contrôleur
<?php foreach ($books as $book): ?>
    <h2><?= h($book->getTitle()) ?></h2>
<?php endforeach; ?>
```

**Utiliser les helpers**
```php
// ❌ Mauvais
echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

// ✅ Bon
echo h($title);
```

**Nommer clairement**
```php
// ❌ Mauvais
public function get($id) {}
public function u($d) {}

// ✅ Bon
public function getUserById($id) {}
public function updateUser($data) {}
```

#### ❌ À ÉVITER

- SQL direct dans les contrôleurs
- Logique métier dans les vues
- Variables globales
- Code dupliqué
- Fonctions trop longues (> 50 lignes)

---

## Sécurité

### Protection XSS

**Toujours échapper les sorties**
```php
// Dans les vues
<?= h($user->getUsername()) ?>
<?= h($book->getTitle()) ?>

// Pour HTML autorisé (très rare)
<?= strip_tags($description, '<p><br>') ?>
```

### Protection CSRF

**Dans chaque formulaire**
```html
<form method="POST">
    <input type="hidden" name="csrf_token" 
           value="<?= Session::generateCsrfToken() ?>">
    <!-- ... -->
</form>
```

**Validation côté serveur**
```php
if (!Session::validateCsrfToken($_POST['csrf_token'])) {
    Session::setFlash('error', 'Token invalide');
    return $this->redirect('...');
}
```

### Protection SQL Injection

**Toujours utiliser requêtes préparées**
```php
// ❌ DANGEREUX
$sql = "SELECT * FROM users WHERE id = $id";
$result = $db->query($sql);

// ✅ SÉCURISÉ
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$id]);
```

### Validation des données

**Côté serveur (obligatoire)**
```php
// Vérifier présence
if (empty($email) || empty($password)) {
    // Erreur
}

// Vérifier format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Erreur
}

// Vérifier longueur
if (strlen($password) < 8) {
    // Erreur
}

// Nettoyer
$title = trim($_POST['title']);
$email = strtolower(trim($_POST['email']));
```

**Côté client (UX)**
```html
<input type="email" required minlength="3">
<textarea maxlength="1000"></textarea>
```

### Upload de fichiers

**Validation complète**
```php
// Vérifier upload réussi
if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    // Erreur
}

// Vérifier taille
if ($_FILES['file']['size'] > MAX_FILE_SIZE) {
    // Trop volumineux
}

// Vérifier extension
$ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ALLOWED_EXTENSIONS)) {
    // Extension non autorisée
}

// Vérifier type MIME (facultatif mais recommandé)
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $_FILES['file']['tmp_name']);
if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
    // Type MIME invalide
}

// Générer nom unique
$filename = uniqid() . '_' . time() . '.' . $ext;
```

---

## Base de données

### Conventions

**Tables**
```sql
-- Pluriel, minuscule
CREATE TABLE users (...)
CREATE TABLE books (...)
CREATE TABLE messages (...)
```

**Colonnes**
```sql
-- snake_case
user_id INT
created_at DATETIME
is_available BOOLEAN
```

**Clés étrangères**
```sql
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
```

### Requêtes optimisées

**Utiliser JOIN plutôt que requêtes multiples**
```php
// ❌ Mauvais : N+1 requêtes
$books = $bookManager->getAll();
foreach ($books as $book) {
    $user = $userManager->getUserById($book->getUserId());
}

// ✅ Bon : 1 seule requête
SELECT b.*, u.username, u.avatar
FROM books b
JOIN users u ON b.user_id = u.id
```

**Limiter les résultats**
```php
// Pagination
SELECT * FROM books LIMIT 20 OFFSET 0

// Top N
SELECT * FROM users ORDER BY created_at DESC LIMIT 10
```

**Indexer les colonnes fréquemment recherchées**
```sql
CREATE INDEX idx_books_user_id ON books(user_id);
CREATE INDEX idx_books_is_available ON books(is_available);
```

---

## Gestion des erreurs

### Environnements

**Development**
```php
define('ENVIRONMENT', 'development');
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

**Production**
```php
define('ENVIRONMENT', 'production');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/error.log');
```

### Try-catch

```php
try {
    $db = Database::getInstance();
    // Opérations BDD
} catch (PDOException $e) {
    if (ENVIRONMENT === 'development') {
        echo "Erreur BDD : " . $e->getMessage();
    } else {
        error_log($e->getMessage());
        Session::setFlash('error', 'Erreur technique. Réessayez plus tard.');
        redirect('home');
    }
}
```

### Messages utilisateur

```php
// ✅ Messages clairs et utiles
Session::setFlash('success', 'Livre ajouté avec succès !');
Session::setFlash('error', 'Le titre doit faire au moins 3 caractères');

// ❌ Messages cryptiques
Session::setFlash('error', 'Erreur 42');
Session::setFlash('error', 'Une erreur est survenue');
```

---

## Performance

### Mise en cache

**Sessions**
```php
// Stocker données fréquemment utilisées
Session::set('user_data', $userData);

// Éviter requêtes répétées
if (!Session::has('user_stats')) {
    $stats = $userManager->getUserStats($userId);
    Session::set('user_stats', $stats);
}
```

**Optimisation images**

```php
// Redimensionner lors de l'upload
function resizeImage($source, $dest, $maxWidth, $maxHeight) {
    list($width, $height) = getimagesize($source);
    
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = $width * $ratio;
    $newHeight = $height * $ratio;
    
    $thumb = imagecreatetruecolor($newWidth, $newHeight);
    $image = imagecreatefromjpeg($source);
    
    imagecopyresampled($thumb, $image, 0, 0, 0, 0, 
                       $newWidth, $newHeight, $width, $height);
    
    imagejpeg($thumb, $dest, 85);
    imagedestroy($thumb);
    imagedestroy($image);
}
```

### Requêtes SQL

**Utiliser COUNT() plutôt que récupérer tout**
```php
// ❌ Mauvais
$books = $bookManager->getAll();
$count = count($books);

// ✅ Bon
SELECT COUNT(*) as count FROM books
```

**Lazy loading vs Eager loading**
```php
// Si on a besoin des utilisateurs : JOIN (eager)
SELECT b.*, u.username FROM books b JOIN users u ON b.user_id = u.id

// Si on n'a pas toujours besoin : requête séparée (lazy)
$books = $bookManager->getAll();
if ($needUsers) {
    foreach ($books as $book) {
        $book->user = $userManager->getUserById($book->getUserId());
    }
}
```

---

## Tests

### Tests manuels

**Checklist avant commit**
- [ ] Tester fonctionnalité en tant qu'utilisateur connecté
- [ ] Tester en tant que non connecté
- [ ] Tester avec données invalides
- [ ] Tester sur mobile (responsive)
- [ ] Vérifier messages d'erreur affichés
- [ ] Vérifier aucune erreur PHP
- [ ] Vérifier console navigateur (JS)

### Tests SQL

**Tester requêtes dans MySQL**
```sql
-- Avant d'implémenter dans le code
SELECT b.*, u.username 
FROM books b 
JOIN users u ON b.user_id = u.id 
WHERE b.is_available = 1;
```

### Debug

**var_dump() temporaire**
```php
// En développement uniquement
echo '<pre>';
var_dump($data);
echo '</pre>';
die();
```

**error_log()**
```php
// Plus propre pour production
error_log("User ID: " . $userId);
error_log("Books count: " . count($books));
```

---

## Git

### Workflow

**Commits fréquents**
```bash
git add app/controller/BookController.php
git commit -m "feat: Add book edit functionality"

git add app/view/book/edit.php
git commit -m "feat: Add book edit view with image preview"
```

**Messages de commit clairs**
```
feat: Add user authentication system
fix: Correct image upload validation
refactor: Reorganize BookManager methods
docs: Update README with installation steps
style: Format code according to PSR-12
```

**Branches**
```bash
git checkout -b feature/messaging
git checkout -b fix/image-upload
```

### .gitignore

```gitignore
# Configuration locale
config/config.local.php

# Uploads
public/uploads/*
!public/uploads/.gitkeep

# IDE
.vscode/
.idea/

# OS
.DS_Store
Thumbs.db

# Logs
*.log
```

---

## Documentation

### Commentaires PHP

**Docblocks pour méthodes publiques**
```php
/**
 * Récupère un livre par son ID
 * 
 * @param int $id L'identifiant du livre
 * @return Book|null Le livre ou null si non trouvé
 */
public function getBookById($id) {
    // ...
}
```

**Commentaires inline pour logique complexe**
```php
// Exclure les livres de l'utilisateur connecté pour éviter
// qu'il voit ses propres livres dans le catalogue public
if ($userId) {
    $sql .= " AND b.user_id != ?";
}
```

### README

Maintenir à jour :
- Instructions d'installation
- Prérequis
- Fonctionnalités implémentées
- Structure du projet
- Commandes utiles

---

## CSS

### Organisation

**Ordre des propriétés**
```css
.element {
    /* Positionnement */
    position: relative;
    top: 0;
    left: 0;
    
    /* Box model */
    display: flex;
    width: 100%;
    padding: 1rem;
    margin: 1rem 0;
    
    /* Typographie */
    font-size: 1rem;
    color: #333;
    
    /* Visuel */
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    
    /* Autres */
    transition: all 0.3s;
}
```

### Variables CSS

```css
:root {
    --primary-color: #007bff;
    --secondary-color: #6c757d;
    --success-color: #28a745;
    --danger-color: #dc3545;
    
    --font-family: 'Inter', Arial, sans-serif;
    --border-radius: 8px;
    --box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn-primary {
    background: var(--primary-color);
    border-radius: var(--border-radius);
}
```

### Responsive

```css
/* Mobile first */
.container {
    width: 100%;
    padding: 1rem;
}

/* Tablettes */
@media (min-width: 768px) {
    .container {
        max-width: 720px;
        padding: 2rem;
    }
}

/* Desktop */
@media (min-width: 1024px) {
    .container {
        max-width: 960px;
    }
}
```

---

## JavaScript

### Bonnes pratiques

**Séparer comportement et présentation**
```javascript
// ❌ Mauvais : JS inline
<button onclick="deleteBook(42)">Supprimer</button>

// ✅ Bon : Event listeners
<button class="delete-btn" data-book-id="42">Supprimer</button>

<script>
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const bookId = this.dataset.bookId;
        deleteBook(bookId);
    });
});
</script>
```

**Utiliser async/await**
```javascript
// ✅ Moderne et lisible
async function sendMessage(content) {
    try {
        const response = await fetch('/messages/send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ content })
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayMessage(data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}
```

---

## Déploiement

### Checklist production

- [ ] Passer en mode `production` dans config
- [ ] Désactiver affichage erreurs
- [ ] Activer logs d'erreurs
- [ ] Vérifier HTTPS activé
- [ ] Configurer permissions fichiers
- [ ] Sauvegarder base de données
- [ ] Tester sur serveur de staging
- [ ] Configurer sauvegardes automatiques

### Optimisations

**PHP**
```ini
; php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
```

**Apache**
```apache
# .htaccess
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css text/javascript
</IfModule>

<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
</IfModule>
```

---

## Extensions futures

### Suggestions d'améliorations

**Technique**
- Migration vers framework (Symfony, Laravel)
- API REST pour app mobile
- Tests automatisés (PHPUnit)
- CI/CD (GitHub Actions)
- Docker pour environnement

**Fonctionnalités**
- Système de notation/avis
- Géolocalisation (trouver livres près de chez soi)
- Historique des échanges
- Wishlist (livres recherchés)
- Recommandations personnalisées
- Notifications email
- Export bibliothèque (CSV, PDF)

---

## Ressources

### Documentation officielle
- [PHP Manual](https://www.php.net/manual/fr/)
- [MDN Web Docs](https://developer.mozilla.org/)
- [MySQL Reference](https://dev.mysql.com/doc/)

### Outils recommandés
- **IDE** : VS Code, PhpStorm
- **Extensions VS Code** : PHP Intelephense, PHP Debug
- **Database** : phpMyAdmin, MySQL Workbench
- **Git** : GitKraken, SourceTree
- **API Testing** : Postman, Insomnia

### Communautés
- Stack Overflow
- PHP France (Discord)
- OpenClassrooms Forum

---

## Résumé

**Points clés du développement** :

✅ **Sécurité** : CSRF, XSS, SQL Injection
✅ **Architecture** : MVC strict, séparation claire
✅ **Performance** : Cache, requêtes optimisées
✅ **Qualité** : Code lisible, documentation
✅ **Tests** : Validation complète
✅ **Git** : Commits atomiques, branches
✅ **Production** : Optimisations, monitoring

**Bonne continuation dans vos développements !** 🚀
