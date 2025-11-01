# Changelog - TomTroc

## [1.0.0] - 2025-11-01

### ✨ Nouvelles fonctionnalités
- Système d'authentification complet (inscription, connexion, déconnexion)
- Gestion des livres (ajout, édition, suppression, disponibilité)
- Upload d'images pour les livres et avatars
- Système de messagerie privée entre utilisateurs
- Profils utilisateurs publics
- Page "Mon compte" pour gérer son profil
- Badge de notifications pour les nouveaux messages
- Système de placeholder pour images manquantes
- Page d'accueil avec derniers livres ajoutés
- Liste publique des livres disponibles à l'échange
- Page de détail pour chaque livre

### 🔒 Sécurité
- Protection XSS avec échappement systématique (`e()`)
- Protection contre les injections SQL (prepared statements)
- Tokens CSRF sur tous les formulaires
- Validation des uploads de fichiers
- Protection des images placeholder
- Sessions sécurisées

### 🎨 Design & UX
- Interface responsive (mobile, tablette, desktop)
- Design moderne et épuré
- Navigation intuitive avec état actif
- Feedback visuel sur les actions utilisateur
- Messages flash pour confirmer les actions
- Interface de messagerie à deux colonnes

### 🏗️ Architecture
- Architecture MVC personnalisée
- Système de routing flexible
- Pattern Entity/Manager pour la BDD
- Séparation des concerns
- Code modulaire et réutilisable

### 📝 Documentation
- README complet avec guide d'installation
- Guide du développeur (DEVELOPER_GUIDE.md)
- Commentaires PHPDoc dans le code
- Documentation des routes

### 🧹 Optimisations (Nettoyage final)

#### CSS
- ✅ Suppression des styles dupliqués entre `global.css` et `components.css`
- ✅ Suppression des styles inutilisés (hero, features, info-boxes)
- ✅ Organisation claire avec imports dans `style.css`
- ✅ Variables CSS centralisées dans `:root`

#### Code
- ✅ Aucun code de debug (var_dump, print_r, console.log)
- ✅ Échappement HTML systématique avec `e()`
- ✅ Utilisation cohérente des prepared statements
- ✅ Conventions de nommage respectées
- ✅ Commentaires et documentation ajoutés

#### Fichiers
- ✅ Suppression de `compose.php` (fonctionnalité fusionnée)
- ✅ Structure de dossiers cohérente
- ✅ Séparation claire CSS par fonctionnalité

### 📊 Statistiques du projet

**Fichiers PHP** : ~30 fichiers
- Controllers: 6
- Models (Entities): 3
- Models (Managers): 3
- Views: ~15 fichiers de templates
- Core: 6 classes système

**Fichiers CSS** : 11 fichiers modulaires
- global.css (base, layout, navigation)
- components.css (composants réutilisables)
- 9 fichiers spécifiques par fonctionnalité

**Base de données** : 3 tables principales
- users (utilisateurs)
- books (livres)
- messages (messagerie)

### 🔄 Migrations BDD
- `sql/database.sql` - Structure initiale
- `sql/migration_remove_login.sql` - Suppression colonne login (migration effectuée)

### 📦 Dépendances
- PHP 8.0+
- MySQL 5.7+
- Extension PDO
- Apache/Nginx

### ⚙️ Configuration
- `config.php` - Configuration de base
- `config.local.php` - Configuration locale (non versionné)
- `routes.php` - Définition des routes

### 🎯 Points forts
1. Code propre et maintenable
2. Sécurité renforcée
3. Architecture scalable
4. UX/UI moderne
5. Documentation complète
6. Performances optimisées

### 🚀 Prêt pour la production
- ✅ Code testé et fonctionnel
- ✅ Sécurité vérifiée
- ✅ Documentation complète
- ✅ CSS optimisé
- ✅ Structure claire
- ✅ Bonnes pratiques respectées

---

**Note** : Ce projet a été développé avec soin dans un cadre pédagogique, en respectant les meilleures pratiques de développement web.
