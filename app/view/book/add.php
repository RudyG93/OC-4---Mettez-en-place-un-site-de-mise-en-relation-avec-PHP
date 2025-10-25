<?php
// Récupérer le message flash s'il existe
$flash = Session::getFlash();
?>

<div id="profile-container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif?>

    <div id="profile-content">
        <div class="profile-block">
            <div class="profile-header">
                <h1 class="profile-title">Ajouter un livre</h1>
                <p class="profile-subtitle">Ajoutez un nouveau livre à votre bibliothèque personnelle</p>
            </div>
            
            <form method="POST" action="<?= BASE_URL ?>book/create" enctype="multipart/form-data" class="book-form">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="title" class="form-label">Titre du livre *</label>
                        <input type="text" 
                               id="title" 
                               name="title" 
                               class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                               value="<?= e($oldInput['title'] ?? '') ?>"
                               required>
                        <?php if (isset($errors['title'])): ?>
                            <div class="invalid-feedback"><?= e($errors['title']) ?></div>
                        <?php endif?>
                    </div>
                    
                    <div class="form-group">
                        <label for="author" class="form-label">Auteur *</label>
                        <input type="text" 
                               id="author" 
                               name="author" 
                               class="form-control <?= isset($errors['author']) ? 'is-invalid' : '' ?>"
                               value="<?= e($oldInput['author'] ?? '') ?>"
                               required>
                        <?php if (isset($errors['author'])): ?>
                            <div class="invalid-feedback"><?= e($errors['author']) ?></div>
                        <?php endif?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="image" class="form-label">Image de couverture</label>
                    <input type="file" 
                           id="image" 
                           name="image" 
                           class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>"
                           accept="image/jpeg,image/png,image/gif">
                    <small class="form-text text-muted">
                        Formats acceptés : JPEG, PNG, GIF. Taille maximum : 5MB.
                    </small>
                    <?php if (isset($errors['image'])): ?>
                        <div class="invalid-feedback"><?= e($errors['image']) ?></div>
                    <?php endif?>
                </div>
                
                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" 
                              name="description" 
                              class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
                              rows="4"
                              placeholder="Résumé du livre, votre avis, état du livre..."><?= e($oldInput['description'] ?? '') ?></textarea>
                    <small class="form-text text-muted">Maximum 1000 caractères</small>
                    <?php if (isset($errors['description'])): ?>
                        <div class="invalid-feedback"><?= e($errors['description']) ?></div>
                    <?php endif?>
                </div>
                
                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" 
                               id="is_available" 
                               name="is_available" 
                               value="1"
                               class="form-check-input"
                               <?= ($oldInput['is_available'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label for="is_available" class="form-check-label">
                            Rendre ce livre disponible pour les échanges
                        </label>
                        <small class="form-text text-muted">
                            Vous pourrez modifier cette option plus tard depuis votre bibliothèque.
                        </small>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Ajouter le livre
                    </button>
                    <a href="<?= BASE_URL ?>book/my-books" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour à ma bibliothèque
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.book-form {
    max-width: 600px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.form-control.is-invalid {
    border-color: #dc3545;
}

.form-control.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.25);
}

.invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.form-check {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}

.form-check-input {
    margin-top: 0.125rem;
}

.form-check-label {
    font-weight: normal;
    cursor: pointer;
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #eee;
    margin-top: 2rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    text-decoration: none;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.2s;
}

.btn-primary {
    background-color: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background-color: var(--primary-hover);
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

.alert {
    padding: 0.75rem 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.alert-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-error,
.alert-danger {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

/* Responsive */
@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prévisualisation de l'image
    const imageInput = document.getElementById('image');
    
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Vérifier la taille
                if (file.size > 5 * 1024 * 1024) {
                    alert('L\'image est trop volumineuse. La taille maximum est de 5MB.');
                    this.value = '';
                    return;
                }
                
                // Optionnel : Ajouter une prévisualisation
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Créer ou mettre à jour l'élément de prévisualisation
                    let preview = document.getElementById('image-preview');
                    if (!preview) {
                        preview = document.createElement('div');
                        preview.id = 'image-preview';
                        preview.style.marginTop = '0.5rem';
                        imageInput.parentNode.insertBefore(preview, imageInput.nextSibling);
                    }
                    
                    preview.innerHTML = `
                        <img src="${e.target.result}" 
                             alt="Aperçu" 
                             style="max-width: 200px; max-height: 150px; border-radius: 4px; border: 1px solid #ddd;">
                        <p style="font-size: 0.875rem; color: #6c757d; margin: 0.25rem 0 0 0;">Aperçu de l'image</p>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Compteur de caractères pour la description
    const descriptionTextarea = document.getElementById('description');
    if (descriptionTextarea) {
        const maxLength = 1000;
        
        // Créer le compteur
        const counter = document.createElement('div');
        counter.style.fontSize = '0.875rem';
        counter.style.color = '#6c757d';
        counter.style.textAlign = 'right';
        counter.style.marginTop = '0.25rem';
        
        descriptionTextarea.parentNode.insertBefore(counter, descriptionTextarea.nextSibling);
        
        function updateCounter() {
            const remaining = maxLength - descriptionTextarea.value.length;
            counter.textContent = `${descriptionTextarea.value.length}/${maxLength} caractères`;
            
            if (remaining < 100) {
                counter.style.color = '#dc3545';
            } else if (remaining < 200) {
                counter.style.color = '#ffc107';
            } else {
                counter.style.color = '#6c757d';
            }
        }
        
        descriptionTextarea.addEventListener('input', updateCounter);
        updateCounter(); // Initialiser
    }
});
</script>