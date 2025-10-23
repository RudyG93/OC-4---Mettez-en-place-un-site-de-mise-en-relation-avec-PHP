<?php
// Récupérer le message flash s'il existe
$flash = Session::getFlash();
?>

<div id="book-detail-container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="book-detail-content">
        <!-- Navigation breadcrumb -->
        <nav class="breadcrumb">
            <a href="<?= BASE_URL ?>nos-livres" class="breadcrumb-link">
                <i class="fas fa-arrow-left"></i> Retour aux livres
            </a>
        </nav>

        <!-- Contenu principal du livre -->
        <div class="book-detail-main">
            
            <!-- Section image -->
            <div class="book-image-section">
                <?php if ($book->getImage()): ?>
                    <img src="<?= $book->getImagePath() ?>" 
                         alt="<?= htmlspecialchars($book->getTitle()) ?>" 
                         class="book-detail-image">
                <?php else: ?>
                    <div class="book-image-placeholder">
                        <i class="fas fa-book"></i>
                        <span>Aucune image</span>
                    </div>
                <?php endif; ?>
                
                <!-- Statut de disponibilité -->
                <div class="availability-status">
                    <span class="availability-badge <?= $book->getAvailabilityClass() ?>">
                        <i class="fas <?= $book->isAvailable() ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                        <?= $book->getAvailabilityText() ?>
                    </span>
                </div>
            </div>

            <!-- Section informations -->
            <div class="book-info-section">
                
                <!-- En-tête du livre -->
                <div class="book-header">
                    <h1 class="book-detail-title"><?= htmlspecialchars($book->getTitle()) ?></h1>
                    <p class="book-detail-author">par <?= htmlspecialchars($book->getAuthor()) ?></p>
                </div>

                <!-- Métadonnées -->
                <div class="book-metadata">
                    <div class="metadata-item">
                        <i class="fas fa-calendar-plus"></i>
                        <span>Ajouté le <?= date('d/m/Y', strtotime($book->getCreatedAt())) ?></span>
                    </div>
                    
                    <?php if ($book->getUpdatedAt() !== $book->getCreatedAt()): ?>
                    <div class="metadata-item">
                        <i class="fas fa-edit"></i>
                        <span>Modifié le <?= date('d/m/Y', strtotime($book->getUpdatedAt())) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <?php if ($book->getDescription()): ?>
                <div class="book-description">
                    <h3>Description</h3>
                    <div class="description-content">
                        <?= nl2br(htmlspecialchars($book->getDescription())) ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Informations du propriétaire -->
                <div class="owner-info">
                    <h3>Propriétaire</h3>
                    <div class="owner-card">
                        <div class="owner-avatar">
                            <?= strtoupper(substr($owner->getUsername(), 0, 1)) ?>
                        </div>
                        <div class="owner-details">
                            <div class="owner-name">
                                <a href="<?= BASE_URL ?>profil/<?= $owner->getId() ?>" class="owner-link">
                                    <?= htmlspecialchars($owner->getUsername()) ?>
                                </a>
                            </div>
                            
                            <?php if ($owner->hasBio()): ?>
                            <div class="owner-bio">
                                <?= htmlspecialchars($owner->getBio()) ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="owner-member-since">
                                <i class="fas fa-user-clock"></i>
                                Membre depuis <?= date('M Y', strtotime($owner->getCreatedAt())) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions disponibles -->
                <div class="book-actions">
                    
                    <?php if (Session::isLoggedIn()): ?>
                        <?php if (Session::getUserId() == $book->getUserId()): ?>
                            <!-- Actions du propriétaire -->
                            <div class="owner-actions">
                                <h4>Gérer ce livre</h4>
                                <div class="action-buttons">
                                    <a href="<?= BASE_URL ?>book/<?= $book->getId() ?>/edit" class="btn btn-primary">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <button class="btn btn-danger delete-book" 
                                            data-book-id="<?= $book->getId() ?>" 
                                            data-book-title="<?= htmlspecialchars($book->getTitle()) ?>">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Actions pour les autres utilisateurs -->
                            <?php if ($book->isAvailable()): ?>
                            <div class="contact-actions">
                                <h4>Intéressé par ce livre ?</h4>
                                <div class="action-buttons">
                                    <a href="<?= BASE_URL ?>profil/<?= $owner->getId() ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-user"></i> Voir le profil de <?= htmlspecialchars($owner->getUsername()) ?>
                                    </a>
                                    <button class="btn btn-primary btn-send-message" 
                                            data-book-id="<?= $book->getId() ?>"
                                            data-owner-id="<?= $owner->getId() ?>"
                                            data-owner-name="<?= htmlspecialchars($owner->getUsername()) ?>">
                                        <i class="fas fa-envelope"></i> Envoyer un message
                                    </button>
                                </div>
                                
                                <div class="contact-info">
                                    <p><i class="fas fa-info-circle"></i> Contactez <?= htmlspecialchars($owner->getUsername()) ?> pour proposer un échange !</p>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="unavailable-info">
                                <div class="unavailable-message">
                                    <i class="fas fa-times-circle"></i>
                                    <span>Ce livre n'est actuellement pas disponible pour l'échange.</span>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Actions pour les utilisateurs non connectés -->
                        <div class="login-prompt">
                            <h4>Intéressé par ce livre ?</h4>
                            <p>Connectez-vous pour contacter le propriétaire et proposer un échange.</p>
                            <div class="action-buttons">
                                <a href="<?= BASE_URL ?>login" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt"></i> Se connecter
                                </a>
                                <a href="<?= BASE_URL ?>register" class="btn btn-outline-primary">
                                    <i class="fas fa-user-plus"></i> Créer un compte
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>

        <!-- Livres suggérés (du même propriétaire) -->
        <?php if (!empty($otherBooks)): ?>
        <div class="suggested-books">
            <h3>Autres livres de <?= htmlspecialchars($owner->getUsername()) ?></h3>
            <div class="suggested-books-grid">
                <?php foreach ($otherBooks as $otherBook): ?>
                <div class="suggested-book-card">
                    <div class="suggested-book-image">
                        <?php if ($otherBook->getImage()): ?>
                            <img src="<?= $otherBook->getImagePath() ?>" alt="<?= htmlspecialchars($otherBook->getTitle()) ?>">
                        <?php else: ?>
                            <div class="book-placeholder-small">
                                <i class="fas fa-book"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="suggested-book-info">
                        <h4>
                            <a href="<?= BASE_URL ?>livre/<?= $otherBook->getId() ?>">
                                <?= htmlspecialchars($otherBook->getTitle()) ?>
                            </a>
                        </h4>
                        <p><?= htmlspecialchars($otherBook->getAuthor()) ?></p>
                        <span class="availability-badge-small <?= $otherBook->getAvailabilityClass() ?>">
                            <?= $otherBook->getAvailabilityText() ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
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

<style>
/* Styles pour la page détail du livre */
#book-detail-container {
    padding: 2rem 0;
    background-color: var(--bg-color);
    min-height: calc(100vh - 200px);
}

.book-detail-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

/* Breadcrumb */
.breadcrumb {
    margin-bottom: 2rem;
}

.breadcrumb-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}

.breadcrumb-link:hover {
    color: var(--primary-hover);
}

/* Layout principal */
.book-detail-main {
    display: grid;
    grid-template-columns: 400px 1fr;
    gap: 3rem;
    margin-bottom: 3rem;
}

/* Section image */
.book-image-section {
    position: sticky;
    top: 2rem;
    height: fit-content;
}

.book-detail-image {
    width: 100%;
    max-height: 500px;
    object-fit: cover;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.book-image-placeholder {
    width: 100%;
    height: 400px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    color: #6c757d;
    font-size: 3rem;
    gap: 1rem;
}

.book-image-placeholder span {
    font-size: 1rem;
    font-weight: 500;
}

.availability-status {
    margin-top: 1.5rem;
    text-align: center;
}

.availability-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    font-weight: 600;
    font-size: 1rem;
}

.availability-available {
    background-color: #d4edda;
    color: #155724;
    border: 2px solid #c3e6cb;
}

.availability-unavailable {
    background-color: #f8d7da;
    color: #721c24;
    border: 2px solid #f5c6cb;
}

/* Section informations */
.book-info-section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.book-header {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #f8f9fa;
}

.book-detail-title {
    font-size: 2.5rem;
    color: var(--text-primary);
    margin: 0 0 0.5rem 0;
    line-height: 1.2;
}

.book-detail-author {
    font-size: 1.3rem;
    color: var(--text-secondary);
    font-style: italic;
    margin: 0;
}

/* Métadonnées */
.book-metadata {
    display: flex;
    gap: 2rem;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #eee;
}

.metadata-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
    font-size: 0.95rem;
}

.metadata-item i {
    color: var(--primary-color);
}

/* Description */
.book-description {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #eee;
}

.book-description h3 {
    color: var(--text-primary);
    margin-bottom: 1rem;
    font-size: 1.3rem;
}

.description-content {
    line-height: 1.7;
    color: var(--text-secondary);
    font-size: 1.05rem;
}

/* Informations propriétaire */
.owner-info {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #eee;
}

.owner-info h3 {
    color: var(--text-primary);
    margin-bottom: 1rem;
    font-size: 1.3rem;
}

.owner-card {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
    transition: background-color 0.2s;
}

.owner-card:hover {
    background: #e9ecef;
}

.owner-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.owner-details {
    flex: 1;
}

.owner-name {
    margin-bottom: 0.5rem;
}

.owner-link {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
    font-size: 1.1rem;
    transition: color 0.2s;
}

.owner-link:hover {
    color: var(--primary-hover);
}

.owner-bio {
    color: var(--text-secondary);
    line-height: 1.5;
    margin-bottom: 0.75rem;
}

.owner-member-since {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.owner-member-since i {
    color: var(--primary-color);
}

/* Actions */
.book-actions h4 {
    color: var(--text-primary);
    margin-bottom: 1rem;
    font-size: 1.2rem;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
    border: 2px solid transparent;
    cursor: pointer;
    font-size: 1rem;
}

.btn-primary {
    background-color: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background-color: var(--primary-hover);
    transform: translateY(-1px);
}

.btn-outline-primary {
    border-color: var(--primary-color);
    color: var(--primary-color);
    background: transparent;
}

.btn-outline-primary:hover {
    background-color: var(--primary-color);
    color: white;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background-color: #c82333;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

.contact-info {
    background: #e8f4f8;
    padding: 1rem;
    border-radius: 6px;
    border-left: 4px solid var(--primary-color);
}

.contact-info p {
    margin: 0;
    color: var(--text-secondary);
}

.contact-info i {
    color: var(--primary-color);
    margin-right: 0.5rem;
}

.unavailable-info {
    text-align: center;
    padding: 2rem;
    background: #fff3cd;
    border-radius: 8px;
    border: 1px solid #ffeaa7;
}

.unavailable-message {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: #856404;
    font-weight: 500;
}

.unavailable-message i {
    color: #dc3545;
}

.login-prompt {
    text-align: center;
    padding: 2rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.login-prompt h4 {
    margin-bottom: 1rem;
}

.login-prompt p {
    margin-bottom: 1.5rem;
    color: var(--text-secondary);
}

/* Livres suggérés */
.suggested-books {
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 2px solid #f8f9fa;
}

.suggested-books h3 {
    color: var(--text-primary);
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
}

.suggested-books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.5rem;
}

.suggested-book-card {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.suggested-book-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.suggested-book-image {
    height: 150px;
    overflow: hidden;
}

.suggested-book-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.book-placeholder-small {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    color: #6c757d;
    font-size: 2rem;
}

.suggested-book-info {
    padding: 1rem;
}

.suggested-book-info h4 {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
}

.suggested-book-info h4 a {
    color: var(--text-primary);
    text-decoration: none;
    transition: color 0.2s;
}

.suggested-book-info h4 a:hover {
    color: var(--primary-color);
}

.suggested-book-info p {
    margin: 0 0 0.5rem 0;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.availability-badge-small {
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

/* Responsive */
@media (max-width: 768px) {
    .book-detail-main {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .book-image-section {
        position: static;
    }
    
    .book-detail-title {
        font-size: 2rem;
    }
    
    .book-metadata {
        flex-direction: column;
        gap: 1rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .suggested-books-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
    }
}

/* Alertes */
.alert {
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
    text-align: center;
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du bouton de suppression (pour les propriétaires)
    const deleteButton = document.querySelector('.delete-book');
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
                    deleteForm.submit();
                }
            }
        });
    }
    
    // Gestion du bouton "Envoyer un message"
    const sendMessageButton = document.querySelector('.btn-send-message');
    
    if (sendMessageButton) {
        sendMessageButton.addEventListener('click', function() {
            const ownerId = this.dataset.ownerId;
            const ownerName = this.dataset.ownerName;
            const bookTitle = '<?= addslashes($book->getTitle()) ?>';
            
            // Rediriger vers la page de composition de message
            window.location.href = '<?= BASE_URL ?>messages/compose/' + ownerId + 
                                   '?book_title=' + encodeURIComponent(bookTitle);
        });
    }
    
    // Animation smooth scroll pour les liens internes
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});
</script>