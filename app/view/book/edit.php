<?php
// Récupérer le message flash s'il existe
$flash = Session::getFlash();
?>

<link rel="stylesheet" href="<?= BASE_URL ?>css/book-edit.css">

<div id="book-edit-container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif?>

    <div class="book-edit-content">
        <!-- Titre de la page -->
        <h1 class="page-title">Modifier les informations</h1>

        <!-- Formulaire d'édition -->
        <form method="POST" action="<?= BASE_URL ?>book/<?= $book->getId() ?>/update" enctype="multipart/form-data" class="book-edit-form">
            <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
            
            <div class="form-container">
                <!-- Colonne gauche : Photo -->
                <div class="photo-column">
                    <div class="photo-wrapper">
                        <div class="photo-display" id="photoDisplay">
                            <?php if ($book->getImage()): ?>
                                <img src="<?= $book->getImagePath() ?>" 
                                     alt="<?= e($book->getTitle()) ?>" 
                                     id="imagePreview">
                            <?php else: ?>
                                <div class="photo-placeholder">
                                    <i class="fas fa-book"></i>
                                </div>
                            <?php endif?>
                        </div>
                        
                        <label for="imageInput" class="btn-modify-photo">
                            Modifier la photo
                        </label>
                        
                        <input type="file" 
                               id="imageInput" 
                               name="image" 
                               accept="image/jpeg,image/png,image/gif" 
                               onchange="if(this.files && this.files[0]){var reader = new FileReader(); reader.onload = function(e){document.getElementById('imagePreview').src = e.target.result;}; reader.readAsDataURL(this.files[0]);}"
                               style="display: none;">
                    </div>
                </div>

                <!-- Colonne droite : Formulaire -->
                <div class="form-column">
                    <!-- Titre -->
                    <div class="form-group">
                        <label for="title" class="form-label">Titre</label>
                        <input type="text" 
                               id="title" 
                               name="title" 
                               class="form-input"
                               value="<?= e($book->getTitle()) ?>" 
                               required>
                    </div>

                    <!-- Auteur -->
                    <div class="form-group">
                        <label for="author" class="form-label">Auteur</label>
                        <input type="text" 
                               id="author" 
                               name="author" 
                               class="form-input"
                               value="<?= e($book->getAuthor()) ?>" 
                               required>
                    </div>

                    <!-- Commentaire -->
                    <div class="form-group">
                        <label for="description" class="form-label">Commentaire</label>
                        <textarea id="description" 
                                  name="description" 
                                  class="form-textarea"
                                  rows="6"
                                  maxlength="1000"><?= e($book->getDescription() ?? '') ?></textarea>
                    </div>

                    <!-- Disponibilité -->
                    <div class="form-group">
                        <label for="available" class="form-label">Disponibilité</label>
                        <select id="available" name="available" class="form-select">
                            <option value="1" <?= $book->isAvailable() ? 'selected' : '' ?>>disponible</option>
                            <option value="0" <?= !$book->isAvailable() ? 'selected' : '' ?>>non disponible</option>
                        </select>
                    </div>

                    <!-- Bouton Valider -->
                    <div class="form-actions">
                        <button type="submit" class="btn-validate">
                            Valider
                        </button>
                    </div>
                </div>
            </div>
        </form>
        
        <!-- Section suppression -->
        <div class="delete-section">
            <h3>Zone dangereuse</h3>
            <p>La suppression d'un livre est irréversible.</p>
            <form method="POST" action="<?= BASE_URL ?>book/<?= $book->getId() ?>/delete" 
                  onsubmit="return confirm('Êtes-vous vraiment sûr de vouloir supprimer le livre « <?= e($book->getTitle()) ?> » ?\n\nCette action est irréversible.');">
                <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                    Supprimer définitivement ce livre
                </button>
            </form>
        </div>
    </div>
</div>

