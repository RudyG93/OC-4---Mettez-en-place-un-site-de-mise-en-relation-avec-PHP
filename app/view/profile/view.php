<?php 
$activePage = 'account';

// Charger le BookManager pour afficher les livres
require_once APP_PATH . '/model/manager/BookManager.php';
?>

<div class="profile-page">
    <div class="container">
        <h1 class="page-title">Mon compte</h1>
        
        <div class="profile-container">
            
            <!-- RANGÉE SUPÉRIEURE: 2 BLOCS CÔTE À CÔTE -->
            <div class="profile-top-row">
                
                <!-- BLOC 1: PROFIL UTILISATEUR (GAUCHE) -->
                <div class="profile-user-block">
                    <div class="profile-avatar">
                        <?php if ($user->getAvatar() && $user->getAvatar() !== 'pp_placeholder.png'): ?>
                            <img src="<?= BASE_URL ?>uploads/avatars/<?= e($user->getAvatar()) ?>" 
                                 alt="Avatar de <?= e($user->getUsername()) ?>" 
                                 class="avatar-image">
                        <?php else: ?>
                            <img src="<?= BASE_URL ?>uploads/avatars/pp_placeholder.png" 
                                 alt="Avatar par défaut" 
                                 class="avatar-image">
                        <?php endif; ?>
                    </div>
                    
                    <!-- Boutons de gestion d'avatar -->
                    <div class="avatar-actions">
                        <form method="POST" action="<?= BASE_URL ?>mon-compte/update-avatar" enctype="multipart/form-data" class="avatar-upload-form">
                            <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
                            <label for="avatar-upload" class="btn-modify-avatar">
                                Modifier
                            </label>
                            <input type="file" 
                                   id="avatar-upload" 
                                   name="avatar" 
                                   accept="image/jpeg,image/png,image/gif"
                                   style="display: none;"
                                   onchange="if(confirm('Voulez-vous vraiment changer votre avatar ?')) this.form.submit();">
                        </form>
                        
                        <?php if ($user->getAvatar() && $user->getAvatar() !== 'pp_placeholder.png'): ?>
                            <form method="POST" action="<?= BASE_URL ?>mon-compte/delete-avatar" class="avatar-delete-form" onsubmit="return confirm('Voulez-vous vraiment supprimer votre photo de profil ?');">
                                <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
                                <button type="submit" class="btn-delete-avatar">Supprimer</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    
                    <h1 class="profile-username"><?= e($user->getUsername()) ?></h1>
                    
                    <p class="profile-member-info">
                        <?php 
                        $createdAt = $user->getCreatedAt();
                        if ($createdAt) {
                            $createdDate = new DateTime($createdAt);
                            $now = new DateTime();
                            $interval = $createdDate->diff($now);
                            
                            if ($interval->y > 0) {
                                echo 'Membre depuis ' . $interval->y . ' an' . ($interval->y > 1 ? 's' : '');
                            } elseif ($interval->m > 0) {
                                echo 'Membre depuis ' . $interval->m . ' mois';
                            } elseif ($interval->d > 0) {
                                echo 'Membre depuis ' . $interval->d . ' jour' . ($interval->d > 1 ? 's' : '');
                            } else {
                                echo 'Membre depuis aujourd\'hui';
                            }
                        } else {
                            echo 'Membre';
                        }
                        ?>
                    </p>
                    
                    <div class="profile-stats">
                        <div class="profile-stats-title">Bibliothèque</div>
                        <div class="profile-book-count">
                            <span class="book-count-icon">📚</span>
                            <?php
                            // Compter les livres de l'utilisateur
                            $bookManager = new BookManager();
                            $totalBooks = $bookManager->countUserBooks($user->getId());
                            $availableBooks = $bookManager->countAvailableUserBooks($user->getId());
                            ?>
                            <span><?= $totalBooks ?> livre<?= $totalBooks > 1 ? 's' : '' ?></span>
                        </div>
                        <a href="#add-book-modal" class="btn-library btn-add-book-modal">
                            Ajouter un livre à ma bibliothèque
                        </a>
                    </div>
                </div>
                
                <!-- BLOC 2: INFORMATIONS PERSONNELLES (DROITE) -->
                <div class="profile-info-block">
                    <h2 class="profile-info-title">Vos informations personnelles</h2>
                    
                    <form class="profile-form" method="POST" action="<?= BASE_URL ?>mon-compte/update">
                        <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
                        
                        <!-- Email -->
                        <div class="form-group">
                            <label class="form-label">Adresse email</label>
                            <input 
                                type="email" 
                                name="email"
                                class="form-input <?= isset($errors['email']) ? 'input-error' : '' ?>" 
                                value="<?= e($oldInput['email'] ?? $user->getEmail()) ?>"
                                placeholder="votre.email@example.com"
                                required>
                            <?php if (isset($errors['email'])): ?>
                                <span class="error-message"><?= e($errors['email']) ?></span>
                            <?php endif?>
                        </div>

                        <!-- Mot de passe -->
                        <div class="form-group">
                            <label class="form-label">Mot de passe</label>
                            <input 
                                type="password" 
                                name="password"
                                class="form-input <?= isset($errors['password']) ? 'input-error' : '' ?>" 
                                placeholder="••••••••">
                            <?php if (isset($errors['password'])): ?>
                                <span class="error-message"><?= e($errors['password']) ?></span>
                            <?php endif?>
                        </div>

                        <!-- Pseudo -->
                        <div class="form-group">
                            <label class="form-label">Pseudo</label>
                            <input 
                                type="text" 
                                name="username"
                                class="form-input <?= isset($errors['username']) ? 'input-error' : '' ?>" 
                                value="<?= e($oldInput['username'] ?? $user->getUsername()) ?>"
                                placeholder="Votre pseudo"
                                required>
                            <?php if (isset($errors['username'])): ?>
                                <span class="error-message"><?= e($errors['username']) ?></span>
                            <?php endif?>
                        </div>
                        
                        <button type="submit" class="btn-save">
                            Enregistrer
                        </button>
                    </form>
                </div>
            </div>

            <!-- Bloc 3: Tableau des livres -->
            <div class="books-block">
                <div class="books-table-container">
                    <?php
                    // Récupérer TOUS les livres de l'utilisateur
                    $userBooks = $bookManager->findByUserId($user->getId());
                    ?>
                    
                    <?php if (empty($userBooks)): ?>
                        <div class="no-books-message">
                            <div class="no-books-icon">📚</div>
                            <h3>Aucun livre dans votre bibliothèque</h3>
                            <p>Commencez à ajouter vos livres pour démarrer les échanges avec la communauté TomTroc.</p>
                            <a href="#add-book-modal" class="btn-add-book">Ajouter mon premier livre</a>
                        </div>
                    <?php else: ?>
                        <table class="account-books-table">
                            <thead>
                                <tr>
                                    <th>PHOTO</th>
                                    <th>TITRE</th>
                                    <th>AUTEUR</th>
                                    <th>DESCRIPTION</th>
                                    <th>DISPONIBILITÉ</th>
                                    <th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userBooks as $book): ?>
                                    <tr>
                                        <td>
                                            <img src="<?= e($book->getImagePath()) ?>" alt="Couverture de <?= e($book->getTitle()) ?>" class="book-cover">
                                        </td>
                                        <td>
                                            <div class="book-title"><?= e($book->getTitle()) ?></div>
                                        </td>
                                        <td>
                                            <div class="book-author"><?= e($book->getAuthor()) ?></div>
                                        </td>
                                        <td>
                                            <div class="book-description"><?= e($book->getShortDescription(100)) ?></div>
                                        </td>
                                        <td>
                                            <span class="availability-badge <?= $book->getAvailabilityClass() ?>">
                                                <?= $book->getAvailabilityText() ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="<?= BASE_URL ?>book/<?= $book->getId() ?>/edit" class="btn-action btn-edit">Éditer</a>
                                                <form method="POST" action="<?= BASE_URL ?>book/<?= $book->getId() ?>/delete" style="display: inline; margin: 0;"
                                                      onsubmit="return confirm('Voulez-vous vraiment supprimer « <?= e($book->getTitle()) ?> » ?');">
                                                    <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
                                                    <button type="submit" class="btn-action btn-delete">Supprimer</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach?>
                            </tbody>
                        </table>
                    <?php endif?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL D'AJOUT DE LIVRE -->
<div id="add-book-modal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Ajouter un livre à ma bibliothèque</h2>
            <a href="#" class="modal-close" title="Fermer">&times;</a>
        </div>
        
        <div class="modal-body">
            <form method="POST" action="<?= BASE_URL ?>book/create" enctype="multipart/form-data" class="book-add-form">
                <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
                
                <!-- Photo du livre -->
                <div class="form-group">
                    <label class="form-label">Photo du livre (optionnel)</label>
                    <div class="image-upload-area">
                        <div id="imagePreviewModal" class="image-preview">
                            <div class="image-placeholder">
                                <i class="fas fa-book"></i>
                                <span>Aucune image</span>
                            </div>
                        </div>
                        <label for="bookImage" class="btn-upload-image">
                            Choisir une image
                        </label>
                        <input type="file" 
                               id="bookImage" 
                               name="image" 
                               accept="image/jpeg,image/png,image/gif"
                               style="display: none;"
                               onchange="if(this.files && this.files[0]){var reader = new FileReader(); reader.onload = function(e){document.getElementById('imagePreviewModal').innerHTML = '<img src=\'' + e.target.result + '\' alt=\'Aperçu\' style=\'max-width: 100%; max-height: 200px; object-fit: contain;\'>';}; reader.readAsDataURL(this.files[0]);}">
                    <small class="form-help">Formats acceptés : JPG, PNG, GIF (max 2Mo)</small>
                </div>
                
                <!-- Titre -->
                <div class="form-group">
                    <label for="title" class="form-label">Titre *</label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           class="form-input" 
                           placeholder="Ex: Le Petit Prince"
                           maxlength="200"
                           required>
                </div>
                
                <!-- Auteur -->
                <div class="form-group">
                    <label for="author" class="form-label">Auteur *</label>
                    <input type="text" 
                           id="author" 
                           name="author" 
                           class="form-input" 
                           placeholder="Ex: Antoine de Saint-Exupéry"
                           maxlength="100"
                           required>
                </div>
                
                <!-- Description/Commentaire -->
                <div class="form-group">
                    <label for="description" class="form-label">Commentaire (optionnel)</label>
                    <textarea id="description" 
                              name="description" 
                              class="form-textarea" 
                              rows="5"
                              maxlength="1000"
                              placeholder="Décrivez ce livre, donnez votre avis, précisez l'état..."></textarea>
                    <small class="form-help">Maximum 1000 caractères</small>
                </div>
                
                <!-- Disponibilité -->
                <div class="form-group">
                    <label for="is_available" class="form-label">Disponibilité *</label>
                    <select id="is_available" name="is_available" class="form-select" required>
                        <option value="1" selected>Disponible à l'échange</option>
                        <option value="0">Non disponible</option>
                    </select>
                    <small class="form-help">Indiquez si ce livre est disponible pour un échange</small>
                </div>
                
                <!-- Boutons d'action -->
                <div class="modal-actions">
                    <a href="#" class="btn-cancel">Annuler</a>
                    <button type="submit" class="btn-submit">
                        Ajouter le livre
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* MODAL OVERLAY */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
    overflow-y: auto;
    padding: 2rem 1rem;
}

.modal-overlay:target {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-container {
    background: white;
    border-radius: 12px;
    max-width: 600px;
    width: 100%;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease;
    margin: auto;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* MODAL HEADER */
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
    border-bottom: 2px solid #e9ecef;
}

.modal-title {
    font-size: 1.5rem;
    color: #2c3e50;
    margin: 0;
    font-weight: 600;
}

.modal-close {
    font-size: 2rem;
    color: #6c757d;
    text-decoration: none;
    line-height: 1;
    transition: color 0.2s;
}

.modal-close:hover {
    color: #dc3545;
}

/* MODAL BODY */
.modal-body {
    padding: 2rem;
}

.book-add-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* IMAGE UPLOAD AREA */
.image-upload-area {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.image-preview {
    width: 100%;
    max-width: 250px;
    height: 200px;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
}

.image-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    color: #6c757d;
}

.image-placeholder i {
    font-size: 3rem;
}

.btn-upload-image {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background: #f8f9fa;
    color: #2c3e50;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-upload-image:hover {
    background: #e9ecef;
    border-color: #adb5bd;
}

/* FORM ELEMENTS */
.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    font-weight: 500;
    color: #2c3e50;
    font-size: 0.95rem;
}

.form-input,
.form-textarea,
.form-select {
    padding: 0.75rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    color: #2c3e50;
    transition: all 0.3s;
    outline: none;
    font-family: inherit;
}

.form-input:focus,
.form-textarea:focus,
.form-select:focus {
    border-color: #00AC66;
    box-shadow: 0 0 0 3px rgba(0, 172, 102, 0.1);
}

.form-textarea {
    resize: vertical;
    min-height: 100px;
}

.form-help {
    color: #6c757d;
    font-size: 0.85rem;
}

/* MODAL ACTIONS */
.modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

.btn-cancel {
    padding: 0.75rem 1.5rem;
    background: transparent;
    color: #6c757d;
    border: 2px solid #6c757d;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-cancel:hover {
    background: #6c757d;
    color: white;
}

.btn-submit {
    padding: 0.75rem 2rem;
    background: linear-gradient(135deg, #00AC66 0%, #00d97e 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-submit:hover {
    background: linear-gradient(135deg, #009955 0%, #00c76d 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 172, 102, 0.3);
}

/* BOUTON MODAL TRIGGER */
.btn-add-book-modal {
    background: linear-gradient(135deg, #00AC66 0%, #00d97e 100%) !important;
}

.btn-add-book-modal:hover {
    background: linear-gradient(135deg, #009955 0%, #00c76d 100%) !important;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .modal-container {
        margin: 1rem;
    }
    
    .modal-header,
    .modal-body {
        padding: 1.5rem;
    }
    
    .modal-actions {
        flex-direction: column;
    }
    
    .btn-cancel,
    .btn-submit {
        width: 100%;
        text-align: center;
    }
}
</style>