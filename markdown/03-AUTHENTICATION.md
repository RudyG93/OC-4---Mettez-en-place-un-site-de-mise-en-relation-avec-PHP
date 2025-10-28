# 🔐 03 - SYSTÈME D'AUTHENTIFICATION

## Vue d'ensemble

Le système d'authentification de TomTroc permet :
- ✅ Inscription de nouveaux utilisateurs
- ✅ Connexion sécurisée
- ✅ Déconnexion
- ✅ Protection des pages privées
- ✅ Gestion des sessions
- ✅ Stockage sécurisé des mots de passe

---

## Architecture

### Composants

```
AuthController (app/controller/)
    ├── register() - Inscription
    ├── login() - Connexion
    └── logout() - Déconnexion

UserManager (app/model/manager/)
    ├── createUser() - Créer utilisateur
    ├── getUserByEmail() - Récupérer par email
    └── authenticate() - Vérifier credentials

User (app/model/entity/)
    ├── Propriétés (id, username, email, password...)
    └── Méthodes getter/setter

Session (app/core/)
    ├── Gestion session PHP
    ├── Tokens CSRF
    └── Messages flash
```

---

## Inscription

### Route

```
GET/POST /register
Controller: AuthController::register()
Vue: app/view/auth/register.php
```

### Processus d'inscription

#### 1. Affichage du formulaire

```php
public function register() {
    // Si déjà connecté, rediriger
    if (Session::get('user_id')) {
        return $this->redirect('mon-compte');
    }
    
    // Afficher le formulaire
    $this->render('auth/register');
}
```

#### 2. Soumission du formulaire

**Champs requis** :
- `username` : Nom d'utilisateur (min 3 caractères)
- `email` : Email valide
- `password` : Mot de passe (min 8 caractères)
- `password_confirm` : Confirmation mot de passe
- `csrf_token` : Token de sécurité

**Validation** :
```php
// Vérifier le token CSRF
if (!Session::validateCsrfToken($_POST['csrf_token'])) {
    Session::setFlash('error', 'Token de sécurité invalide');
    return $this->redirect('register');
}

// Vérifier les champs requis
if (empty($username) || empty($email) || empty($password)) {
    Session::setFlash('error', 'Tous les champs sont requis');
    return $this->redirect('register');
}

// Vérifier longueur username
if (strlen($username) < 3) {
    Session::setFlash('error', 'Le pseudo doit faire au moins 3 caractères');
    return $this->redirect('register');
}

// Vérifier format email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Session::setFlash('error', 'Email invalide');
    return $this->redirect('register');
}

// Vérifier longueur mot de passe
if (strlen($password) < 8) {
    Session::setFlash('error', 'Le mot de passe doit faire au moins 8 caractères');
    return $this->redirect('register');
}

// Vérifier confirmation mot de passe
if ($password !== $passwordConfirm) {
    Session::setFlash('error', 'Les mots de passe ne correspondent pas');
    return $this->redirect('register');
}
```

#### 3. Vérification unicité email

```php
$existingUser = $this->userManager->getUserByEmail($email);
if ($existingUser) {
    Session::setFlash('error', 'Cet email est déjà utilisé');
    return $this->redirect('register');
}
```

#### 4. Création de l'utilisateur

```php
$userId = $this->userManager->createUser([
    'username' => $username,
    'email' => $email,
    'password' => $password  // Sera hashé dans createUser()
]);
```

**Dans UserManager::createUser()** :

```php
public function createUser($data) {
    // Hasher le mot de passe
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Insérer dans la BDD
    $stmt = $this->db->prepare(
        "INSERT INTO users (username, email, password, created_at) 
         VALUES (?, ?, ?, NOW())"
    );
    
    $stmt->execute([
        $data['username'],
        $data['email'],
        $hashedPassword
    ]);
    
    return $this->db->lastInsertId();
}
```

#### 5. Connexion automatique

```php
// Stocker l'utilisateur en session
Session::set('user_id', $userId);
Session::set('username', $username);
Session::set('email', $email);

Session::setFlash('success', 'Inscription réussie ! Bienvenue ' . $username);
$this->redirect('mon-compte');
```

### Formulaire d'inscription

```html
<form method="POST" action="<?= url('register') ?>">
    <!-- Token CSRF -->
    <input type="hidden" name="csrf_token" 
           value="<?= Session::generateCsrfToken() ?>">
    
    <!-- Pseudo -->
    <div class="form-group">
        <label for="username">Pseudo</label>
        <input type="text" id="username" name="username" 
               required minlength="3">
    </div>
    
    <!-- Email -->
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
    </div>
    
    <!-- Mot de passe -->
    <div class="form-group">
        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" 
               required minlength="8">
    </div>
    
    <!-- Confirmation -->
    <div class="form-group">
        <label for="password_confirm">Confirmer le mot de passe</label>
        <input type="password" id="password_confirm" 
               name="password_confirm" required>
    </div>
    
    <button type="submit">S'inscrire</button>
</form>
```

---

## Connexion

### Route

```
GET/POST /login
Controller: AuthController::login()
Vue: app/view/auth/login.php
```

### Processus de connexion

#### 1. Affichage du formulaire

```php
public function login() {
    // Si déjà connecté, rediriger
    if (Session::get('user_id')) {
        return $this->redirect('mon-compte');
    }
    
    $this->render('auth/login');
}
```

#### 2. Soumission du formulaire

**Champs requis** :
- `email` : Email
- `password` : Mot de passe
- `csrf_token` : Token CSRF

**Validation** :
```php
// Vérifier token CSRF
if (!Session::validateCsrfToken($_POST['csrf_token'])) {
    Session::setFlash('error', 'Token invalide');
    return $this->redirect('login');
}

// Vérifier champs remplis
if (empty($email) || empty($password)) {
    Session::setFlash('error', 'Email et mot de passe requis');
    return $this->redirect('login');
}
```

#### 3. Vérification des identifiants

```php
$user = $this->userManager->authenticate($email, $password);

if (!$user) {
    Session::setFlash('error', 'Email ou mot de passe incorrect');
    return $this->redirect('login');
}
```

**Dans UserManager::authenticate()** :

```php
public function authenticate($email, $password) {
    // Récupérer l'utilisateur par email
    $stmt = $this->db->prepare(
        "SELECT * FROM users WHERE email = ?"
    );
    $stmt->execute([$email]);
    $data = $stmt->fetch();
    
    if (!$data) {
        return null; // Email non trouvé
    }
    
    // Vérifier le mot de passe
    if (!password_verify($password, $data['password'])) {
        return null; // Mot de passe incorrect
    }
    
    // Créer et retourner l'entité User
    $user = new User();
    $user->hydrate($data);
    return $user;
}
```

#### 4. Création de la session

```php
// Stocker les infos utilisateur
Session::set('user_id', $user->getId());
Session::set('username', $user->getUsername());
Session::set('email', $user->getEmail());

// Message de succès
Session::setFlash('success', 'Connexion réussie !');

// Rediriger vers le compte
$this->redirect('mon-compte');
```

### Formulaire de connexion

```html
<form method="POST" action="<?= url('login') ?>">
    <input type="hidden" name="csrf_token" 
           value="<?= Session::generateCsrfToken() ?>">
    
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
    </div>
    
    <div class="form-group">
        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" required>
    </div>
    
    <button type="submit">Se connecter</button>
    
    <p>Pas encore de compte ? 
       <a href="<?= url('register') ?>">S'inscrire</a>
    </p>
</form>
```

---

## Déconnexion

### Route

```
GET /logout
Controller: AuthController::logout()
```

### Processus

```php
public function logout() {
    // Détruire toutes les données de session
    Session::destroy();
    
    // Message de confirmation
    Session::setFlash('success', 'Vous êtes déconnecté');
    
    // Rediriger vers l'accueil
    $this->redirect('home');
}
```

**Dans Session::destroy()** :

```php
public static function destroy() {
    // Supprimer toutes les variables
    $_SESSION = [];
    
    // Détruire le cookie de session
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Détruire la session
    session_destroy();
    
    // Redémarrer une nouvelle session
    session_start();
}
```

---

## Protection des pages

### Vérifier si l'utilisateur est connecté

#### Méthode 1 : Dans le contrôleur

```php
public function myBooks() {
    // Vérifier si connecté
    if (!Session::get('user_id')) {
        Session::setFlash('error', 'Vous devez être connecté');
        return $this->redirect('login');
    }
    
    // Suite du code...
}
```

#### Méthode 2 : Helper requireAuth()

```php
public function myBooks() {
    // Lance une exception si non connecté
    requireAuth();
    
    // Suite du code...
}
```

**Dans helpers.php** :

```php
function requireAuth() {
    if (!Session::get('user_id')) {
        Session::setFlash('error', 'Vous devez être connecté');
        redirect('login');
        exit;
    }
}
```

### Vérifier la propriété d'une ressource

```php
public function edit($id) {
    requireAuth(); // Doit être connecté
    
    $book = $this->bookManager->getBookById($id);
    
    // Vérifier que c'est bien son livre
    if ($book->getUserId() !== Session::get('user_id')) {
        Session::setFlash('error', 'Vous ne pouvez pas modifier ce livre');
        return $this->redirect('book/my-books');
    }
    
    // Suite du code...
}
```

---

## Sécurité des mots de passe

### Hachage avec password_hash()

```php
// Lors de l'inscription
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// PASSWORD_DEFAULT utilise bcrypt
// Génère automatiquement un salt unique
// Coût adaptatif selon la puissance du serveur
```

**Résultat** :
```
$2y$10$abcdefghijklmnopqrstuv...
└┬┘└┬┘└──────┬─────────┘└───┬───┘
 │  │       │              │
 │  │       │              └─ Hash (31 caractères)
 │  │       └──────────────── Salt (22 caractères)
 │  └──────────────────────── Coût (10)
 └─────────────────────────── Algorithme (bcrypt)
```

### Vérification avec password_verify()

```php
// Lors de la connexion
if (password_verify($passwordSaisi, $passwordHashé)) {
    // Mot de passe correct
} else {
    // Mot de passe incorrect
}
```

### Bonnes pratiques

✅ **À FAIRE** :
- Utiliser `password_hash()` avec `PASSWORD_DEFAULT`
- Ne jamais stocker les mots de passe en clair
- Hasher côté serveur uniquement
- Exiger minimum 8 caractères

❌ **À ÉVITER** :
- MD5 ou SHA1 (obsolètes)
- Hash sans salt
- Stocker les mots de passe en clair
- Envoyer les mots de passe par email

---

## Gestion des sessions

### Configuration sécurisée

**Dans Session.php (constructeur)** :

```php
public function __construct() {
    if (session_status() === PHP_SESSION_NONE) {
        // Configuration sécurisée
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_samesite', 'Lax');
        
        session_start();
    }
}
```

**Explications** :
- `cookie_httponly` : Empêche JavaScript d'accéder au cookie
- `use_only_cookies` : N'accepte que les cookies (pas d'URL)
- `cookie_samesite` : Protection CSRF supplémentaire

### Stocker des données

```php
// Stocker
Session::set('user_id', 42);
Session::set('cart', ['item1', 'item2']);

// Récupérer
$userId = Session::get('user_id');
$cart = Session::get('cart', []); // Valeur par défaut

// Vérifier existence
if (Session::has('user_id')) {
    // Utilisateur connecté
}

// Supprimer
Session::remove('user_id');
```

### Messages flash

Messages affichés une seule fois :

```php
// Définir
Session::setFlash('success', 'Opération réussie !');
Session::setFlash('error', 'Une erreur est survenue');
Session::setFlash('info', 'Information importante');

// Afficher (dans la vue)
<?php if ($flash = Session::getFlash('success')): ?>
    <div class="alert alert-success"><?= h($flash) ?></div>
<?php endif; ?>

// Le message est automatiquement supprimé après affichage
```

---

## Protection CSRF

### Principe

**CSRF** (Cross-Site Request Forgery) : Attaque où un site malveillant fait exécuter une action à votre insu.

**Protection** : Token unique par session, vérifié à chaque requête POST.

### Génération du token

```php
// Génère un token aléatoire unique
$token = Session::generateCsrfToken();
```

**Dans Session.php** :

```php
public static function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
```

### Utilisation dans les formulaires

```html
<form method="POST">
    <input type="hidden" name="csrf_token" 
           value="<?= Session::generateCsrfToken() ?>">
    <!-- Autres champs -->
</form>
```

### Validation côté serveur

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token
    if (!Session::validateCsrfToken($_POST['csrf_token'])) {
        Session::setFlash('error', 'Token de sécurité invalide');
        return $this->redirect('form-page');
    }
    
    // Traiter le formulaire...
}
```

**Dans Session.php** :

```php
public static function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) 
        && hash_equals($_SESSION['csrf_token'], $token);
}
```

**Note** : `hash_equals()` évite les attaques par timing.

---

## Navigation conditionnelle

### Dans le layout (header)

```php
<?php if (Session::get('user_id')): ?>
    <!-- Utilisateur connecté -->
    <nav>
        <a href="<?= url('mon-compte') ?>">Mon compte</a>
        <a href="<?= url('book/my-books') ?>">Ma bibliothèque</a>
        <a href="<?= url('messages') ?>">
            Messages
            <?php if ($unreadCount = Session::get('unread_messages', 0)): ?>
                <span class="badge"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= url('logout') ?>">Déconnexion</a>
    </nav>
<?php else: ?>
    <!-- Utilisateur non connecté -->
    <nav>
        <a href="<?= url('login') ?>">Connexion</a>
        <a href="<?= url('register') ?>">Inscription</a>
    </nav>
<?php endif; ?>
```

---

## Tests et débogage

### Tester l'inscription

1. Aller sur `/register`
2. Remplir le formulaire
3. Vérifier la BDD : `SELECT * FROM users ORDER BY id DESC LIMIT 1`
4. Vérifier que le mot de passe est hashé

### Tester la connexion

1. Aller sur `/login`
2. Email : `alice@example.com`
3. Password : `password123`
4. Vérifier redirection vers `/mon-compte`
5. Vérifier session : `var_dump($_SESSION)`

### Tester la déconnexion

1. Aller sur `/logout`
2. Vérifier redirection vers `/`
3. Vérifier session vide
4. Vérifier impossibilité d'accéder à `/mon-compte`

### Déboguer les sessions

```php
// Voir le contenu de la session
echo '<pre>';
var_dump($_SESSION);
echo '</pre>';

// Voir l'ID de session
echo 'Session ID: ' . session_id();
```

---

## Erreurs courantes

### "Headers already sent"

**Cause** : Sortie (echo, espace) avant session_start() ou redirect()

**Solution** :
- Vérifier qu'il n'y a pas d'espace avant `<?php`
- Utiliser `ob_start()` en début de script

### "Token de sécurité invalide"

**Cause** : Token CSRF manquant ou incorrect

**Solution** :
- Vérifier que le formulaire contient le champ `csrf_token`
- Vérifier que la validation est bien appelée

### "Call to undefined function password_hash()"

**Cause** : PHP < 5.5

**Solution** : Mettre à jour PHP (minimum 7.4, recommandé 8.0+)

---

## Améliorations possibles

### Mot de passe oublié

1. Formulaire "Mot de passe oublié"
2. Génération token unique temporaire
3. Envoi email avec lien
4. Page réinitialisation avec token

### Connexion persistante ("Se souvenir de moi")

1. Cookie avec token longue durée
2. Table `remember_tokens` en BDD
3. Vérification du token au chargement

### Authentification à deux facteurs (2FA)

1. Génération code 6 chiffres
2. Envoi par SMS ou email
3. Vérification du code après mot de passe

### OAuth (Google, Facebook)

1. Intégration bibliothèque OAuth
2. Création compte ou liaison compte existant
3. Stockage token d'accès

---

## Résumé

Le système d'authentification TomTroc offre :

✅ **Sécurité** : Hachage bcrypt, CSRF, sessions sécurisées
✅ **Simplicité** : API claire et facile à utiliser
✅ **Fiabilité** : Validation complète des données
✅ **Flexibilité** : Extensible facilement

**Prochaine étape** : Consulter **04-PROFILS.md** pour la gestion des profils utilisateurs.
