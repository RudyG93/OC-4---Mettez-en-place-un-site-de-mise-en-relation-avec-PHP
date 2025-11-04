# 📁 Organisation des fichiers CSS

## Structure

```
public/css/
├── style.css          ← Fichier principal (importe tous les modules)
├── global.css         ← Styles globaux (layout, navigation, variables)
├── components.css     ← Composants réutilisables (boutons, cartes, formulaires)
├── auth.css           ← Pages login et register
├── home.css           ← Page d'accueil
├── profile.css        ← Profil public d'un utilisateur
├── account.css        ← Page "Mon compte" (profil privé)
├── books.css          ← Liste des livres disponibles
├── bookdetail.css     ← Page de détail d'un livre
├── bookadd.css        ← Formulaire d'ajout de livre
├── bookedit.css       ← Formulaire d'édition de livre
└── messagerie.css     ← Système de messagerie
```

---

## 📄 Fichiers

### `style.css` - Fichier principal
**Rôle** : Point d'entrée unique qui importe tous les modules CSS

```css
@import url('global.css');
@import url('components.css');
@import url('auth.css');
@import url('home.css');
@import url('profile.css');
@import url('account.css');
@import url('books.css');
@import url('bookdetail.css');
@import url('bookadd.css');
@import url('bookedit.css');
@import url('messagerie.css');
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
- Flash messages
- Footer
- Utilities (mt-*, mb-*, p-*, text-center)

**Classes principales** :
```css
.container, .main-content
.header, .nav, .nav-link
.flash-message, .flash-success, .flash-error
.footer
```

---

### `components.css` - Composants réutilisables
**Contenu** :
- Boutons (.btn-primary, .btn-secondary, .btn-danger, etc.)
- Cartes (.card, .book-card)
- Formulaires (.form-group, .form-label, .form-input)
- Badges et tags
- Modales et overlays

**Classes principales** :
```css
.btn, .btn-primary, .btn-secondary, .btn-danger, .btn-outline
.card, .card-header, .card-body, .card-footer
.form-group, .form-label, .form-input, .form-textarea
.badge, .badge-success, .badge-warning
```

---

### `auth.css` - Authentification
**Contenu** :
- Pages login & register
- Formulaires d'authentification
- Layout spécifique aux pages d'authentification

**Pages concernées** :
- `/login`
- `/register`

---

### `home.css` - Page d'accueil
**Contenu** :
- Hero section
- Features sections
- Call to actions
- Présentation du site

**Page concernée** :
- `/` (page d'accueil)

---

### `profile.css` - Profil public
**Contenu** :
- Affichage du profil d'un utilisateur
- Avatar, bio, statistiques
- Liste des livres de l'utilisateur

**Page concernée** :
- `/profil/{id}` (profil public)

---

### `account.css` - Mon compte
**Contenu** :
- Page de gestion du profil personnel
- Formulaire d'édition du profil
- Upload d'avatar
- Ma bibliothèque personnelle

**Pages concernées** :
- `/mon-compte` (mon profil privé)
- `/mon-compte/modifier` (modifier mon profil)

---

### `books.css` - Liste des livres
**Contenu** :
- Grille de livres disponibles
- Recherche de livres
- Filtres et tri

**Page concernée** :
- `/nos-livres` (catalogue public)

---

### `bookdetail.css` - Détail d'un livre
**Contenu** :
- Page de détail complète d'un livre
- Informations propriétaire
- Actions contextuelles

**Page concernée** :
- `/livre/{id}` (détail d'un livre)

---

### `bookadd.css` - Ajout de livre
**Contenu** :
- Formulaire d'ajout de livre
- Upload d'image avec prévisualisation

**Page concernée** :
- `/book/create` (ajouter un livre)

---

### `bookedit.css` - Édition de livre
**Contenu** :
- Formulaire d'édition moderne
- Design 2 colonnes (photo + infos)
- Upload d'image avec prévisualisation

**Page concernée** :
- `/book/{id}/edit` (modifier un livre)

---

### `messagerie.css` - Messagerie
**Contenu** :
- Liste des conversations
- Fil de discussion
- Formulaire d'envoi de messages
- Compteurs de messages non lus

**Pages concernées** :
- `/messages` (liste des conversations)
- `/messages/conversation/{id}` (conversation)
- `/messages/compose/{id}` (nouveau message)

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

Pour ajouter un nouveau module CSS :

1. **Créer le fichier** : `public/css/nouveau-module.css`

2. **Ajouter le contenu** :
```css
/**
 * TomTroc - Nouveau Module
 * Description du module
 */

.mon-element {
    /* ... */
}
```

3. **Importer dans `style.css`** :
```css
@import url('global.css');
/* ... autres imports ... */
@import url('nouveau-module.css');    /* ← Nouveau */
```

---

## ✅ Avantages de cette organisation

1. **Modularité** : Chaque fichier correspond à une fonctionnalité
2. **Maintenabilité** : Plus facile de trouver et modifier les styles
3. **Performance** : Un seul fichier à charger (style.css) grâce aux @import
4. **Clarté** : Organisation logique par feature
5. **Évolutivité** : Facile d'ajouter de nouveaux modules
6. **Réutilisabilité** : Variables CSS et composants partagés

---

**Date de mise à jour** : Novembre 2025
**Version** : 2.0 - TomTroc Production Ready
