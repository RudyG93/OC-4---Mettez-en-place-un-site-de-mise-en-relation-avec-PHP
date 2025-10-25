<?php
// Récupérer le message flash s'il existe
$flash = Session::getFlash();
?>


<div id="book-edit-container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif?>

    <div class="book-edit-content">
        <!-- Navigation breadcrumb -->
        <nav class="breadcrumb">
            <a href="<?= BASE_URL ?>book/my-books" class="breadcrumb-link">
                <i class="fas fa-arrow-left"></i> Retour à ma bibliothèque
            </a>
        </nav>

        <!-- Titre de la page -->
        <div class="page-header">
            <h1 class="page-title">Modifier les informations</h1>
        </div>

        <!-- Formulaire d'édition -->
        <form method="POST" action="<?= BASE_URL ?>book/<?= $book->getId() ?>/update" enctype="multipart/form-data" class="book-edit-form">
            <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
            
            <div class="form-layout">
                
                <!-- Section Photo -->
                <div class="photo-section">
                    <div class="form-group">
                        <label for="image" class="form-label">Photo</label>
                        
                        <div class="current-image-container">
                            <?php if ($book->getImage()): ?>
                                <img src="<?= $book->getImagePath() ?>" 
                                     alt="<?= e($book->getTitle()) ?>" 
                                     class="current-book-image"
                                     id="imagePreview">
                            <?php else: ?>
                                <div class="image-placeholder" id="imagePreview">
                                    <i class="fas fa-book"></i>
                                    <span>Aucune image</span>
                                </div>
                            <?php endif?>
                            
                            <div class="image-overlay">
                                <button type="button" class="btn-change-image" onclick="document.getElementById('image').click();">
                                    <i class="fas fa-camera"></i>
                                    Modifier la photo
                                </button>
                            </div>
                        </div>
                        
                        <input type="file" 
                               id="image" 
                               name="image" 
                               accept="image/*" 
                               class="file-input"
                               onchange="previewImage(this)">
                        
                        <p class="form-help">
                            Formats acceptés : JPG, PNG, GIF. Taille max : 5MB
                        </p>
                    </div>
                </div>

                <!-- Section Informations -->
                <div class="info-section">
                    
                    <!-- Titre -->
                    <div class="form-group">
                        <label for="title" class="form-label">Titre</label>
                        <input type="text" 
                               id="title" 
                               name="title" 
                               value="<?= e($book->getTitle()) ?>" 
                               required 
                               maxlength="200"
                               class="form-input"
                               placeholder="Titre du livre">
                        <div class="input-underline"></div>
                    </div>

                    <!-- Auteur -->
                    <div class="form-group">
                        <label for="author" class="form-label">Auteur</label>
                        <input type="text" 
                               id="author" 
                               name="author" 
                               value="<?= e($book->getAuthor()) ?>" 
                               required 
                               maxlength="100"
                               class="form-input"
                               placeholder="Nom de l'auteur">
                        <div class="input-underline"></div>
                    </div>

                    <!-- Commentaire/Description -->
                    <div class="form-group">
                        <label for="description" class="form-label">Commentaire</label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="8"
                                  maxlength="1000"
                                  class="form-textarea"
                                  placeholder="Décrivez ce livre, votre avis, l'état du livre..."><?= e($book->getDescription() ?? '') ?></textarea>
                        <div class="textarea-underline"></div>
                        <div class="character-count">
                            <span id="charCount">0</span>/1000 caractères
                        </div>
                    </div>

                    <!-- Disponibilité -->
                    <div class="form-group">
                        <label for="available" class="form-label">Disponibilité</label>
                        <div class="select-wrapper">
                            <select id="available" name="available" class="form-select">
                                <option value="1" <?= $book->isAvailable() ? 'selected' : '' ?>>disponible</option>
                                <option value="0" <?= !$book->isAvailable() ? 'selected' : '' ?>>non disponible</option>
                            </select>
                            <div class="select-arrow">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-validate">
                            <i class="fas fa-check"></i>
                            Valider
                        </button>
                        
                        <a href="<?= BASE_URL ?>livre/<?= $book->getId() ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Annuler
                        </a>
                        
                        <button type="button" 
                                class="btn btn-danger btn-delete" 
                                data-book-id="<?= $book->getId() ?>" 
                                data-book-title="<?= e($book->getTitle()) ?>">
                            <i class="fas fa-trash"></i>
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteBookModal" tabindex="-1" aria-labelledby="deleteBookModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteBookModalLabel">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer le livre "<span id="bookTitleToDelete"></span>" ?</p>
                <p><strong>Cette action est irréversible.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form method="POST" id="deleteBookForm" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Compteur de caractères pour la description
    const descriptionTextarea = document.getElementById('description');
    const charCountSpan = document.getElementById('charCount');
    
    function updateCharCount() {
        const count = descriptionTextarea.value.length;
        charCountSpan.textContent = count;
        
        // Changer la couleur si proche de la limite
        if (count > 900) {
            charCountSpan.style.color = '#dc3545';
        } else if (count > 800) {
            charCountSpan.style.color = '#fd7e14';
        } else {
            charCountSpan.style.color = '#6c757d';
        }
    }
    
    // Initialiser le compteur
    updateCharCount();
    
    // Écouter les changements
    descriptionTextarea.addEventListener('input', updateCharCount);
    
    // Gestion du bouton de suppression
    const deleteButton = document.querySelector('.btn-delete');
    const deleteModal = document.getElementById('deleteBookModal');
    const deleteForm = document.getElementById('deleteBookForm');
    const bookTitleSpan = document.getElementById('bookTitleToDelete');
    
    if (deleteButton) {
        deleteButton.addEventListener('click', function() {
            const bookId = this.dataset.bookId;
            const bookTitle = this.dataset.bookTitle;
            
            bookTitleSpan.textContent = bookTitle;
            deleteForm.action = '<?= BASE_URL ?>book/' + bookId + '/delete';
            
            // Si vous utilisez Bootstrap modal
            if (typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(deleteModal);
                modal.show();
            } else {
                // Fallback confirmation simple
                if (confirm('Êtes-vous sûr de vouloir supprimer le livre "' + bookTitle + '" ?\n\nCette action est irréversible.')) {
                    // Créer un formulaire pour la suppression
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '<?= BASE_URL ?>book/' + bookId + '/delete';
                    
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = 'csrf_token';
                    csrfInput.value = '<?= Session::generateCsrfToken() ?>';
                    
                    form.appendChild(csrfInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        });
    }
    
    // Validation du formulaire
    const form = document.querySelector('.book-edit-form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const author = document.getElementById('author').value.trim();
            
            if (!title || title.length < 2) {
                e.preventDefault();
                alert('Le titre doit contenir au moins 2 caractères.');
                document.getElementById('title').focus();
                return;
            }
            
            if (!author || author.length < 2) {
                e.preventDefault();
                alert('L\'auteur doit contenir au moins 2 caractères.');
                document.getElementById('author').focus();
                return;
            }
            
            // Afficher un indicateur de chargement
            const submitButton = form.querySelector('.btn-validate');
            if (submitButton) {
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';
                submitButton.disabled = true;
            }
        });
    }
});

// Fonction pour prévisualiser l'image
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        const preview = document.getElementById('imagePreview');
        
        reader.onload = function(e) {
            // Remplacer le contenu par la nouvelle image
            preview.innerHTML = '';
            preview.className = 'current-book-image';
            preview.src = e.target.result;
            preview.style.backgroundImage = `url(${e.target.result})`;
            preview.style.backgroundSize = 'cover';
            preview.style.backgroundPosition = 'center';
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>