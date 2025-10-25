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
        <!-- En-tête de la bibliothèque -->
        <div class="profile-block">
            <div class="profile-header">
                <h1 class="profile-title">Ma bibliothèque</h1>
            </div>
            
            <div class="library-stats">
                <div class="stat-item">
                    <span class="stat-number"><?= $totalBooks ?></span>
                    <span class="stat-label">Livre<?= $totalBooks > 1 ? 's' : '' ?> au total</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= $availableBooks ?></span>
                    <span class="stat-label">Disponible<?= $availableBooks > 1 ? 's' : '' ?></span>
                </div>
            </div>
            
            <div class="library-actions">
                <a href="<?= BASE_URL ?>book/add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter un livre
                </a>
            </div>
        </div>

        <!-- Liste des livres -->
        <div class="profile-block">
            <h2>Mes livres</h2>
            
            <?php if (empty($books)): ?>
                <div class="empty-library">
                    <div class="empty-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3>Votre bibliothèque est vide</h3>
                    <p>Commencez par ajouter vos premiers livres pour démarrer les échanges avec la communauté TomTroc.</p>
                    <a href="<?= BASE_URL ?>book/add" class="btn btn-primary">Ajouter mon premier livre</a>
                </div>
            <?php else: ?>
                <div class="books-grid">
                    <?php foreach ($books as $book): ?>
                        <div class="book-card">
                            <div class="book-image">
                                <?php if ($book->getImage()): ?>
                                    <img src="<?= $book->getImagePath() ?>" alt="<?= e($book->getTitle()) ?>">
                                <?php else: ?>
                                    <div class="book-placeholder">
                                        <i class="fas fa-book"></i>
                                    </div>
                                <?php endif?>
                                
                                <div class="book-overlay">
                                    <div class="book-actions">
                                        <a href="<?= BASE_URL ?>livre/<?= $book->getId() ?>" class="btn btn-sm btn-secondary" title="Voir le détail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>book/<?= $book->getId() ?>/edit" class="btn btn-sm btn-primary" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger delete-book" 
                                                data-book-id="<?= $book->getId() ?>" 
                                                data-book-title="<?= e($book->getTitle()) ?>"
                                                title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="book-info">
                                <h3 class="book-title"><?= e($book->getTitle()) ?></h3>
                                <p class="book-author">par <?= e($book->getAuthor()) ?></p>
                                
                                <?php if ($book->getDescription()): ?>
                                    <p class="book-description"><?= e($book->getShortDescription(80)) ?></p>
                                <?php endif?>
                                
                                <div class="book-status">
                                    <span class="availability-badge <?= $book->getAvailabilityClass() ?>">
                                        <?= $book->getAvailabilityText() ?>
                                    </span>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary toggle-availability" 
                                            data-book-id="<?= $book->getId() ?>"
                                            data-current-status="<?= $book->isAvailable() ? '1' : '0' ?>">
                                        <?= $book->isAvailable() ? 'Masquer' : 'Rendre disponible' ?>
                                    </button>
                                </div>
                                
                                <div class="book-meta">
                                    <small class="text-muted">
                                        Ajouté le <?= date('d/m/Y', strtotime($book->getCreatedAt())) ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach?>
                </div>
            <?php endif?>
        </div>
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
/* Styles spécifiques à la bibliothèque */
.library-stats {
    display: flex;
    gap: 2rem;
    margin: 1.5rem 0;
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 8px;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 2rem;
    font-weight: bold;
    color: var(--primary-color);
}

.stat-label {
    font-size: 0.9rem;
    color: #666;
}

.library-actions {
    margin-top: 1rem;
}

.books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.book-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
    background: white;
}

.book-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.book-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.book-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.book-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
    color: #6c757d;
    font-size: 3rem;
}

.book-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
}

.book-image:hover .book-overlay {
    opacity: 1;
}

.book-actions {
    display: flex;
    gap: 0.5rem;
}

.book-info {
    padding: 1rem;
}

.book-title {
    font-size: 1.1rem;
    font-weight: bold;
    margin: 0 0 0.5rem 0;
    color: #333;
}

.book-author {
    color: #666;
    margin: 0 0 0.75rem 0;
    font-style: italic;
}

.book-description {
    font-size: 0.9rem;
    color: #555;
    line-height: 1.4;
    margin: 0 0 1rem 0;
}

.book-status {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 1rem 0;
}

.availability-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
}

.availability-available {
    background-color: #d4edda;
    color: #155724;
}

.availability-unavailable {
    background-color: #f8d7da;
    color: #721c24;
}

.book-meta {
    border-top: 1px solid #eee;
    padding-top: 0.75rem;
    margin-top: 1rem;
}

.empty-library {
    text-align: center;
    padding: 3rem 1rem;
    color: #666;
}

.empty-icon {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 1rem;
}

.empty-library h3 {
    margin: 1rem 0;
    color: #333;
}

.empty-library p {
    margin-bottom: 2rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.btn {
    display: inline-block;
    padding: 0.5rem 1rem;
    text-decoration: none;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-size: 0.9rem;
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

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background-color: #c82333;
}

.btn-outline-secondary {
    border: 1px solid #6c757d;
    color: #6c757d;
    background: transparent;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    color: white;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
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
    .books-grid {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1rem;
    }
    
    .library-stats {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .book-status {
        flex-direction: column;
        gap: 0.5rem;
        align-items: stretch;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la suppression de livres
    const deleteButtons = document.querySelectorAll('.delete-book');
    const deleteModal = document.getElementById('deleteBookModal');
    const deleteForm = document.getElementById('deleteBookForm');
    const bookTitleSpan = document.getElementById('bookTitleToDelete');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bookId = this.dataset.bookId;
            const bookTitle = this.dataset.bookTitle;
            
            bookTitleSpan.textContent = bookTitle;
            deleteForm.action = '<?= BASE_URL ?>book/' + bookId + '/delete';
            
            // Si vous utilisez Bootstrap modal
            if (typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(deleteModal);
                modal.show();
            } else {
                // Fallback pour affichage simple
                deleteModal.style.display = 'block';
            }
        });
    });
    
    // Gestion du toggle de disponibilité
    const toggleButtons = document.querySelectorAll('.toggle-availability');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bookId = this.dataset.bookId;
            const currentStatus = this.dataset.currentStatus;
            
            fetch('<?= BASE_URL ?>book/' + bookId + '/toggle-availability', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mettre à jour le bouton
                    this.dataset.currentStatus = data.isAvailable ? '1' : '0';
                    this.textContent = data.isAvailable ? 'Masquer' : 'Rendre disponible';
                    
                    // Mettre à jour le badge
                    const card = this.closest('.book-card');
                    const badge = card.querySelector('.availability-badge');
                    badge.textContent = data.text;
                    badge.className = 'availability-badge ' + (data.isAvailable ? 'availability-available' : 'availability-unavailable');
                } else {
                    alert('Erreur: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de la mise à jour.');
            });
        });
    });
});
</script>