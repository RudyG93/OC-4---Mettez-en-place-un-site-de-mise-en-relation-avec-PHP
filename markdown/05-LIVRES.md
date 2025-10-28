# 📚 05 - GESTION DES LIVRES

## Vue d'ensemble

Le système de gestion des livres comprend deux parties :
1. **Bibliothèque personnelle** : Gestion de mes propres livres
2. **Catalogue public** : Consultation des livres disponibles à l'échange

---

## Bibliothèque personnelle

### Ma bibliothèque (`/book/my-books`)

**Fonctionnalités** :
- Liste de tous mes livres
- Statistiques (total, disponibles)
- Actions rapides (modifier, supprimer, toggle disponibilité)
- Bouton "Ajouter un livre"

**Code contrôleur** :
```php
public function myBooks() {
    requireAuth();
    
    $userId = Session::get('user_id');
    $books = $this->bookManager->getBooksByUserId($userId);
    
    // Calcul des stats
    $stats = [
        'total' => count($books),
        'available' => count(array_filter($books, fn($b) => $b->getIsAvailable()))
    ];
    
    $this->render('book/my-books', [
        'books' => $books,
        'stats' => $stats
    ]);
}
```

### Ajouter un livre (`/book/add`)

**Champs du formulaire** :
- Titre (requis, min 1 caractère)
- Auteur (requis, min 1 caractère)
- Description (optionnel, max 1000 caractères)
- Image (JPG/PNG/GIF, max 2 Mo)
- Disponibilité (checkbox)

**Validation** :
```php
public function create() {
    requireAuth();
    
    if (!Session::validateCsrfToken($_POST['csrf_token'])) {
        Session::setFlash('error', 'Token invalide');
        return $this->redirect('book/add');
    }
    
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $isAvailable = isset($_POST['is_available']) ? 1 : 0;
    
    // Validation
    if (empty($title) || empty($author)) {
        Session::setFlash('error', 'Le titre et l\'auteur sont requis');
        return $this->redirect('book/add');
    }
    
    // Upload image
    $imagePath = $this->handleImageUpload();
    
    // Créer le livre
    $bookId = $this->bookManager->create([
        'user_id' => Session::get('user_id'),
        'title' => $title,
        'author' => $author,
        'description' => $description,
        'image' => $imagePath,
        'is_available' => $isAvailable
    ]);
    
    Session::setFlash('success', 'Livre ajouté avec succès !');
    $this->redirect('book/my-books');
}
```

### Modifier un livre (`/book/{id}/edit`)

**Sécurité** : Vérifier que l'utilisateur est propriétaire

```php
public function edit($id) {
    requireAuth();
    
    $book = $this->bookManager->getBookById($id);
    
    if (!$book) {
        return $this->render('error/404', [], 'error');
    }
    
    // Vérifier propriété
    if ($book->getUserId() !== Session::get('user_id')) {
        Session::setFlash('error', 'Vous ne pouvez pas modifier ce livre');
        return $this->redirect('book/my-books');
    }
    
    $this->render('book/edit', ['book' => $book]);
}
```

**Fonctionnalités avancées** :
- Design 2 colonnes (image + formulaire)
- Prévisualisation instantanée de l'image
- Compteur de caractères pour la description
- Boutons multiples (Sauvegarder / Annuler / Supprimer)

### Supprimer un livre

```php
public function delete($id) {
    requireAuth();
    
    if (!Session::validateCsrfToken($_POST['csrf_token'])) {
        Session::setFlash('error', 'Token invalide');
        return $this->redirect('book/my-books');
    }
    
    $book = $this->bookManager->getBookById($id);
    
    // Vérifier propriété
    if ($book->getUserId() !== Session::get('user_id')) {
        Session::setFlash('error', 'Action non autorisée');
        return $this->redirect('book/my-books');
    }
    
    // Supprimer l'image physique
    if ($book->getImage()) {
        $imagePath = '../public/uploads/' . $book->getImage();
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    // Supprimer en BDD
    $this->bookManager->delete($id);
    
    Session::setFlash('success', 'Livre supprimé');
    $this->redirect('book/my-books');
}
```

### Toggle disponibilité

**AJAX pour action rapide** :

```php
public function toggleAvailability($id) {
    requireAuth();
    
    $book = $this->bookManager->getBookById($id);
    
    if ($book->getUserId() !== Session::get('user_id')) {
        return $this->json(['error' => 'Non autorisé'], 403);
    }
    
    $newStatus = !$book->getIsAvailable();
    $this->bookManager->update($id, ['is_available' => $newStatus]);
    
    return $this->json(['success' => true, 'is_available' => $newStatus]);
}
```

---

## Catalogue public

### Liste des livres (`/nos-livres`)

**Fonctionnalités** :
- Affichage de tous les livres disponibles
- Recherche par titre OU auteur
- Exclusion de mes propres livres (si connecté)
- Affichage du propriétaire

**Code contrôleur** :
```php
public function index() {
    $search = trim($_GET['search'] ?? '');
    $userId = Session::get('user_id');
    
    if ($search) {
        $books = $this->bookManager->searchBooks($search, $userId);
    } else {
        $books = $this->bookManager->getAllAvailableBooks($userId);
    }
    
    $this->render('book/index', [
        'books' => $books,
        'search' => $search
    ]);
}
```

**Requête SQL** :
```php
public function getAllAvailableBooks($excludeUserId = null) {
    $sql = "SELECT b.*, u.username, u.avatar 
            FROM books b
            JOIN users u ON b.user_id = u.id
            WHERE b.is_available = 1";
    
    if ($excludeUserId) {
        $sql .= " AND b.user_id != ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$excludeUserId]);
    } else {
        $stmt = $this->db->query($sql);
    }
    
    return $stmt->fetchAll(PDO::FETCH_CLASS, 'Book');
}
```

### Recherche

```php
public function searchBooks($query, $excludeUserId = null) {
    $sql = "SELECT b.*, u.username, u.avatar 
            FROM books b
            JOIN users u ON b.user_id = u.id
            WHERE b.is_available = 1
            AND (b.title LIKE ? OR b.author LIKE ?)";
    
    $params = ["%$query%", "%$query%"];
    
    if ($excludeUserId) {
        $sql .= " AND b.user_id != ?";
        $params[] = $excludeUserId;
    }
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_CLASS, 'Book');
}
```

---

## Page détail d'un livre

### Route : `/livre/{id}`

**Informations affichées** :
- Image du livre
- Titre, auteur, description
- Statut disponibilité
- Propriétaire (avec lien vers profil)
- Autres livres du même propriétaire

**Actions contextuelles** :

| Situation | Actions disponibles |
|-----------|-------------------|
| Propriétaire | Modifier / Supprimer |
| Connecté + disponible | Envoyer un message |
| Connecté + non disponible | "Livre non disponible" |
| Non connecté | Se connecter pour échanger |

**Code** :
```php
public function show($id) {
    $book = $this->bookManager->getBookById($id);
    
    if (!$book) {
        return $this->render('error/404', [], 'error');
    }
    
    // Récupérer le propriétaire
    $owner = $this->userManager->getUserById($book->getUserId());
    
    // Autres livres du propriétaire
    $otherBooks = $this->bookManager->getBooksByUserId(
        $book->getUserId(),
        $id, // Exclure le livre actuel
        3    // Limiter à 3
    );
    
    // Vérifier propriété
    $isOwner = (Session::get('user_id') == $book->getUserId());
    
    $this->render('book/show', [
        'book' => $book,
        'owner' => $owner,
        'otherBooks' => $otherBooks,
        'isOwner' => $isOwner
    ]);
}
```

---

## Upload d'images

### Fonction réutilisable

```php
private function handleImageUpload() {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        Session::setFlash('error', 'Erreur lors de l\'upload');
        return null;
    }
    
    // Vérifier taille
    if ($_FILES['image']['size'] > MAX_FILE_SIZE) {
        Session::setFlash('error', 'Image trop volumineuse (max 2 Mo)');
        return null;
    }
    
    // Vérifier extension
    $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        Session::setFlash('error', 'Format non autorisé (JPG, PNG, GIF)');
        return null;
    }
    
    // Générer nom unique
    $filename = 'book_' . uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = '../public/uploads/books/';
    
    // Créer dossier si nécessaire
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }
    
    // Déplacer
    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath . $filename)) {
        return 'books/' . $filename;
    }
    
    Session::setFlash('error', 'Impossible de sauvegarder l\'image');
    return null;
}
```

---

## Entité Book

```php
class Book extends Entity {
    private $id;
    private $userId;
    private $title;
    private $author;
    private $description;
    private $image;
    private $isAvailable;
    private $createdAt;
    
    // Propriétés pour jointures
    private $username;  // Nom du propriétaire
    private $avatar;    // Avatar du propriétaire
    
    // Getters/Setters pour toutes les propriétés
    
    public function getExcerpt($length = 150) {
        if (strlen($this->description) <= $length) {
            return $this->description;
        }
        return substr($this->description, 0, $length) . '...';
    }
    
    public function getAvailabilityLabel() {
        return $this->isAvailable ? 'Disponible' : 'Non disponible';
    }
}
```

---

## Manager BookManager

### Méthodes principales

```php
class BookManager extends Model {
    protected $table = 'books';
    
    public function getBookById($id) { /* ... */ }
    
    public function getBooksByUserId($userId, $exclude = null, $limit = null) { /* ... */ }
    
    public function getAllAvailableBooks($excludeUserId = null) { /* ... */ }
    
    public function searchBooks($query, $excludeUserId = null) { /* ... */ }
    
    public function create($data) { /* ... */ }
    
    public function update($id, $data) { /* ... */ }
    
    public function delete($id) { /* ... */ }
}
```

---

## Design responsive

### Grille de livres

```css
.books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 2rem;
}

.book-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.2s;
}

.book-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .books-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
    }
}
```

---

## Sécurité

### ✅ Bonnes pratiques implémentées

- Vérification de propriété avant modification/suppression
- Tokens CSRF sur tous les formulaires
- Validation des uploads (taille, type)
- Échappement HTML dans les vues
- Requêtes préparées
- Génération de noms de fichiers uniques

### ⚠️ Points d'attention

- Ne jamais faire confiance aux données utilisateur
- Toujours vérifier userId === propriétaire
- Valider côté serveur (jamais seulement côté client)
- Limiter taille des uploads
- Stocker les uploads hors de la racine si possible

---

## Tests

### Scénarios de test

1. **Ajouter un livre**
   - Avec/sans image
   - Disponible/non disponible
   - Vérifier validation

2. **Modifier un livre**
   - Changer l'image
   - Supprimer l'image
   - Modifier textes

3. **Supprimer un livre**
   - Vérifier suppression BDD
   - Vérifier suppression image physique

4. **Recherche**
   - Par titre
   - Par auteur
   - Chaîne vide

5. **Page détail**
   - En tant que propriétaire
   - En tant que visiteur connecté
   - En tant que visiteur non connecté

---

## Améliorations possibles

- Pagination (actuellement tous les livres affichés)
- Filtres avancés (genre, année, etc.)
- Tri personnalisable
- Import depuis API (ISBN, Google Books)
- Optimisation d'images (resize, compression)
- Système de tags/catégories
- Vue liste/grille commutable
- Favoris

---

## Résumé

Le système de gestion des livres offre :

✅ **Bibliothèque personnelle** : CRUD complet
✅ **Catalogue public** : Recherche et navigation
✅ **Pages détail** : Informations complètes
✅ **Upload sécurisé** : Images validées
✅ **Actions contextuelles** : Selon rôle utilisateur

**Prochaine étape** : **06-MESSAGERIE.md** pour le système de messagerie.
