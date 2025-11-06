<?php $activePage = 'books'?>

<div id="profile-container">
    <div id="profile-content">
        <div class="profile-block">
            <div class="profile-header">
                <h1 class="profile-title">Ajouter un livre</h1>
                <p class="profile-subtitle">Ajoutez un nouveau livre à votre bibliothèque personnelle</p>
            </div>
            
            <link rel="stylesheet" href="<?= BASE_URL ?>css/bookadd.css">
            
            <form method="POST" action="<?= BASE_URL ?>book/create" enctype="multipart/form-data" class="book-form">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="title" class="form-label">Titre du livre *</label>
                        <input type="text" 
                               id="title" 
                               name="title" 
                               class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                               value=""
                               required>
                        <?php if (isset($errors['title'])): ?>
                            <div class="invalid-feedback"><?= escape($errors['title']) ?></div>
                        <?php endif?>
                    </div>
                    
                    <div class="form-group">
                        <label for="author" class="form-label">Auteur *</label>
                        <input type="text" 
                               id="author" 
                               name="author" 
                               class="form-control <?= isset($errors['author']) ? 'is-invalid' : '' ?>"
                               value=""
                               required>
                        <?php if (isset($errors['author'])): ?>
                            <div class="invalid-feedback"><?= escape($errors['author']) ?></div>
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
                        <div class="invalid-feedback"><?= escape($errors['image']) ?></div>
                    <?php endif?>
                </div>
                
                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" 
                              name="description" 
                              class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
                              rows="4"
                              placeholder="Résumé du livre, votre avis, état du livre..."></textarea>
                    <small class="form-text text-muted">Maximum 1000 caractères</small>
                    <?php if (isset($errors['description'])): ?>
                        <div class="invalid-feedback"><?= escape($errors['description']) ?></div>
                    <?php endif?>
                </div>
                
                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" 
                               id="is_available" 
                               name="is_available" 
                               value="1"
                               class="form-check-input">
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
                    <a href="<?= BASE_URL ?>mon-compte" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour à mon compte
                    </a>
                </div>
            </form>
        </div>
    </div>
        </div>
    </div>
</div>
