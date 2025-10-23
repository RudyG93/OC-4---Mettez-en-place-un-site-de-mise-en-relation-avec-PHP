# 📁 Organisation des fichiers CSS

## Structure

```
public/css/
├── style.css       ← Fichier principal (importe tous les modules)
├── global.css      ← Styles globaux (layout, navigation, boutons, etc.)
├── auth.css        ← Styles pour login et register
└── profile.css     ← Styles pour les profils
```

---

## 📄 Fichiers

### `style.css` - Fichier principal
**Rôle** : Point d'entrée unique qui importe tous les modules CSS

```css
@import url('global.css');
@import url('auth.css');
@import url('profile.css');
```

**Utilisation** : C'est le seul fichier à inclure dans vos pages HTML
```html
<link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
```

---

### `global.css` - Styles globaux
**Contenu** :
- Variables CSS (couleurs, polices, espacements)
- Reset & Base styles
- Layout (container, main-content)
- Header & Navigation
- Boutons (.btn-primary, .btn-secondary, .btn-block)
- Flash messages
- Hero section
- Features & Info sections
- Error pages
- Footer
- Utilities (mt-*, mb-*, p-*, text-center)
- Responsive global

**Classes principales** :
```css
.container
.main-content
.header, .nav, .nav-link
.btn, .btn-primary, .btn-secondary
.flash-message, .flash-success, .flash-error
.hero, .hero-title
.footer
```

---

### `auth.css` - Authentification
**Contenu** :
- Pages login & register
- Formulaires d'authentification
- Images de fond pour les pages auth
- Layout spécifique aux pages d'authentification

**Classes principales** :
```css
.auth-page
.login-container
.auth-container
.auth-card
.auth-title
.auth-form
.form-group, .form-label, .form-input
.form-help, .error-message
.auth-footer, .auth-link
.login-pic
```

**Pages concernées** :
- `/login`
- `/register`

---

### `profile.css` - Profils
**Contenu** :
- Pages de profil (view, edit, show)
- Avatar
- Informations utilisateur
- Statistiques
- Actions profil
- Formulaire d'édition

**Classes principales** :
```css
.profile-page
.profile-container
.profile-header
.profile-avatar, .avatar-placeholder
.profile-title, .profile-subtitle
.profile-info, .profile-info-item
.info-label, .info-value
.profile-stats, .stat-item, .stat-value, .stat-label
.profile-actions
.profile-edit-container
.profile-form
.profile-form-actions
```

**Pages concernées** :
- `/mon-compte` (mon profil)
- `/mon-compte/modifier` (modifier mon profil)
- `/profil/{id}` (profil public)

---

## 🎨 Variables CSS

Définies dans `global.css` et disponibles partout :

```css
--primary-color: black;
--secondary-color: #A6A6A6;
--third-color: #00AC66;
--accent-color: #FF6B6B;
--text-color: #333333;
--text-light: #666666;
--bg-color: #F5F3EF;
--bg-light: #fffbf5;
--border-color: #E0E0E0;
--shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
--shadow-hover: 0 4px 12px rgba(0, 0, 0, 0.15);
--border-radius: 8px;
--transition: all 0.3s ease;
--primary-font: 'Inter', sans-serif;
--secondary-font: 'Playfair Display', serif;
```

**Utilisation** :
```css
.my-element {
    color: var(--primary-color);
    background: var(--bg-color);
    border-radius: var(--border-radius);
}
```

---

## 🔧 Ajout de nouveaux modules

Pour ajouter un nouveau module CSS (ex: `books.css` pour la gestion des livres) :

1. **Créer le fichier** : `public/css/books.css`

2. **Ajouter le contenu** :
```css
/**
 * TomTroc - Styles Livres
 * Book list, book detail, book management pages
 */

.book-grid {
    /* ... */
}

.book-card {
    /* ... */
}
```

3. **Importer dans `style.css`** :
```css
@import url('global.css');
@import url('auth.css');
@import url('profile.css');
@import url('books.css');    /* ← Nouveau */
```

---

## ✅ Avantages de cette organisation

1. **Modularité** : Chaque fichier correspond à une fonctionnalité
2. **Maintenabilité** : Plus facile de trouver et modifier les styles
3. **Performance** : Un seul fichier à charger (style.css) grâce aux @import
4. **Clarté** : Organisation logique par feature
5. **Évolutivité** : Facile d'ajouter de nouveaux modules
6. **Réutilisabilité** : Variables CSS partagées entre tous les fichiers

---

## 📝 Conventions de nommage

### Classes BEM-like
```css
.block               /* Élément parent */
.block-element       /* Élément enfant */
.block-element-item  /* Sous-élément */
```

**Exemples** :
```css
.profile-container
.profile-header
.profile-info-item
```

### Modificateurs
```css
.btn
.btn-primary
.btn-secondary
.btn-block
```

### États
```css
.nav-link
.nav-link.active
.form-input
.form-input.input-error
```

---

## 🧪 Tests

Après modification du CSS, tester sur :

1. **Pages d'authentification**
   - `/login`
   - `/register`

2. **Pages de profil**
   - `/mon-compte`
   - `/mon-compte/modifier`
   - `/profil/2`

3. **Pages globales**
   - Page d'accueil `/`
   - Pages d'erreur `/404`

4. **Responsive**
   - Desktop (1920px)
   - Tablette (768px)
   - Mobile (375px)

---

## 🎯 Prochaines étapes

Modules CSS à créer :

1. **books.css** - Gestion des livres
   - Liste des livres
   - Détail d'un livre
   - Formulaire d'ajout/édition
   - Grille de livres

2. **messages.css** - Messagerie
   - Liste des conversations
   - Fenêtre de conversation
   - Formulaire d'envoi

3. **home.css** - Page d'accueil
   - Hero spécifique
   - Sections de présentation
   - Call to actions

---

**Date de création** : 18 octobre 2025
**Auteur** : Équipe TomTroc
**Version** : 1.0
