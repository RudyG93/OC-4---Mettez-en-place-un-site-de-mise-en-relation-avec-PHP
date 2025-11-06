# 📚 Documentation complète : BookController.php

## Vue d'ensemble

Le **BookController** est le contrôleur central pour gérer toutes les opérations liées aux livres dans l'application. Il gère le CRUD complet (Create, Read, Update, Delete) ainsi que des fonctionnalités avancées comme la recherche et la gestion des images.

---

## 🔧 Propriétés de la classe

```php
private BookManager $bookManager;      // Gère les opérations en base de données pour les livres
private UserManager $userManager;      // Gère les opérations en base de données pour les utilisateurs
private ImageUploader $imageUploader;  // Service pour uploader/supprimer les images
```

---

## 📋 Table des méthodes

| Méthode | Type | Visibilité | Utilisée ? | Route associée |
|---------|------|------------|------------|----------------|
| `__construct()` | Initialisation | public | ✅ Auto | - |
| `index()` | Action publique | public | ✅ Oui | `nos-livres` |
| `create()` | Action POST | public | ✅ Oui | `book/create` |
| `show()` | Action publique | public | ✅ Oui | `livre/{id}` |
| `edit()` | Action GET | public | ✅ Oui | `book/{id}/edit` |
| `update()` | Action POST | public | ✅ Oui | `book/{id}/update` |
| `delete()` | Action POST | public | ✅ Oui | `book/{id}/delete` |
| `deleteImage()` | Action POST | public | ✅ Oui | `book/delete-image` |
| `search()` | Action publique | public | ❌ Non | Aucune route |
| `toggleAvailability()` | Action POST | public | ✅ Oui | `book/{id}/toggle-availability` |
| `handleImageUpload()` | Helper privé | private | ✅ Oui | - |
| `handleImageUpdate()` | Helper privé | private | ✅ Oui | - |
| `findBookOrFail()` | Helper protected | protected | ✅ Oui | - |
| `ensureBookOwnership()` | Helper protected | protected | ✅ Oui | - |
| `findOwnBookOrFail()` | Helper protected | protected | ✅ Oui | - |
| `validate()` | Validation | private | ✅ Oui | - |
| `sanitize()` | Nettoyage | private | ✅ Oui | - |

---

## 📖 Détail des méthodes

### 1️⃣ `__construct()` - Constructeur

**Rôle :** Initialise les dépendances du contrôleur.

**Utilisation :** Appelé automatiquement à chaque création d'instance du contrôleur.

```php
public function __construct()
{
    $this->bookManager = $this->loadManager('Book');      // Charge BookManager
    $this->userManager = $this->loadManager('User');      // Charge UserManager
    $this->imageUploader = new ImageUploader();           // Instancie ImageUploader
}
```

**Pourquoi c'est nécessaire :**
- Prépare les outils nécessaires pour toutes les actions
- Pattern d'injection de dépendances

---

### 2️⃣ `index()` - Liste des livres disponibles

**Rôle :** Affiche tous les livres disponibles à l'échange (page publique).

**Route :** `nos-livres`

**Utilisation :** ✅ **UTILISÉE** - Page principale de consultation des livres.

```php
public function index(): void
```

**Fonctionnalités :**
- ✅ Récupère le terme de recherche (`$_GET['q']`)
- ✅ Exclut les livres de l'utilisateur connecté
- ✅ Affiche tous les livres disponibles OU résultats de recherche
- ✅ Accessible aux visiteurs non connectés

**Flux :**
1. Récupère le terme de recherche (si présent)
2. Identifie l'utilisateur connecté (si connecté)
3. Appelle `searchBooks()` si recherche, sinon `findAvailableBooks()`
4. Affiche la vue `book/list`

**💡 À GARDER** - C'est la page principale de l'application.

---

### 3️⃣ `create()` - Création d'un livre

**Rôle :** Traite le formulaire d'ajout d'un nouveau livre.

**Route :** `book/create` (POST uniquement)

**Utilisation :** ✅ **UTILISÉE** - Formulaire modal dans la page "Mon compte".

```php
public function create(): void
```

**Fonctionnalités :**
- 🔒 Requiert l'authentification (`requireAuth()`)
- 🛡️ Validation CSRF
- ✅ Validation des données (titre, auteur, description)
- 📸 Upload de l'image (ou placeholder si aucune)
- 💾 Enregistrement en base de données
- 🔄 Redirection avec message flash

**Flux :**
1. Vérifie que l'utilisateur est connecté
2. Vérifie que c'est une requête POST
3. Valide le token CSRF
4. Récupère et valide les données (`validate()`)
5. Gère l'upload d'image (`handleImageUpload()`)
6. Nettoie les données (`sanitize()`)
7. Crée le livre via `BookManager::createBook()`
8. Redirige vers "mon-compte" avec message de succès/erreur

**💡 À GARDER** - Fonctionnalité essentielle.

---

### 4️⃣ `show()` - Détail d'un livre

**Rôle :** Affiche la page de détail d'un livre spécifique.

**Route :** `livre/{id}`

**Utilisation :** ✅ **UTILISÉE** - Page de détail pour chaque livre.

```php
public function show(int $id): void
```

**Fonctionnalités :**
- 📖 Affiche les informations complètes du livre
- 👤 Affiche le propriétaire du livre
- 📚 Suggère 3 autres livres du même propriétaire
- ✉️ Permet de contacter le propriétaire (bouton)

**Flux :**
1. Récupère le livre (`findBookOrFail()`)
2. Récupère le propriétaire via `UserManager`
3. Récupère max 3 autres livres du propriétaire (excluant le livre actuel)
4. Affiche la vue `book/show`

**💡 À GARDER** - Page de détail indispensable.

---

### 5️⃣ `edit()` - Formulaire d'édition

**Rôle :** Affiche le formulaire de modification d'un livre.

**Route :** `book/{id}/edit` (GET)

**Utilisation :** ✅ **UTILISÉE** - Page d'édition d'un livre.

```php
public function edit(int $id): void
```

**Fonctionnalités :**
- 🔒 Requiert l'authentification
- 🔐 Vérifie la propriété du livre (`findOwnBookOrFail()`)
- 🎫 Génère un token CSRF pour le formulaire

**Flux :**
1. Vérifie que l'utilisateur est connecté
2. Vérifie que l'utilisateur est propriétaire du livre
3. Génère un token CSRF
4. Affiche la vue `book/edit` avec les données du livre

**💡 À GARDER** - Nécessaire pour modifier les livres.

---

### 6️⃣ `update()` - Mise à jour d'un livre

**Rôle :** Traite la soumission du formulaire d'édition.

**Route :** `book/{id}/update` (POST)

**Utilisation :** ✅ **UTILISÉE** - Traitement de la modification.

```php
public function update(int $id): void
```

**Fonctionnalités :**
- 🔒 Requiert l'authentification
- 🔐 Vérifie la propriété du livre
- 🛡️ Validation CSRF
- ✅ Validation des données
- 📸 Gestion de la mise à jour de l'image (optionnelle)
- 💾 Mise à jour en base de données

**Flux :**
1. Vérifie que c'est une requête POST
2. Vérifie la propriété du livre (`findOwnBookOrFail()`)
3. Valide le token CSRF
4. Récupère et valide les données
5. Gère l'upload d'image (`handleImageUpdate()`)
6. Nettoie les données
7. Met à jour via `BookManager::updateBook()`
8. Redirige avec message flash

**💡 À GARDER** - Fonctionnalité essentielle.

---

### 7️⃣ `delete()` - Suppression d'un livre

**Rôle :** Supprime un livre et son image.

**Route :** `book/{id}/delete` (POST)

**Utilisation :** ✅ **UTILISÉE** - Bouton de suppression dans "Mon compte".

```php
public function delete(int $id): void
```

**Fonctionnalités :**
- 🔒 Requiert l'authentification
- 🔐 Vérifie la propriété du livre
- 🛡️ Validation CSRF
- 🗑️ Supprime le livre de la base de données
- 📸 Supprime l'image physique du serveur

**Flux :**
1. Vérifie que c'est une requête POST
2. Vérifie la propriété du livre
3. Valide le token CSRF
4. Supprime le livre via `BookManager::deleteBook()`
5. Supprime l'image via `ImageUploader::delete()`
6. Redirige vers "mon-compte" avec message flash

**💡 À GARDER** - Fonctionnalité essentielle.

---

### 8️⃣ `deleteImage()` - Suppression de l'image uniquement

**Rôle :** Supprime l'image d'un livre et la remplace par le placeholder.

**Route :** `book/delete-image` (POST)

**Utilisation :** ✅ **UTILISÉE** - Bouton dans le formulaire d'édition.

```php
public function deleteImage(): void
```

**Fonctionnalités :**
- 🔒 Requiert l'authentification
- 🔐 Vérifie la propriété du livre
- 🛡️ Validation CSRF
- 📸 Supprime l'image mais garde le livre
- 🖼️ Remplace par l'image placeholder

**Flux :**
1. Récupère l'ID du livre depuis POST
2. Vérifie la propriété (`findOwnBookOrFail()`)
3. Valide le token CSRF
4. Supprime l'image physique
5. Met à jour le livre avec le placeholder
6. Redirige vers le formulaire d'édition

**💡 À GARDER** - Utile pour retirer une image sans supprimer le livre.

---

### 9️⃣ `search()` - Recherche de livres

**Rôle :** Page dédiée à la recherche de livres.

**Route :** ❌ **AUCUNE ROUTE DÉFINIE**

**Utilisation :** ❌ **NON UTILISÉE** - Aucune route ne pointe vers cette méthode.

```php
public function search(): void
```

**Problème :**
- La recherche est déjà intégrée dans `index()` via `$_GET['q']`
- Cette méthode fait exactement la même chose que `index()`
- Doublon inutile

**⚠️ À SUPPRIMER** - Fonctionnalité redondante avec `index()`.

---

### 🔟 `toggleAvailability()` - Changer la disponibilité

**Rôle :** Change le statut disponible/non disponible d'un livre.

**Route :** `book/{id}/toggle-availability` (POST)

**Utilisation :** ✅ **UTILISÉE** - Bouton switch dans "Mon compte".

```php
public function toggleAvailability(int $id): void
```

**Fonctionnalités :**
- 🔒 Requiert l'authentification
- 🔐 Vérifie la propriété du livre
- 🛡️ Validation CSRF
- 🔄 Inverse le statut de disponibilité (0 ↔ 1)

**Flux :**
1. Vérifie que c'est une requête POST
2. Vérifie la propriété du livre
3. Valide le token CSRF
4. Calcule le nouveau statut (inverse de l'actuel)
5. Met à jour via `BookManager::updateAvailability()`
6. Redirige avec message flash

**💡 À GARDER** - Fonctionnalité très utile pour gérer la disponibilité.

---

## 🛠️ Méthodes privées/protégées (Helpers)

### 1️⃣1️⃣ `handleImageUpload()` - Upload nouvelle image

**Rôle :** Gère l'upload d'une nouvelle image lors de la création d'un livre.

**Visibilité :** `private`

**Utilisation :** ✅ **UTILISÉE** - Appelée par `create()`

```php
private function handleImageUpload(?array $file, array &$errors): string
```

**Paramètres :**
- `$file` : Fichier uploadé depuis `$_FILES['image']`
- `&$errors` : Tableau d'erreurs (passé par référence)

**Retour :** Nom du fichier (nouveau ou placeholder)

**Flux :**
1. Par défaut, utilise le placeholder
2. Si fichier uploadé sans erreur :
   - Appelle `ImageUploader::upload()`
   - Si succès : utilise le nouveau nom
   - Si échec : ajoute l'erreur au tableau `$errors`
3. Retourne le nom final

**💡 À GARDER** - Évite la duplication de code dans `create()`.

---

### 1️⃣2️⃣ `handleImageUpdate()` - Mise à jour image existante

**Rôle :** Gère le remplacement d'une image lors de la modification d'un livre.

**Visibilité :** `private`

**Utilisation :** ✅ **UTILISÉE** - Appelée par `update()`

```php
private function handleImageUpdate(?array $file, Book $book, array &$errors): string
```

**Paramètres :**
- `$file` : Nouveau fichier uploadé (peut être null)
- `$book` : Livre existant (pour récupérer l'ancienne image)
- `&$errors` : Tableau d'erreurs (passé par référence)

**Retour :** Nom du fichier (nouveau ou ancien)

**Différence avec `handleImageUpload()` :**
- Conserve l'ancienne image par défaut (pas le placeholder)
- Supprime l'ancienne image si nouveau upload réussi

**Flux :**
1. Par défaut, garde l'ancienne image
2. Si nouveau fichier uploadé :
   - Upload le nouveau fichier
   - Si succès : supprime l'ancien et utilise le nouveau
   - Si échec : garde l'ancien et ajoute l'erreur
3. Retourne le nom final

**💡 À GARDER** - Évite la duplication de code dans `update()`.

---

### 1️⃣3️⃣ `findBookOrFail()` - Récupérer un livre ou échouer

**Rôle :** Récupère un livre et vérifie qu'il existe.

**Visibilité :** `protected`

**Utilisation :** ✅ **UTILISÉE** - Appelée 2 fois :
- `show()` - Affichage public
- `findOwnBookOrFail()` - Méthode combinée

```php
protected function findBookOrFail(int $id, string $redirectUrl = 'mon-compte'): ?Book
```

**Paramètres :**
- `$id` : ID du livre à récupérer
- `$redirectUrl` : URL de redirection si livre introuvable (défaut : 'mon-compte')

**Retour :** 
- `Book` si trouvé
- `null` si introuvable (avec redirection automatique)

**Flux :**
1. Récupère le livre via `BookManager::findById()`
2. Si inexistant :
   - Message flash d'erreur
   - Redirection
   - Retourne `null`
3. Si existe : retourne le livre

**💡 À GARDER** - Évite la répétition du code de vérification.

---

### 1️⃣4️⃣ `ensureBookOwnership()` - Vérifier la propriété

**Rôle :** Vérifie que l'utilisateur connecté est le propriétaire du livre.

**Visibilité :** `protected`

**Utilisation :** ✅ **UTILISÉE** - Appelée par `findOwnBookOrFail()`

```php
protected function ensureBookOwnership(Book $book, string $redirectUrl = 'mon-compte'): bool
```

**Paramètres :**
- `$book` : Livre à vérifier
- `$redirectUrl` : URL de redirection si non propriétaire (défaut : 'mon-compte')

**Retour :** 
- `true` si propriétaire
- `false` si non propriétaire (avec redirection automatique)

**Flux :**
1. Compare `$book->getUserId()` avec `Session::getUserId()`
2. Si différent :
   - Message flash d'erreur ("non autorisé")
   - Redirection
   - Retourne `false`
3. Si identique : retourne `true`

**💡 À GARDER** - Sécurité essentielle pour les opérations sur les livres.

---

### 1️⃣5️⃣ `findOwnBookOrFail()` - Récupérer son propre livre

**Rôle :** Méthode combinée qui vérifie existence ET propriété.

**Visibilité :** `protected`

**Utilisation :** ✅ **UTILISÉE** - Appelée 5 fois :
- `edit()` - Affichage formulaire
- `update()` - Modification
- `delete()` - Suppression
- `deleteImage()` - Suppression image
- `toggleAvailability()` - Changement disponibilité

```php
protected function findOwnBookOrFail(int $id, string $redirectUrl = 'mon-compte'): ?Book
```

**Paramètres :**
- `$id` : ID du livre
- `$redirectUrl` : URL de redirection en cas d'erreur

**Retour :** 
- `Book` si trouvé ET propriétaire
- `null` sinon (avec redirection)

**Flux :**
1. Appelle `findBookOrFail()` pour vérifier l'existence
2. Si inexistant : retourne `null`
3. Appelle `ensureBookOwnership()` pour vérifier la propriété
4. Si non propriétaire : retourne `null`
5. Si OK : retourne le livre

**💡 À GARDER** - Pratique et évite la répétition dans toutes les actions CRUD.

---

### 1️⃣6️⃣ `validate()` - Validation des données

**Rôle :** Valide les données d'un livre (titre, auteur, description).

**Visibilité :** `private`

**Utilisation :** ✅ **UTILISÉE** - Appelée 2 fois :
- `create()` - Création
- `update()` - Modification

```php
private function validate(array $data): array
```

**Paramètres :**
- `$data` : Tableau avec `['title', 'author', 'description']`

**Retour :** Tableau d'erreurs (vide si tout est valide)

**Règles de validation :**
- **Titre** :
  - ✅ Obligatoire
  - ✅ Max 255 caractères
- **Auteur** :
  - ✅ Obligatoire
  - ✅ Max 255 caractères
- **Description** :
  - ⚠️ Optionnelle
  - ✅ Max 1000 caractères (si remplie)

**💡 À GARDER** - Validation essentielle pour la qualité des données.

---

### 1️⃣7️⃣ `sanitize()` - Nettoyage des données

**Rôle :** Nettoie et prépare les données avant l'enregistrement en base.

**Visibilité :** `private`

**Utilisation :** ✅ **UTILISÉE** - Appelée 2 fois :
- `create()` - Création
- `update()` - Modification

```php
private function sanitize(array $data): array
```

**Paramètres :**
- `$data` : Données brutes du formulaire

**Retour :** Données nettoyées prêtes pour la base

**Nettoyages effectués :**
- `trim()` sur titre, auteur, description (retire espaces)
- Conversion `is_available` : `'1'` → `1`, sinon → `0`

**💡 À GARDER** - Sécurité et cohérence des données.

---

## ✅ Résumé de l'audit

### 📊 Statistiques

- **Total des méthodes :** 17
- **Méthodes publiques (actions) :** 10
- **Méthodes privées/protégées (helpers) :** 7
- **Méthodes utilisées :** 16 ✅
- **Méthodes inutilisées :** 1 ❌

### ❌ Méthode à supprimer

**`search()`** - Raisons :
1. Aucune route définie dans `routes.php`
2. Fonctionnalité déjà intégrée dans `index()` (même code)
3. Doublon inutile qui ajoute de la confusion

### ✅ Méthodes à conserver (16)

| Catégorie | Méthodes | Justification |
|-----------|----------|---------------|
| **Initialisation** | `__construct()` | Nécessaire |
| **CRUD public** | `index()`, `show()` | Pages publiques essentielles |
| **CRUD authentifié** | `create()`, `edit()`, `update()`, `delete()` | Fonctionnalités principales |
| **Fonctionnalités avancées** | `deleteImage()`, `toggleAvailability()` | Très utiles pour l'UX |
| **Helpers upload** | `handleImageUpload()`, `handleImageUpdate()` | Évitent duplication |
| **Helpers sécurité** | `findBookOrFail()`, `ensureBookOwnership()`, `findOwnBookOrFail()` | Sécurité et réutilisabilité |
| **Helpers validation** | `validate()`, `sanitize()` | Qualité et sécurité des données |

---

## 🎯 Recommandations

### ✅ Points forts
- Architecture claire avec séparation des responsabilités
- Sécurité bien gérée (CSRF, propriété, authentification)
- Réutilisabilité via helpers (`findOwnBookOrFail`, etc.)
- Validation robuste des données

### 🔧 Améliorations proposées
1. **Supprimer `search()`** - Redondante avec `index()`
2. **Mettre à jour le commentaire de classe** - Retirer la mention de BookValidator (déjà intégré)

### 📝 Code à supprimer

```php
// À SUPPRIMER : Méthode search() complète (lignes ~274-286)
public function search(): void
{
    $searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
    $excludeUserId = Session::isLoggedIn() ? Session::getUserId() : null;

    $books = !empty($searchTerm)
        ? $this->bookManager->searchBooks($searchTerm, $excludeUserId)
        : [];

    $this->render('book/search', [
        'books' => $books,
        'searchTerm' => $searchTerm,
        'title' => 'Recherche de livres'
    ]);
}
```

---

## 📚 Conclusion

Le **BookController** est très bien structuré et toutes les méthodes sont utiles **sauf `search()`**. Le code est propre, sécurisé et facile à maintenir. L'intégration de BookValidator directement dans le contrôleur était une bonne décision car il n'est utilisé que dans ce contexte.

**Score de qualité : 9/10** ⭐

Points à améliorer :
- Supprimer la méthode `search()` redondante
- Mettre à jour le commentaire de classe
