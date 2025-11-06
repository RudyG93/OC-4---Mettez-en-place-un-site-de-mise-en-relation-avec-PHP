# 📚 TomTroc - Plateforme d'échange de livres

TomTroc est une plateforme web permettant aux utilisateurs d'échanger des livres entre eux. Développée en PHP natif avec une architecture MVC personnalisée.

## 🎯 Fonctionnalités principales

### Authentification
- ✅ Inscription et connexion sécurisées
- ✅ Gestion de session
- ✅ Protection CSRF sur tous les formulaires

### Gestion des livres
- ✅ Ajout de livres avec image
- ✅ Édition et suppression
- ✅ Disponibilité à l'échange
- ✅ Consultation publique des livres disponibles
- ✅ Page de détail pour chaque livre
- ✅ Système de placeholder pour les images

### Profils utilisateurs
- ✅ Page profil publique (consultation)
- ✅ Page "Mon compte" (gestion personnelle)
- ✅ Upload d'avatar personnalisé
- ✅ Affichage de la bibliothèque

### Messagerie
- ✅ Système de messagerie privée
- ✅ Conversations en temps réel
- ✅ Badge de notifications
- ✅ Interface à deux colonnes (conversations/messagerie)

## 🏗️ Architecture technique

### Structure MVC
```
app/
├── controller/     # Contrôleurs (logique métier)
├── model/          # Modèles (entités et managers)
│   ├── entity/     # Classes entités (Book, User, Message)
│   └── manager/    # Classes de gestion BDD
├── view/           # Vues (templates PHP)
│   └── layouts/    # Layout principal
├── service/        # Services réutilisables (ImageUploader, BookValidator)
├── traits/         # Traits PHP (ManagesBookOwnership)
└── core/           # Classes système (App, Controller, Model, etc.)
```

### Technologies utilisées
- **Backend**: PHP 8.0+
- **Base de données**: MySQL
- **Frontend**: HTML5, CSS3 (vanilla)
- **Architecture**: MVC custom
- **Sécurité**: Prepared statements, CSRF tokens, XSS protection

## 📦 Installation

### Prérequis
- PHP 8.0 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache/Nginx)
- Extension PHP PDO activée

### Étapes d'installation

1. **Cloner le projet**
```bash
git clone [url-du-repo]
cd Projet4
```

2. **Configuration de la base de données**
- Créer une base de données MySQL
- Importer le fichier `sql/database.sql`

3. **Configuration de l'application**
- Copier `config/config.example.php` vers `config/config.local.php`
- Modifier les paramètres de connexion à la base de données

```php
// config/config.local.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tomtroc');
define('DB_USER', 'votre_utilisateur');
define('DB_PASS', 'votre_mot_de_passe');
```

4. **Créer les dossiers d'upload**
```bash
mkdir -p public/uploads/books
mkdir -p public/uploads/avatars
chmod 755 public/uploads
```

5. **Ajouter les images placeholder**
- Placer `book_placeholder.png` dans `public/uploads/books/`
- Placer `pp_placeholder.png` dans `public/uploads/avatars/`

6. **Accéder à l'application**
- Ouvrir votre navigateur : `http://localhost/tests/Projet4/public/`

## 🎨 Structure CSS

L'application utilise une architecture CSS modulaire :

- `global.css` - Variables, reset, layout, navigation
- `components.css` - Composants réutilisables (boutons, formulaires, cartes)
- `auth.css` - Pages d'authentification (login, register)
- `home.css` - Page d'accueil
- `books.css` - Liste des livres disponibles
- `bookdetail.css` - Page de détail d'un livre
- `bookadd.css` - Formulaire d'ajout de livre
- `bookedit.css` - Formulaire d'édition de livre
- `account.css` - Page "Mon compte"
- `profile.css` - Profil public d'un utilisateur
- `messagerie.css` - Système de messagerie

Toutes les feuilles de style sont importées via `style.css`.

## 🔐 Sécurité

### Mesures de sécurité implémentées
- ✅ **Protection XSS**: Utilisation systématique de `htmlspecialchars()` via la fonction `e()`
- ✅ **Protection SQL Injection**: Prepared statements PDO
- ✅ **Protection CSRF**: Tokens CSRF sur tous les formulaires
- ✅ **Upload sécurisé**: Validation type MIME et extension des fichiers
- ✅ **Sessions sécurisées**: Régénération d'ID de session
- ✅ **Protection des placeholders**: Empêche la suppression des images par défaut

### Bonnes pratiques
- Validation côté serveur de toutes les entrées
- Échappement des sorties HTML
- Séparation des concerns (MVC)
- Gestion d'erreurs appropriée

## 📝 Routes principales

### Pages publiques
- `/` - Page d'accueil
- `/nos-livres` - Liste des livres disponibles
- `/livre/{id}` - Détail d'un livre
- `/profil/{id}` - Profil public d'un utilisateur
- `/login` - Connexion
- `/register` - Inscription

### Pages privées (authentification requise)
- `/mon-compte` - Page Mon compte (gestion profil et livres)
- `/book/create` - Ajouter un livre
- `/book/{id}/edit` - Éditer un livre
- `/book/{id}/delete` - Supprimer un livre
- `/book/{id}/toggle-availability` - Changer la disponibilité
- `/messagerie` - Liste des conversations
- `/messagerie/conversation/{id}` - Conversation avec un utilisateur

## 🧪 Données de test

La base de données contient des données de test :
- Utilisateurs exemples
- Livres disponibles
- Conversations de démonstration

## 🧩 Patterns et Bonnes Pratiques

### Design Patterns implémentés
- **Singleton**: Connexion base de données unique (`Database.php`)
- **Repository**: Managers pour l'accès aux données
- **Front Controller**: Routeur centralisé (`App.php`)
- **Service Layer**: Services métier réutilisables (`ImageUploader`, `BookValidator`)
- **Trait**: Code partagé sans héritage (`ManagesBookOwnership`)

### Principes SOLID
- **Single Responsibility**: Chaque classe a une responsabilité unique
- **Separation of Concerns**: MVC strict sans mélange des couches
- **DRY** (Don't Repeat Yourself): Code factorisé dans des services et traits

## 🚀 Améliorations futures possibles

- [ ] Système de recherche avancée (filtres multiples, tri)
- [ ] Notifications par email
- [ ] Système de favoris/wishlist
- [ ] API REST pour application mobile
- [ ] Système de notation et avis
- [ ] Pagination sur la liste des livres
- [ ] Tests automatisés (PHPUnit)

## 📄 Licence

Ce projet est développé dans un cadre éducatif.

## 👤 Auteur

Développé dans le cadre du projet OpenClassrooms.