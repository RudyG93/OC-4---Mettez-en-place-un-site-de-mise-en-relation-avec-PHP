# 📋 GUIDE - GESTION DES PROFILS

## 🎯 Objectif

Permettre aux utilisateurs de :
1. **Voir leur propre profil** avec toutes leurs informations
2. **Modifier leur profil** (pseudo, email, mot de passe)
3. **Consulter les profils publics** des autres utilisateurs

---

## 📐 Architecture

### Structure MVC

```
app/
├── controllers/
│   └── ProfileController.php       # Gestion des profils
├── models/
│   ├── entities/
│   │   └── User.php               # Déjà existant
│   └── managers/
│       └── UserManager.php        # Méthodes ajoutées
└── views/
    └── profile/
        ├── view.php               # Mon profil (privé)
        ├── edit.php               # Modifier mon profil
        └── show.php               # Profil public (autres users)
```

### Routes

| Route | Méthode | Action | Description |
|-------|---------|--------|-------------|
| `/profile` | GET | `view()` | Affiche mon profil |
| `/profile/edit` | GET | `edit()` | Formulaire de modification |
| `/profile/update` | POST | `update()` | Traite la modification |
| `/profile/{id}` | GET | `show($id)` | Affiche un profil public |

---

## 🔧 Plan d'implémentation

### Étape 1 : ProfileController

**Fichier** : `app/controllers/ProfileController.php`

**Méthodes à créer** :

#### 1. `view()` - Voir mon profil
- Vérifie que l'utilisateur est connecté
- Récupère les informations de l'utilisateur depuis la session
- Affiche la vue `profile/view.php`

#### 2. `edit()` - Formulaire de modification
- Vérifie que l'utilisateur est connecté
- Récupère les informations actuelles
- Génère un token CSRF
- Affiche la vue `profile/edit.php`

#### 3. `update()` - Traiter la modification
- Vérifie que l'utilisateur est connecté
- Valide le token CSRF
- Valide les données du formulaire :
  - Pseudo (3-50 caractères)
  - Email (format valide)
  - Mot de passe (optionnel, min 6 caractères)
- Vérifie que l'email n'est pas déjà utilisé par un autre compte
- Met à jour la base de données
- Redirige vers le profil avec un message de succès

#### 4. `show($id)` - Voir un profil public
- Récupère l'utilisateur par son ID
- Vérifie qu'il existe
- Affiche uniquement les informations publiques (pseudo, date d'inscription)
- Affiche la vue `profile/show.php`

---

### Étape 2 : UserManager - Nouvelles méthodes

**Fichier** : `app/models/managers/UserManager.php`

#### Méthodes à ajouter :

```php
/**
 * Met à jour les informations d'un utilisateur
 * @param int $userId ID de l'utilisateur
 * @param array $data Données à mettre à jour
 * @return bool Succès de l'opération
 */
public function updateUser(int $userId, array $data): bool

/**
 * Récupère un utilisateur par son ID
 * @param int $userId ID de l'utilisateur
 * @return User|null L'utilisateur ou null
 */
public function getUserById(int $userId): ?User
```

---

### Étape 3 : Vues

#### 1. `profile/view.php` - Mon profil (privé)

Affiche :
- Photo de profil (si disponible)
- Pseudo
- Email
- Date d'inscription
- Date de dernière connexion
- Bouton "Modifier mon profil"

#### 2. `profile/edit.php` - Modifier mon profil

Formulaire avec :
- Pseudo (pré-rempli)
- Email (pré-rempli)
- Nouveau mot de passe (optionnel)
- Confirmer le mot de passe (optionnel)
- Bouton "Enregistrer les modifications"
- Lien "Annuler" vers le profil

Validation côté client :
- Pseudo : 3-50 caractères
- Email : format valide
- Mot de passe : min 6 caractères (si renseigné)

#### 3. `profile/show.php` - Profil public

Affiche :
- Photo de profil (si disponible)
- Pseudo
- Membre depuis [date]
- Nombre de livres partagés (future fonctionnalité)

---

### Étape 4 : Routes

**Fichier** : `app/core/Router.php`

Ajouter dans la méthode `route()` :

```php
// Profils
elseif ($path === 'profile' && $method === 'GET') {
    $controller = new ProfileController();
    $controller->view();
}
elseif ($path === 'profile/edit' && $method === 'GET') {
    $controller = new ProfileController();
    $controller->edit();
}
elseif ($path === 'profile/update' && $method === 'POST') {
    $controller = new ProfileController();
    $controller->update();
}
elseif (preg_match('#^profile/(\d+)$#', $path, $matches) && $method === 'GET') {
    $controller = new ProfileController();
    $controller->show((int)$matches[1]);
}
```

---

### Étape 5 : Styles CSS

**Fichier** : `public/css/style.css`

Ajouter des classes pour :

```css
/* === PROFILE === */
.profile-container { }
.profile-header { }
.profile-avatar { }
.profile-info { }
.profile-info-item { }
.profile-stats { }
.profile-edit-btn { }

/* === PROFILE FORM === */
.profile-form { }
.profile-form-actions { }
```

---

### Étape 6 : Navigation

**Fichier** : `app/views/layout/header.php`

Ajouter un lien "Mon profil" dans le menu pour les utilisateurs connectés :

```php
<?php if (Session::isLoggedIn()): ?>
    <li><a href="<?php echo BASE_URL; ?>profile" class="nav-link">Mon profil</a></li>
<?php endif; ?>
```

---

## 🔒 Sécurité

### Validation des données

1. **Pseudo** :
   - Entre 3 et 50 caractères
   - Alphanumériques, tirets et underscores uniquement
   - Regex : `/^[a-zA-Z0-9_-]{3,50}$/`

2. **Email** :
   - Format valide
   - Utiliser `filter_var($email, FILTER_VALIDATE_EMAIL)`
   - Vérifier l'unicité (sauf si inchangé)

3. **Mot de passe** :
   - Minimum 6 caractères
   - Hashé avec `password_hash()`
   - Optionnel (ne modifier que s'il est renseigné)

### Protection CSRF

- Utiliser `Session::generateCSRFToken()` dans le formulaire
- Valider avec `Session::validateCSRFToken()` lors de la soumission

### Contrôle d'accès

- Vérifier `Session::isLoggedIn()` pour toutes les actions
- Un utilisateur ne peut modifier que son propre profil
- Les profils publics sont accessibles à tous

---

## 🎨 Design

### Page "Mon profil"

```
+----------------------------------+
|         [Avatar]                 |
|                                  |
|  Pseudo: Alice                   |
|  Email: alice@example.com        |
|  Membre depuis: 10 oct. 2024    |
|                                  |
|  [Modifier mon profil] [button]  |
+----------------------------------+
```

### Page "Modifier mon profil"

```
+----------------------------------+
|  Modifier mon profil             |
|                                  |
|  Pseudo: [Alice          ]       |
|  Email:  [alice@ex...    ]       |
|                                  |
|  Nouveau mot de passe (opt.):    |
|  [••••••••••••••]                |
|                                  |
|  Confirmer le mot de passe:      |
|  [••••••••••••••]                |
|                                  |
|  [Enregistrer] [Annuler]         |
+----------------------------------+
```

### Page "Profil public"

```
+----------------------------------+
|         [Avatar]                 |
|                                  |
|  Pseudo: Bob                     |
|  Membre depuis: 15 sept. 2024   |
|                                  |
|  📚 12 livres partagés           |
|                                  |
|  [Voir ses livres] [button]      |
+----------------------------------+
```

---

## ✅ Checklist d'implémentation

### Backend
- [ ] Créer `ProfileController.php`
- [ ] Implémenter `view()` - Mon profil
- [ ] Implémenter `edit()` - Formulaire
- [ ] Implémenter `update()` - Traitement
- [ ] Implémenter `show($id)` - Profil public
- [ ] Ajouter `updateUser()` dans `UserManager`
- [ ] Ajouter `getUserById()` dans `UserManager`
- [ ] Ajouter les routes dans `Router.php`

### Frontend
- [ ] Créer `views/profile/view.php`
- [ ] Créer `views/profile/edit.php`
- [ ] Créer `views/profile/show.php`
- [ ] Ajouter les styles CSS
- [ ] Ajouter le lien dans la navigation

### Tests
- [ ] Tester l'affichage de mon profil
- [ ] Tester la modification du pseudo
- [ ] Tester la modification de l'email
- [ ] Tester la modification du mot de passe
- [ ] Tester les validations d'erreur
- [ ] Tester l'affichage d'un profil public
- [ ] Tester l'accès à un profil inexistant (404)

---

## 🧪 Scénarios de test

### Test 1 : Voir mon profil
1. Se connecter avec `alice@example.com`
2. Aller sur `/profile`
3. ✅ On voit les infos d'Alice

### Test 2 : Modifier mon pseudo
1. Sur `/profile`, cliquer "Modifier"
2. Changer le pseudo en "Alice2024"
3. Cliquer "Enregistrer"
4. ✅ Redirection vers `/profile` avec succès
5. ✅ Le nouveau pseudo s'affiche

### Test 3 : Modifier l'email (déjà utilisé)
1. Sur `/profile/edit`
2. Changer l'email en `bob@example.com` (existe déjà)
3. Cliquer "Enregistrer"
4. ❌ Message d'erreur : "Cet email est déjà utilisé"

### Test 4 : Voir un profil public
1. Aller sur `/profile/2`
2. ✅ On voit le profil public de l'utilisateur #2
3. ✅ On ne voit PAS son email (privé)

### Test 5 : Profil inexistant
1. Aller sur `/profile/999`
2. ✅ Page 404 ou message "Utilisateur introuvable"

---

## 🚀 Prochaines évolutions possibles

1. **Upload de photo de profil**
   - Ajouter une colonne `avatar` dans la table `users`
   - Gérer l'upload et le redimensionnement d'image

2. **Statistiques utilisateur**
   - Nombre de livres partagés
   - Nombre de messages envoyés
   - Date de dernière activité

3. **Paramètres de confidentialité**
   - Masquer son profil des autres utilisateurs
   - Désactiver les messages privés

4. **Suppression de compte**
   - Bouton "Supprimer mon compte"
   - Confirmation par email
   - Anonymisation des données

---

## 📚 Ressources

- Documentation PHP : https://www.php.net/manual/fr/
- Guide MVC : `README.md`
- Guide authentification : `AUTHENTICATION_GUIDE.md`
- Base de données : `sql/database.sql`

---

**Statut** : 🚧 En cours d'implémentation
**Dépendances** : ✅ Authentification (déjà faite)
**Prochaine étape** : Bibliothèque personnelle
