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
- ✅ Interface à deux colonnes (conversations/messages)

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
- `components.css` - Composants réutilisables
- `auth.css` - Authentification
- `books.css` - Liste des livres
- `book-detail.css` - Détail d'un livre
- `book-edit.css` - Édition de livre
- `account.css` - Page Mon compte
- `profile.css` - Profil public
- `messagerie.css` - Système de messagerie
- `home.css` - Page d'accueil

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
- `/mon-compte` - Page Mon compte
- `/book/{id}/edit` - Éditer un livre
- `/messages` - Messagerie
- `/messages/conversation/{userId}` - Conversation avec un utilisateur

## 🧪 Données de test

La base de données contient des données de test :
- Utilisateurs exemples
- Livres disponibles
- Conversations de démonstration

## 🚀 Améliorations futures possibles

- [ ] Système de recherche avancée
- [ ] Notifications en temps réel
- [ ] Système de favoris
- [ ] API REST
- [ ] Application mobile
- [ ] Système de notation des utilisateurs

## 📄 Licence

Ce projet est développé dans un cadre éducatif.

## 👤 Auteur

Développé dans le cadre du projet OpenClassrooms.

---

**Note**: Ce projet utilise du PHP natif sans framework pour des raisons pédagogiques. En production, l'utilisation d'un framework moderne (Symfony, Laravel) serait recommandée.
