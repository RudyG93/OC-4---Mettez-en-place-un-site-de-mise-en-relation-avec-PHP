# 🚀 GUIDE DE DÉMARRAGE RAPIDE - TomTroc

## ✅ Ce qui est DÉJÀ FAIT

1. ✅ Structure MVC complète créée
2. ✅ Toutes les classes core implémentées
3. ✅ Système de routage fonctionnel
4. ✅ Base de données SQL définie avec relations
5. ✅ Page "Hello World" opérationnelle
6. ✅ Système de sessions et CSRF
7. ✅ Layout HTML/CSS responsive
8. ✅ .gitignore configuré

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

Vous devriez voir la page **"Hello World !"** 🎉

## 🧪 TESTER LES FONCTIONNALITÉS

### Routes disponibles actuellement :

✅ **Page d'accueil**
```
http://localhost/tests/Projet4/public/
```

✅ **Test 404** (page inexistante)
```
http://localhost/tests/Projet4/public/page-inexistante
```

### Données de test dans la BDD :

**Utilisateurs** (mot de passe: `password123`)
- alice@example.com
- bob@example.com
- charlie@example.com

**Livres** : 6 livres disponibles
**Messages** : 4 messages d'exemple

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

3. **Bibliothèque personnelle**
   - [ ] Afficher mes livres
   - [ ] Ajouter un livre
   - [ ] Modifier un livre
   - [ ] Supprimer un livre
   - [ ] Upload d'image
   - [ ] Entité Book + BookManager

4. **Page "Nos livres"**
   - [ ] Liste des livres disponibles
   - [ ] Recherche par titre
   - [ ] Filtres

5. **Détail d'un livre**
   - [ ] Affichage complet
   - [ ] Lien vers profil du propriétaire
   - [ ] Bouton "Envoyer un message"

6. **Messagerie**
   - [ ] Liste des conversations
   - [ ] Afficher une conversation
   - [ ] Envoyer un message
   - [ ] Répondre
   - [ ] Entité Message + MessageManager

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

Voir `README.md` pour la documentation complète.

---

**Statut actuel** : ✅ Hello World fonctionnel !
**Prêt pour** : Développement des fonctionnalités métier
