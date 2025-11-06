<?php $activePage = 'books'?>

<link rel="stylesheet" href="<?= BASE_URL ?>css/bookedit.css">

<div id="book-edit-container">
    <div class="book-edit-content">
        <!-- Lien retour -->
        <a href="<?= BASE_URL ?>mon-compte" class="back-link">← retour</a>
        
        <!-- Titre de la page -->
        <h1 class="page-title">Modifier les informations</h1>

        <!-- Formulaire de suppression d'image (en dehors du formulaire principal) -->
        <?php if ($book->getImage() && $book->getImage() !== 'book_placeholder.png'): ?>
            <form method="POST" action="<?= BASE_URL ?>book/delete-image" class="image-delete-form-hidden" id="deleteImageForm" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette photo ?');">
                <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
                <input type="hidden" name="book_id" value="<?= $book->getId() ?>">
            </form>
        <?php endif ?>

        <!-- Formulaire d'édition -->
        <form method="POST" action="<?= BASE_URL ?>book/<?= $book->getId() ?>/update" enctype="multipart/form-data" class="book-edit-form">
            <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
            
            <div class="form-container">
                <!-- Colonne gauche : Photo -->
                <div class="photo-column">
                    <label class="photo-label">Photo</label>
                    <div class="photo-wrapper">
                        <div class="photo-display" id="photoDisplay">
                            <?php if ($book->getImage()): ?>
                                <img src="<?= BASE_URL . 'uploads/books/' . $book->getImage() ?>" 
                                     alt="<?= escape($book->getTitle()) ?>" 
                                     id="imagePreview">
                            <?php else: ?>
                                <div class="photo-placeholder">
                                    <i class="fas fa-book"></i>
                                </div>
                            <?php endif?>
                        </div>
                        
                        <div class="photo-actions-container">
                            <label for="imageInput" class="link-modify-photo">
                                Modifier la photo
                            </label>
                            
                            <?php if ($book->getImage() && $book->getImage() !== 'book_placeholder.png'): ?>
                                <button type="submit" form="deleteImageForm" class="link-delete-photo">Supprimer</button>
                            <?php endif ?>
                        </div>
                        
               <input type="file" 
                               id="imageInput" 
                               name="image" 
                               accept="image/jpeg,image/png,image/gif" 
                               onchange="if(this.files && this.files[0]){var reader = new FileReader(); reader.onload = function(e){document.getElementById('imagePreview').src = e.target.result;}; reader.readAsDataURL(this.files[0]);}"
                   class="is-hidden">
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
                               value="<?= escape($book->getTitle()) ?>" 
                               required>
                    </div>

                    <!-- Auteur -->
                    <div class="form-group">
                        <label for="author" class="form-label">Auteur</label>
                        <input type="text" 
                               id="author" 
                               name="author" 
                               class="form-input"
                               value="<?= escape($book->getAuthor()) ?>" 
                               required>
                    </div>

                    <!-- Commentaire -->
                    <div class="form-group">
                        <label for="description" class="form-label">Commentaire</label>
                        <textarea id="description" 
                                  name="description" 
                                  class="form-textarea"
                                  rows="6"
                                  maxlength="1000"><?= escape($book->getDescription() ?? '') ?></textarea>
                    </div>

                    <!-- Disponibilité -->
                    <div class="form-group">
                        <label for="available" class="form-label">Disponibilité</label>
                        <select id="available" name="available" class="form-select">
                            <option value="1" <?= $book->getIsAvailable() ? 'selected' : '' ?>>disponible</option>
                            <option value="0" <?= !$book->getIsAvailable() ? 'selected' : '' ?>>non disponible</option>
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
    </div>
</div>

