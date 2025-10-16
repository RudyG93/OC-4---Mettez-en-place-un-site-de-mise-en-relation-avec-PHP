# 📝 Guide d'implémentation du système d'authentification

## ✅ Ce qui a été créé

Vous disposez maintenant d'un système d'authentification complet avec :
- ✅ Formulaire d'inscription
- ✅ Formulaire de connexion
- ✅ Déconnexion
- ✅ Validation côté serveur
- ✅ Sécurité (CSRF, hachage de mot de passe)

---

## 📁 Fichiers créés

### 1. **Entité User** (`app/entities/User.php`)

**Rôle** : Représente un utilisateur dans l'application.

**Contenu principal** :
- Propriétés : `id`, `username`, `email`, `password`, `avatar`, `created_at`, `updated_at`
- Getters et setters pour chaque propriété
- Méthode `verifyPassword()` : Vérifie si un mot de passe correspond au hash
- Méthode statique `hashPassword()` : Hash un mot de passe avec `password_hash()`

**Pourquoi c'est important** :
- Encapsule les données d'un utilisateur
- Fournit des méthodes pour manipuler les mots de passe de manière sécurisée
- Hérite de `Entity` pour bénéficier de l'hydratation automatique

---

### 2. **Manager UserManager** (`app/models/UserManager.php`)

**Rôle** : Gère toutes les opérations de base de données liées aux utilisateurs.

**Méthodes principales** :
- `findById($id)` : Récupère un utilisateur par son ID
- `findByEmail($email)` : Récupère un utilisateur par son email
- `findByUsername($username)` : Récupère un utilisateur par son pseudo
- `findAll()` : Récupère tous les utilisateurs
- `createUser(User $user)` : Crée un nouvel utilisateur en BDD
- `updateUser(User $user)` : Met à jour un utilisateur existant
- `delete($id)` : Supprime un utilisateur
- `emailExists($email)` : Vérifie si un email est déjà utilisé
- `usernameExists($username)` : Vérifie si un pseudo est déjà utilisé

**Pourquoi c'est important** :
- Centralise toute la logique d'accès aux données utilisateur
- Utilise des requêtes préparées (sécurité contre les injections SQL)
- Retourne des objets `User` hydratés

---

### 3. **Controller AuthController** (`app/controllers/AuthController.php`)

**Rôle** : Gère la logique métier de l'authentification.

**Méthodes principales** :

#### `register()`
- Affiche le formulaire d'inscription (GET)
- Traite la soumission du formulaire (POST)
- Génère un token CSRF pour la sécurité

#### `registerPost()` (privée)
- Valide toutes les données du formulaire
- Vérifie que l'email et le pseudo ne sont pas déjà utilisés
- Hash le mot de passe
- Crée l'utilisateur en base de données
- Redirige vers la page de connexion avec un message de succès

#### `login()`
- Affiche le formulaire de connexion (GET)
- Traite la soumission du formulaire (POST)

#### `loginPost()` (privée)
- Vérifie les identifiants
- Utilise `verifyPassword()` pour comparer le mot de passe
- Crée une session utilisateur
- Redirige vers l'accueil

#### `logout()`
- Détruit la session
- Redirige vers l'accueil avec un message

**Pourquoi c'est important** :
- Sépare la logique GET et POST dans les mêmes méthodes
- Valide toutes les entrées utilisateur
- Protège contre les attaques CSRF
- Gère les messages flash pour informer l'utilisateur

---

### 4. **Vue d'inscription** (`app/views/auth/register.php`)

**Contenu** :
- Formulaire HTML avec 4 champs :
  - Pseudo (avec validation pattern)
  - Email
  - Mot de passe
  - Confirmation du mot de passe
- Token CSRF caché
- Affichage des erreurs de validation
- Conservation des valeurs saisies en cas d'erreur (`oldInput`)
- Lien vers la page de connexion

**Classes CSS utilisées** :
- `.auth-container` : Conteneur principal centré
- `.auth-card` : Carte avec ombre
- `.form-group` : Groupe de champ
- `.form-input` : Champ de saisie
- `.input-error` : Champ avec erreur (bordure rouge)
- `.error-message` : Message d'erreur en rouge
- `.btn-primary` : Bouton d'action principal

---

### 5. **Vue de connexion** (`app/views/auth/login.php`)

**Contenu** :
- Formulaire HTML simplifié avec 2 champs :
  - Email
  - Mot de passe
- Token CSRF caché
- Lien vers la page d'inscription

**Pourquoi plus simple** :
- Pas besoin de validation complexe côté affichage
- Les erreurs sont affichées via les messages flash

---

### 6. **Styles CSS** (`public/css/style.css`)

**Sections ajoutées** :

#### Conteneurs d'authentification
```css
.auth-container  → Centrage vertical et horizontal
.auth-card       → Carte avec ombre et bordure arrondie
.auth-title      → Titre principal
.auth-subtitle   → Sous-titre
.auth-form       → Espacement du formulaire
```

#### Éléments de formulaire
```css
.form-group      → Espacement entre les champs
.form-label      → Label des champs
.form-input      → Champ de saisie stylisé
.form-input:focus → Bordure verte au focus
.input-error     → Bordure rouge en cas d'erreur
.error-message   → Message d'erreur rouge
.form-help       → Texte d'aide gris
```

#### Boutons et footer
```css
.btn-block       → Bouton pleine largeur
.auth-footer     → Pied de carte avec bordure
.auth-link       → Lien stylisé
```

#### Responsive
- Adaptation pour mobile (padding réduit, taille de titre ajustée)

---

## 🔒 Sécurité implémentée

### 1. **Protection CSRF**
```php
// Génération du token
$csrfToken = Session::generateCsrfToken();

// Vérification du token
Session::verifyCsrfToken($_POST['csrf_token'])
```

### 2. **Hachage des mots de passe**
```php
// Lors de l'inscription
User::hashPassword($password)  // Utilise password_hash()

// Lors de la connexion
$user->verifyPassword($password)  // Utilise password_verify()
```

### 3. **Requêtes préparées**
```php
$stmt = $this->db->prepare($sql);
$stmt->bindValue(':email', $email, PDO::PARAM_STR);
```

### 4. **Validation des entrées**
- Longueur minimum/maximum
- Format email avec `filter_var()`
- Pattern pour le pseudo (lettres, chiffres, tirets uniquement)
- Vérification de l'unicité (email, pseudo)
- Correspondance des mots de passe

### 5. **Échappement des sorties**
```php
htmlspecialchars($value)  // Dans toutes les vues
```

---

## 🧪 Comment tester

### 1. **Tester l'inscription**

Accédez à :
```
http://localhost/tests/Projet4/public/register
```

Essayez de :
- ✅ Créer un compte avec des données valides
- ❌ Créer un compte avec un email déjà utilisé
- ❌ Créer un compte avec un pseudo trop court
- ❌ Utiliser des mots de passe qui ne correspondent pas
- ❌ Laisser des champs vides

### 2. **Tester la connexion**

Accédez à :
```
http://localhost/tests/Projet4/public/login
```

Utilisez les comptes de test (mot de passe : `password123`) :
- alice@example.com
- bob@example.com
- charlie@example.com

Ou créez votre propre compte via l'inscription.

### 3. **Tester la déconnexion**

Une fois connecté, cliquez sur "Déconnexion" dans la navbar.

---

## 🔄 Flux d'authentification

### Inscription
```
1. Utilisateur → GET /register
2. Affichage du formulaire avec token CSRF
3. Utilisateur remplit et soumet → POST /register
4. Validation des données
5. Vérification de l'unicité (email, pseudo)
6. Hachage du mot de passe
7. Insertion en BDD
8. Message flash "Inscription réussie"
9. Redirection → /login
```

### Connexion
```
1. Utilisateur → GET /login
2. Affichage du formulaire avec token CSRF
3. Utilisateur soumet → POST /login
4. Recherche de l'utilisateur par email
5. Vérification du mot de passe
6. Création de la session (user_id, username)
7. Message flash "Bienvenue [username]"
8. Redirection → /
```

### Déconnexion
```
1. Utilisateur clique sur "Déconnexion"
2. Destruction de la session
3. Message flash "Vous avez été déconnecté"
4. Redirection → /
```

---

## 🎨 Personnalisation

### Modifier les couleurs
Dans `public/css/style.css`, ajustez les variables CSS :
```css
:root {
    --primary-color: #00AC66;     /* Couleur principale */
    --accent-color: #FF6B6B;      /* Couleur d'erreur */
    --border-color: #E0E0E0;      /* Bordures */
}
```

### Ajouter des champs
1. Ajoutez le champ dans la vue (`app/views/auth/register.php`)
2. Ajoutez la validation dans `AuthController::registerPost()`
3. Ajoutez la propriété dans `User.php`
4. Modifiez la méthode `createUser()` dans `UserManager.php`

### Modifier les règles de validation
Dans `AuthController::registerPost()` :
```php
// Exemple : Mot de passe minimum 8 caractères
if (strlen($password) < 8) {
    $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
}
```

---

## 📊 Base de données

La table `users` contient :
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## ⚠️ Problèmes courants

### Erreur "Token CSRF invalide"
- Vérifiez que la session est bien démarrée
- Vérifiez que le champ `csrf_token` est bien dans le formulaire

### Erreur "Email déjà utilisé"
- Normal si vous essayez de créer un compte avec un email existant
- Vérifiez dans la BDD avec : `SELECT * FROM users WHERE email = 'xxx'`

### Page blanche
- Activez l'affichage des erreurs dans `config/config.php`
- Vérifiez les logs d'erreurs PHP

### Formulaire ne s'affiche pas
- Vérifiez que `AuthController.php` est bien chargé
- Vérifiez les routes dans `config/routes.php`

---

## 🚀 Prochaines étapes

Maintenant que l'authentification est fonctionnelle, vous pouvez :

1. **Ajouter la gestion du profil utilisateur**
   - Afficher le profil
   - Modifier le profil
   - Upload d'avatar

2. **Protéger les routes**
   - Créer un middleware d'authentification
   - Vérifier `Session::isLoggedIn()` dans les contrôleurs

3. **Ajouter la récupération de mot de passe**
   - Formulaire "Mot de passe oublié"
   - Envoi d'email avec token
   - Réinitialisation du mot de passe

4. **Implémenter les fonctionnalités métier**
   - CRUD des livres
   - Messagerie
   - Etc.

---

## 📝 Checklist de validation

- [x] Formulaire d'inscription fonctionnel
- [x] Validation côté serveur
- [x] Hachage des mots de passe
- [x] Protection CSRF
- [x] Formulaire de connexion fonctionnel
- [x] Vérification des identifiants
- [x] Gestion de session
- [x] Déconnexion fonctionnelle
- [x] Messages flash
- [x] Design responsive
- [x] Gestion des erreurs

---

**Félicitations ! Le système d'authentification est maintenant opérationnel !** 🎉

Vous pouvez tester en vous rendant sur :
- **Inscription** : `http://localhost/tests/Projet4/public/register`
- **Connexion** : `http://localhost/tests/Projet4/public/login`
