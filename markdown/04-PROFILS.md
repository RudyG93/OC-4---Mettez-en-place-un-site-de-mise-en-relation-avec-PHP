# 👤 04 - GESTION DES PROFILS

## Vue d'ensemble

Le système de profils permet à chaque utilisateur de :
- ✅ Voir et modifier son profil privé
- ✅ Personnaliser son avatar et sa biographie
- ✅ Consulter les profils publics des autres utilisateurs
- ✅ Voir les livres associés à un utilisateur

---

## Architecture

### Composants

```
ProfileController (app/controller/)
    ├── view() - Mon profil (privé)
    ├── edit() - Formulaire modification
    ├── update() - Traitement modification
    └── show($id) - Profil public

UserManager (app/model/manager/)
    ├── getUserById($id) - Récupérer utilisateur
    ├── updateUser($id, $data) - Mettre à jour
    └── getBookCount($userId) - Compter livres

User (app/model/entity/)
    ├── getId(), getUsername(), getEmail()
    ├── getAvatar(), getBio()
    └── getCreatedAt()
```

---

## Mon profil (privé)

### Route

```
GET /mon-compte
Controller: ProfileController::view()
Vue: app/view/profile/view.php
Protection: Requiert authentification
```

### Fonctionnement

```php
public function view() {
    // Vérifier authentification
    requireAuth();
    
    // Récupérer l'utilisateur connecté
    $userId = Session::get('user_id');
    $user = $this->userManager->getUserById($userId);
    
    // Récupérer statistiques
    $bookCount = $this->userManager->getBookCount($userId);
    
    // Afficher la vue
    $this->render('profile/view', [
        'user' => $user,
        'bookCount' => $bookCount
    ]);
}
```

### Contenu affiché

#### Informations personnelles
- **Pseudo** : Nom d'utilisateur
- **Email** : Adresse email (privée)
- **Avatar** : Photo de profil
- **Bio** : Description personnelle
- **Membre depuis** : Date d'inscription

#### Statistiques
- Nombre de livres dans la bibliothèque
- Nombre de livres disponibles
- Nombre de conversations

#### Actions
- Bouton "Modifier mon profil"
- Lien "Voir ma bibliothèque complète"
- Lien "Voir mes messages"

### Exemple de vue

```php
<!-- app/view/profile/view.php -->
<div class="profile-container">
    <div class="profile-header">
        <div class="avatar-section">
            <?php if ($user->getAvatar()): ?>
                <img src="<?= url('uploads/' . h($user->getAvatar())) ?>" 
                     alt="Avatar de <?= h($user->getUsername()) ?>">
            <?php else: ?>
                <div class="avatar-placeholder">
                    <?= strtoupper(substr($user->getUsername(), 0, 1)) ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="profile-info">
            <h1><?= h($user->getUsername()) ?></h1>
            <p class="email"><?= h($user->getEmail()) ?></p>
            <p class="member-since">
                Membre depuis <?= formatDate($user->getCreatedAt()) ?>
            </p>
        </div>
    </div>
    
    <div class="profile-bio">
        <h2>À propos</h2>
        <?php if ($user->getBio()): ?>
            <p><?= nl2br(h($user->getBio())) ?></p>
        <?php else: ?>
            <p class="empty">Aucune biographie renseignée</p>
        <?php endif; ?>
    </div>
    
    <div class="profile-stats">
        <div class="stat">
            <span class="number"><?= $bookCount ?></span>
            <span class="label">Livre(s)</span>
        </div>
    </div>
    
    <div class="profile-actions">
        <a href="<?= url('mon-compte/modifier') ?>" class="btn btn-primary">
            Modifier mon profil
        </a>
        <a href="<?= url('book/my-books') ?>" class="btn btn-secondary">
            Ma bibliothèque
        </a>
    </div>
</div>
```

---

## Modification du profil

### Routes

```
GET  /mon-compte/modifier  → Afficher formulaire
POST /mon-compte/update    → Traiter la modification
```

### Affichage du formulaire

```php
public function edit() {
    requireAuth();
    
    $userId = Session::get('user_id');
    $user = $this->userManager->getUserById($userId);
    
    $this->render('profile/edit', ['user' => $user]);
}
```

### Formulaire de modification

```php
<form method="POST" action="<?= url('mon-compte/update') ?>" 
      enctype="multipart/form-data">
    
    <!-- Token CSRF -->
    <input type="hidden" name="csrf_token" 
           value="<?= Session::generateCsrfToken() ?>">
    
    <!-- Pseudo -->
    <div class="form-group">
        <label for="username">Pseudo *</label>
        <input type="text" id="username" name="username" 
               value="<?= h($user->getUsername()) ?>" 
               required minlength="3">
    </div>
    
    <!-- Email -->
    <div class="form-group">
        <label for="email">Email *</label>
        <input type="email" id="email" name="email" 
               value="<?= h($user->getEmail()) ?>" 
               required>
    </div>
    
    <!-- Bio -->
    <div class="form-group">
        <label for="bio">Biographie</label>
        <textarea id="bio" name="bio" rows="5" 
                  maxlength="500"><?= h($user->getBio()) ?></textarea>
        <small>Maximum 500 caractères</small>
    </div>
    
    <!-- Avatar actuel -->
    <?php if ($user->getAvatar()): ?>
        <div class="current-avatar">
            <img src="<?= url('uploads/' . h($user->getAvatar())) ?>" 
                 alt="Avatar actuel">
            <label>
                <input type="checkbox" name="remove_avatar" value="1">
                Supprimer l'avatar
            </label>
        </div>
    <?php endif; ?>
    
    <!-- Nouvel avatar -->
    <div class="form-group">
        <label for="avatar">Changer d'avatar</label>
        <input type="file" id="avatar" name="avatar" 
               accept="image/jpeg,image/png,image/gif">
        <small>JPG, PNG ou GIF - Max 2 Mo</small>
    </div>
    
    <!-- Changement mot de passe (optionnel) -->
    <fieldset>
        <legend>Changer de mot de passe (optionnel)</legend>
        
        <div class="form-group">
            <label for="current_password">Mot de passe actuel</label>
            <input type="password" id="current_password" 
                   name="current_password">
        </div>
        
        <div class="form-group">
            <label for="new_password">Nouveau mot de passe</label>
            <input type="password" id="new_password" 
                   name="new_password" minlength="8">
        </div>
        
        <div class="form-group">
            <label for="new_password_confirm">Confirmer nouveau mot de passe</label>
            <input type="password" id="new_password_confirm" 
                   name="new_password_confirm">
        </div>
    </fieldset>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            Enregistrer les modifications
        </button>
        <a href="<?= url('mon-compte') ?>" class="btn btn-secondary">
            Annuler
        </a>
    </div>
</form>
```

### Traitement de la modification

```php
public function update() {
    requireAuth();
    
    // Vérifier le token CSRF
    if (!Session::validateCsrfToken($_POST['csrf_token'])) {
        Session::setFlash('error', 'Token de sécurité invalide');
        return $this->redirect('mon-compte/modifier');
    }
    
    $userId = Session::get('user_id');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    
    // Validation
    if (empty($username) || empty($email)) {
        Session::setFlash('error', 'Le pseudo et l\'email sont requis');
        return $this->redirect('mon-compte/modifier');
    }
    
    if (strlen($username) < 3) {
        Session::setFlash('error', 'Le pseudo doit faire au moins 3 caractères');
        return $this->redirect('mon-compte/modifier');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        Session::setFlash('error', 'Email invalide');
        return $this->redirect('mon-compte/modifier');
    }
    
    // Vérifier unicité email (si changé)
    $currentUser = $this->userManager->getUserById($userId);
    if ($email !== $currentUser->getEmail()) {
        $existingUser = $this->userManager->getUserByEmail($email);
        if ($existingUser && $existingUser->getId() !== $userId) {
            Session::setFlash('error', 'Cet email est déjà utilisé');
            return $this->redirect('mon-compte/modifier');
        }
    }
    
    // Préparer les données à mettre à jour
    $updateData = [
        'username' => $username,
        'email' => $email,
        'bio' => $bio
    ];
    
    // Gestion de l'avatar
    $avatarPath = $this->handleAvatarUpload($userId);
    if ($avatarPath) {
        $updateData['avatar'] = $avatarPath;
    } elseif (isset($_POST['remove_avatar'])) {
        $updateData['avatar'] = null;
    }
    
    // Changement de mot de passe (optionnel)
    if (!empty($_POST['current_password'])) {
        $passwordChanged = $this->handlePasswordChange(
            $userId,
            $_POST['current_password'],
            $_POST['new_password'] ?? '',
            $_POST['new_password_confirm'] ?? ''
        );
        
        if ($passwordChanged === false) {
            return $this->redirect('mon-compte/modifier');
        }
        
        if ($passwordChanged === true) {
            $updateData['password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        }
    }
    
    // Mettre à jour en BDD
    $this->userManager->updateUser($userId, $updateData);
    
    // Mettre à jour la session
    Session::set('username', $username);
    Session::set('email', $email);
    
    Session::setFlash('success', 'Profil mis à jour avec succès !');
    $this->redirect('mon-compte');
}
```

### Upload d'avatar

```php
private function handleAvatarUpload($userId) {
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // Pas de fichier uploadé
    }
    
    if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        Session::setFlash('error', 'Erreur lors de l\'upload de l\'avatar');
        return null;
    }
    
    // Vérifier la taille
    if ($_FILES['avatar']['size'] > MAX_FILE_SIZE) {
        Session::setFlash('error', 'L\'avatar est trop volumineux (max 2 Mo)');
        return null;
    }
    
    // Vérifier l'extension
    $extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        Session::setFlash('error', 'Format d\'image non autorisé (JPG, PNG ou GIF uniquement)');
        return null;
    }
    
    // Générer un nom unique
    $filename = 'avatar_' . $userId . '_' . time() . '.' . $extension;
    $uploadPath = '../public/uploads/avatars/';
    
    // Créer le dossier si nécessaire
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }
    
    // Déplacer le fichier
    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath . $filename)) {
        return 'avatars/' . $filename;
    }
    
    Session::setFlash('error', 'Impossible de sauvegarder l\'avatar');
    return null;
}
```

### Changement de mot de passe

```php
private function handlePasswordChange($userId, $currentPassword, $newPassword, $newPasswordConfirm) {
    // Si aucun champ mot de passe rempli, ne rien faire
    if (empty($currentPassword) && empty($newPassword)) {
        return null;
    }
    
    // Vérifier que tous les champs sont remplis
    if (empty($currentPassword) || empty($newPassword) || empty($newPasswordConfirm)) {
        Session::setFlash('error', 'Tous les champs de mot de passe doivent être remplis');
        return false;
    }
    
    // Vérifier le mot de passe actuel
    $user = $this->userManager->getUserById($userId);
    if (!password_verify($currentPassword, $user->getPassword())) {
        Session::setFlash('error', 'Mot de passe actuel incorrect');
        return false;
    }
    
    // Vérifier longueur nouveau mot de passe
    if (strlen($newPassword) < 8) {
        Session::setFlash('error', 'Le nouveau mot de passe doit faire au moins 8 caractères');
        return false;
    }
    
    // Vérifier confirmation
    if ($newPassword !== $newPasswordConfirm) {
        Session::setFlash('error', 'Les nouveaux mots de passe ne correspondent pas');
        return false;
    }
    
    return true; // Mot de passe peut être changé
}
```

---

## Profil public

### Route

```
GET /profil/{id}
Controller: ProfileController::show($id)
Vue: app/view/profile/show.php
Protection: Aucune (accessible à tous)
```

### Fonctionnement

```php
public function show($id) {
    // Récupérer l'utilisateur
    $user = $this->userManager->getUserById($id);
    
    if (!$user) {
        return $this->render('error/404', [], 'error');
    }
    
    // Récupérer les livres de l'utilisateur
    $books = $this->bookManager->getBooksByUserId($id);
    $bookCount = count($books);
    
    // Vérifier si c'est son propre profil
    $isOwnProfile = (Session::get('user_id') == $id);
    
    $this->render('profile/show', [
        'user' => $user,
        'books' => $books,
        'bookCount' => $bookCount,
        'isOwnProfile' => $isOwnProfile
    ]);
}
```

### Contenu affiché

#### Informations publiques
- Pseudo
- Avatar
- Biographie
- Membre depuis
- **Pas d'email** (information privée)

#### Livres de l'utilisateur
- Liste des livres disponibles
- Aperçu de la bibliothèque

#### Actions
- **Si c'est son profil** : "Modifier mon profil"
- **Sinon** : "Envoyer un message"

### Exemple de vue

```php
<div class="public-profile">
    <div class="profile-header">
        <div class="avatar">
            <?php if ($user->getAvatar()): ?>
                <img src="<?= url('uploads/' . h($user->getAvatar())) ?>" 
                     alt="<?= h($user->getUsername()) ?>">
            <?php else: ?>
                <div class="avatar-placeholder">
                    <?= strtoupper(substr($user->getUsername(), 0, 1)) ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="profile-info">
            <h1><?= h($user->getUsername()) ?></h1>
            <p>Membre depuis <?= formatDate($user->getCreatedAt()) ?></p>
        </div>
    </div>
    
    <?php if ($user->getBio()): ?>
        <div class="profile-bio">
            <h2>À propos</h2>
            <p><?= nl2br(h($user->getBio())) ?></p>
        </div>
    <?php endif; ?>
    
    <div class="profile-books">
        <h2>Livres (<?= $bookCount ?>)</h2>
        
        <?php if (empty($books)): ?>
            <p class="empty">Aucun livre dans la bibliothèque</p>
        <?php else: ?>
            <div class="books-grid">
                <?php foreach ($books as $book): ?>
                    <div class="book-card">
                        <a href="<?= url('livre/' . $book->getId()) ?>">
                            <?php if ($book->getImage()): ?>
                                <img src="<?= url('uploads/' . h($book->getImage())) ?>" 
                                     alt="<?= h($book->getTitle()) ?>">
                            <?php endif; ?>
                            <h3><?= h($book->getTitle()) ?></h3>
                            <p class="author"><?= h($book->getAuthor()) ?></p>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="profile-actions">
        <?php if ($isOwnProfile): ?>
            <a href="<?= url('mon-compte/modifier') ?>" class="btn btn-primary">
                Modifier mon profil
            </a>
        <?php elseif (Session::get('user_id')): ?>
            <a href="<?= url('messages/compose/' . $user->getId()) ?>" class="btn btn-primary">
                Envoyer un message
            </a>
        <?php else: ?>
            <a href="<?= url('login') ?>" class="btn btn-primary">
                Se connecter pour contacter
            </a>
        <?php endif; ?>
    </div>
</div>
```

---

## Méthodes UserManager

### getUserById()

```php
public function getUserById($id) {
    $stmt = $this->db->prepare(
        "SELECT * FROM users WHERE id = ?"
    );
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    
    if (!$data) {
        return null;
    }
    
    $user = new User();
    $user->hydrate($data);
    return $user;
}
```

### updateUser()

```php
public function updateUser($id, $data) {
    $fields = [];
    $values = [];
    
    foreach ($data as $field => $value) {
        $fields[] = "$field = ?";
        $values[] = $value;
    }
    
    $values[] = $id;
    
    $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $this->db->prepare($sql);
    
    return $stmt->execute($values);
}
```

### getBookCount()

```php
public function getBookCount($userId) {
    $stmt = $this->db->prepare(
        "SELECT COUNT(*) as count FROM books WHERE user_id = ?"
    );
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    return $result['count'] ?? 0;
}
```

---

## Sécurité

### Protection des profils privés

```php
// Vérifier que l'utilisateur modifie bien SON profil
public function edit() {
    requireAuth();
    $userId = Session::get('user_id');
    // Ne peut modifier que son propre profil
}
```

### Validation des uploads

- Vérifier type MIME
- Limiter la taille
- Générer noms uniques
- Stocker hors de la racine web si possible

### Échappement des données

```php
// Toujours échapper dans les vues
<?= h($user->getUsername()) ?>
<?= h($user->getBio()) ?>
```

---

## Tests

### Tester le profil privé

1. Se connecter
2. Aller sur `/mon-compte`
3. Vérifier affichage informations
4. Vérifier lien "Modifier"

### Tester la modification

1. Cliquer "Modifier mon profil"
2. Changer le pseudo
3. Ajouter une bio
4. Uploader un avatar
5. Sauvegarder
6. Vérifier changements appliqués

### Tester le profil public

1. Depuis `/nos-livres`, cliquer sur un livre
2. Cliquer sur le nom du propriétaire
3. Vérifier affichage profil public
4. Vérifier que l'email n'est PAS affiché
5. Vérifier bouton "Envoyer un message"

---

## Améliorations possibles

- Recadrage d'images
- Validation côté client en temps réel
- Prévisualisation avatar avant upload
- Historique des modifications
- Statistiques étendues (taux d'échange, etc.)
- Badges et récompenses
- Paramètres de confidentialité

---

## Résumé

Le système de profils offre :

✅ **Personnalisation** : Avatar, bio, informations
✅ **Vie privée** : Email privé, profils publics/privés
✅ **Sécurité** : Upload sécurisé, validation
✅ **Flexibilité** : Modification facile

**Prochaine étape** : Consulter **05-LIVRES.md** pour la gestion de la bibliothèque.
