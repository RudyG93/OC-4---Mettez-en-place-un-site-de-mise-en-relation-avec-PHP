# 🚀 GUIDE DE DÉMARRAGE RAPIDE - TomTroc

## ✅ Ce qui est DÉJÀ FAIT

1. ✅ Structure MVC complète créée
2. ✅ Toutes les classes core implémentées
3. ✅ Système de routage fonctionnel
4. ✅ Base de données SQL définie avec relations
5. ✅ Système d'authentification complet
6. ✅ Gestion des profils utilisateurs
7. ✅ Bibliothèque personnelle complète
8. ✅ Page publique des livres
9. ✅ Système de sessions et CSRF
10. ✅ Layout HTML/CSS responsive
11. ✅ Upload et gestion d'images
12. ✅ Pages détail des livres complètes
13. ✅ Formulaires d'édition avec design avancé
14. ✅ Système de messagerie complet
15. ✅ .gitignore configuré

## 📦 INSTALLATION EN 3 ÉTAPES

### 1️⃣ Créer la base de données (2 minutes)

**Option A - Via phpMyAdmin :**
1. Ouvrir http://localhost/phpmyadmin
2. Cliquer sur "Importer"
3. Sélectionner le fichier `sql/database.sql`
4. Cliquer sur "Exécuter"

**Option B - Via ligne de commande :**
```bash
mysql -u root -p < sql/database.sql
```

✅ Cela créera :
- La base `tomtroc`
- 3 tables (users, books, messages)
- Des données de test

### 2️⃣ Configurer les identifiants BDD (30 secondes)

Le fichier `config/config.local.php` existe déjà avec ces valeurs :

```php
DB_HOST = 'localhost'
DB_NAME = 'tomtroc'
DB_USER = 'root'
DB_PASS = ''  // <- Modifier si vous avez un mot de passe
```

Si vous utilisez XAMPP par défaut, **rien à changer** !

### 3️⃣ Tester l'application (10 secondes)

Ouvrir dans le navigateur :
```
http://localhost/tests/Projet4/public/
```

Vous devriez voir la **page d'accueil TomTroc** avec navigation complète ! 🎉

## 🧪 TESTER LES FONCTIONNALITÉS

### Routes disponibles actuellement :

✅ **Page d'accueil**
```
http://localhost/tests/Projet4/public/
```

✅ **Authentification**
```
http://localhost/tests/Projet4/public/login      # Connexion
http://localhost/tests/Projet4/public/register   # Inscription
http://localhost/tests/Projet4/public/logout     # Déconnexion
```

✅ **Profils utilisateurs**
```
http://localhost/tests/Projet4/public/mon-compte          # Mon profil
http://localhost/tests/Projet4/public/mon-compte/modifier # Modifier mon profil
http://localhost/tests/Projet4/public/profil/1            # Profil public
```

✅ **Bibliothèque personnelle** (nécessite connexion)
```
http://localhost/tests/Projet4/public/book/my-books       # Ma bibliothèque
http://localhost/tests/Projet4/public/book/add            # Ajouter un livre
http://localhost/tests/Projet4/public/book/1/edit         # Modifier un livre (propriétaire)
```

✅ **Livres publics**
```
http://localhost/tests/Projet4/public/nos-livres          # Tous les livres
http://localhost/tests/Projet4/public/livre/recherche     # Recherche
http://localhost/tests/Projet4/public/livre/1             # Détail d'un livre
```

✅ **Page détail du livre** (nouvellement implémentée)
```
http://localhost/tests/Projet4/public/livre/1             # Détail complet
http://localhost/tests/Projet4/public/livre/2             # Autre livre
# Fonctionnalités :
# • Informations complètes (titre, auteur, description, image)
# • Profil du propriétaire avec lien vers profil public
# • Actions contextuelles selon l'utilisateur (modifier, contacter, etc.)
# • Suggestions d'autres livres du même propriétaire
# • Design responsive avec navigation intuitive
# • Boutons d'action dynamiques (propriétaire vs visiteur vs non-connecté)
```

✅ **Page d'édition de livre** (design avancé)
```
http://localhost/tests/Projet4/public/book/1/edit         # Modifier livre 1
http://localhost/tests/Projet4/public/book/2/edit         # Modifier livre 2
# Fonctionnalités :
# • Design 2 colonnes (photo + informations)
# • Upload d'image avec prévisualisation instantanée
# • Formulaire moderne avec animations CSS
# • Validation temps réel (compteur caractères)
# • Actions multiples (sauver, annuler, supprimer)
# • Protection CSRF et vérification propriétaire
```

✅ **Messagerie** (système complet)
```
http://localhost/tests/Projet4/public/messages            # Liste conversations
http://localhost/tests/Projet4/public/messages/conversation/2  # Discussion avec user 2
http://localhost/tests/Projet4/public/messages/compose/2  # Nouveau message
# Fonctionnalités :
# • Liste des conversations avec aperçu
# • Fil de discussion chronologique
# • Envoi AJAX en temps réel
# • Messages non lus avec compteur
# • Intégration depuis pages livres
# • Design responsive moderne
```

✅ **Test 404** (page inexistante)
```
http://localhost/tests/Projet4/public/page-inexistante
```

### Données de test dans la BDD :

**Utilisateurs** (mot de passe: `password123`)
- alice@example.com (ID: 1)
- bob@example.com (ID: 2)  
- charlie@example.com (ID: 3)

**Livres** : 6 livres total (5 disponibles) répartis sur les 3 utilisateurs
- 2 livres pour Alice (tous disponibles)
- 2 livres pour Bob (1 disponible, 1 non disponible)
- 2 livres pour Charlie (tous disponibles)

**Messages** : 4 messages d'exemple entre utilisateurs

### 🧪 Parcours de test complet :

1. **S'inscrire/Se connecter** → `http://localhost/tests/Projet4/public/register`
2. **Voir son profil** → `http://localhost/tests/Projet4/public/mon-compte`
3. **Aller dans sa bibliothèque** → Cliquer "Voir ma bibliothèque complète"
4. **Ajouter un livre** → Bouton "Ajouter un livre" (avec upload d'image)
5. **Modifier un livre** → Depuis ma bibliothèque, cliquer "Modifier"
6. **Voir tous les livres** → `http://localhost/tests/Projet4/public/nos-livres`
7. **Voir détail d'un livre** → Cliquer sur un livre ou aller sur `/livre/1`
8. **Rechercher des livres** → Barre de recherche sur la page nos-livres

### 🎯 **Tests Spécifiques Nouveaux :**

**Test Upload d'Images :**
- Modifier un livre → Cliquer sur l'image → Sélectionner nouvelle image → Voir prévisualisation → Valider

**Test Page Détail :**
- `/livre/1` → Voir infos complètes → Cliquer profil propriétaire → Voir actions selon statut connexion

**Test Responsive :**
- Redimensionner navigateur → Vérifier adaptation mobile/desktop sur toutes les pages

## 🎯 PROCHAINES ÉTAPES

### À implémenter (dans l'ordre recommandé) :

1. **Système d'authentification** ✅ **TERMINÉ !**
   - [x] Formulaire d'inscription ✅
   - [x] Formulaire de connexion ✅
   - [x] Déconnexion ✅
   - [x] Entité User + UserManager ✅
   
   📖 **Voir le guide complet** : `AUTHENTICATION_GUIDE.md`

2. **Gestion des profils** ✅ **TERMINÉ !**
   - [x] Voir son profil ✅
   - [x] Modifier son profil ✅
   - [x] Voir le profil des autres ✅
   - [x] ProfileController + vues ✅
   
   📖 **Voir le guide complet** : `PROFILE_MANAGEMENT_GUIDE.md`

3. **Bibliothèque personnelle** ✅ **TERMINÉ !**
   - [x] Afficher mes livres ✅
   - [x] Ajouter un livre ✅
   - [x] Modifier un livre ✅ (formulaire design avancé)
   - [x] Supprimer un livre ✅
   - [x] Upload d'image ✅ (avec prévisualisation)
   - [x] Entité Book + BookManager ✅
   - [x] Toggle disponibilité ✅
   - [x] Statistiques et compteurs ✅
   - [x] Page d'édition moderne ✅
   
   📖 **Voir le guide complet** : `BIBLIOTHEQUE_IMPLEMENTATION.md`

4. **Page "Nos livres à l'échange"** ✅ **TERMINÉ !**
   - [x] Liste des livres disponibles ✅ (exclut les livres de l'utilisateur connecté)
   - [x] Champ de recherche ✅ (titre ET auteur)
   - [x] Design responsive ✅
   - [x] Informations propriétaire ✅
   - [x] Statut disponibilité visible ✅

5. **Détail d'un livre** ✅ **TERMINÉ !**
   - [x] Structure route définie ✅
   - [x] Vue détaillée complète ✅
   - [x] Lien vers profil du propriétaire ✅
   - [x] Actions contextuelles (propriétaire/visiteur/non-connecté) ✅
   - [x] Suggestions d'autres livres ✅
   - [x] Design responsive avancé ✅
   - [x] Bouton "Envoyer un message" fonctionnel ✅

6. **Messagerie** ✅ **TERMINÉ !**
   - [x] Structure BDD définie ✅
   - [x] Liste des conversations ✅
   - [x] Afficher une conversation ✅
   - [x] Envoyer un message ✅
   - [x] Répondre ✅
   - [x] MessageController + vues ✅
   - [x] Compteur messages non lus ✅
   - [x] Envoi AJAX temps réel ✅

## 🔧 COMMANDES UTILES

### Vérifier la structure :
```bash
ls -R app/
```

### Voir les fichiers PHP créés :
```bash
find . -name "*.php"
```

### Tester la connexion BDD :
Ouvrir : http://localhost/tests/Projet4/public/
Si pas d'erreur de connexion BDD = ✅ OK !

## 📝 RAPPELS IMPORTANTS

### Architecture MVC
- **Models** = Managers (accès BDD) + Entities (objets métier)
- **Views** = Templates HTML (aucune logique métier)
- **Controllers** = Logique métier, orchestration

### Sécurité
- ✅ Prepared statements (déjà implémenté)
- ✅ password_hash() pour les mots de passe
- ✅ CSRF tokens (déjà dans Session.php)
- ✅ htmlspecialchars() pour affichage
- ✅ Validation des inputs

### CSS
- ❌ PAS de styles inline
- ✅ Uniquement des classes CSS
- Fichier : `public/css/style.css`

## 🐛 RÉSOLUTION DE PROBLÈMES

### Page blanche
→ Activer l'affichage des erreurs dans `config/config.php` :
```php
define('ENVIRONMENT', 'development');
```

### Erreur 404 partout
→ Vérifier que mod_rewrite est activé :
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Erreur connexion BDD
→ Vérifier `config/config.local.php`
→ Vérifier que la BDD `tomtroc` existe

### CSS ne charge pas
→ Vérifier l'URL dans le navigateur
→ Ajuster `BASE_URL` dans `config/config.php`

## 📚 DOCUMENTATION

### Documentation Principale
- **README.md** : Documentation complète du projet
- **QUICKSTART.md** : Ce guide de démarrage rapide
- **STRUCTURE.txt** : Architecture détaillée des fichiers

### Documentation par Étape
- **01-INSTALLATION.md** : Installation et configuration
- **02-ARCHITECTURE.md** : Architecture MVC et structure
- **03-AUTHENTICATION.md** : Système d'authentification
- **04-PROFILS.md** : Gestion des profils utilisateurs
- **05-LIVRES.md** : Bibliothèque et catalogue
- **06-MESSAGERIE.md** : Système de messagerie
- **07-DEVELOPPEMENT.md** : Guide développement et bonnes pratiques

---

**Statut actuel** : ✅ Application TomTroc complètement fonctionnelle !
**Fonctionnalités** : Authentification, profils, bibliothèque, catalogue, messagerie
**Progression** : 85% du projet terminé (6 étapes sur 7 complètes)
**Prêt pour** : Production (v1) ou extensions futures
