<?php
// Récupérer le message flash s'il existe
$flash = Session::getFlash();
?>

<div id="books-public-container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="books-public-content">
        <!-- En-tête -->
        <div class="books-header">
            <h1 class="books-title">Nos livres à l'échange</h1>
            <p class="books-subtitle">Découvrez tous les livres disponibles de la communauté TomTroc</p>
            
            <!-- Barre de recherche -->
            <div class="search-container">
                <form method="GET" action="<?= BASE_URL ?>livre/recherche" class="search-form">
                    <div class="search-input-group">
                        <input type="text" 
                               name="q" 
                               class="search-input" 
                               placeholder="Rechercher un livre ou un auteur..."
                               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des livres -->
        <div class="books-section">
            <?php if (empty($books)): ?>
                <div class="no-books">
                    <div class="no-books-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3>Aucun livre disponible</h3>
                    <p>Il n'y a actuellement aucun livre disponible pour l'échange.</p>
                    <?php if (Session::isLoggedIn()): ?>
                        <a href="<?= BASE_URL ?>book/add" class="btn btn-primary">
                            Ajoutez le vôtre !
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>register" class="btn btn-primary">
                            Rejoignez-nous !
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="books-count">
                    <p><?= count($books) ?> livre<?= count($books) > 1 ? 's' : '' ?> disponible<?= count($books) > 1 ? 's' : '' ?></p>
                </div>
                
                <div class="books-grid">
                    <?php foreach ($books as $bookData): ?>
                        <?php 
                        $book = $bookData['book'];
                        $owner = $bookData['owner'];
                        ?>
                        <div class="book-card">
                            <div class="book-image">
                                <?php if ($book->getImage()): ?>
                                    <img src="<?= $book->getImagePath() ?>" alt="<?= htmlspecialchars($book->getTitle()) ?>">
                                <?php else: ?>
                                    <div class="book-placeholder">
                                        <i class="fas fa-book"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="book-overlay">
                                    <a href="<?= BASE_URL ?>livre/<?= $book->getId() ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Voir le détail
                                    </a>
                                </div>
                            </div>
                            
                            <div class="book-info">
                                <h3 class="book-title">
                                    <a href="<?= BASE_URL ?>livre/<?= $book->getId() ?>">
                                        <?= htmlspecialchars($book->getTitle()) ?>
                                    </a>
                                </h3>
                                <p class="book-author">par <?= htmlspecialchars($book->getAuthor()) ?></p>
                                
                                <?php if ($book->getDescription()): ?>
                                    <p class="book-description"><?= htmlspecialchars($book->getShortDescription(120)) ?></p>
                                <?php endif; ?>
                                
                                <div class="book-meta">
                                    <div class="book-owner">
                                        <i class="fas fa-user"></i>
                                        <span>Par <?= htmlspecialchars($owner['username']) ?></span>
                                    </div>
                                    
                                    <div class="book-date">
                                        <i class="fas fa-calendar"></i>
                                        <span><?= date('d/m/Y', strtotime($book->getCreatedAt())) ?></span>
                                    </div>
                                </div>
                                
                                <div class="book-actions">
                                    <a href="<?= BASE_URL ?>livre/<?= $book->getId() ?>" class="btn btn-outline-primary">
                                        Voir ce livre
                                    </a>
                                    <?php if (Session::isLoggedIn()): ?>
                                        <button class="btn btn-outline-secondary btn-contact" 
                                                data-book-id="<?= $book->getId() ?>"
                                                data-owner-id="<?= $book->getUserId() ?>">
                                            <i class="fas fa-envelope"></i> Contacter
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Styles pour la page publique des livres */
#books-public-container {
    padding: 2rem 0;
    background-color: var(--bg-color);
    min-height: calc(100vh - 200px);
}

.books-public-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.books-header {
    text-align: center;
    margin-bottom: 3rem;
}

.books-title {
    font-size: 2.5rem;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.books-subtitle {
    font-size: 1.2rem;
    color: var(--text-secondary);
    margin-bottom: 2rem;
}

/* Barre de recherche */
.search-container {
    max-width: 500px;
    margin: 0 auto;
}

.search-form {
    position: relative;
}

.search-input-group {
    display: flex;
    border-radius: 25px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.search-input {
    flex: 1;
    padding: 1rem 1.5rem;
    border: none;
    font-size: 1rem;
    outline: none;
}

.search-input:focus {
    box-shadow: inset 0 0 0 2px var(--primary-color);
}

.search-btn {
    background-color: var(--primary-color);
    color: white;
    border: none;
    padding: 1rem 1.5rem;
    cursor: pointer;
    transition: background-color 0.2s;
}

.search-btn:hover {
    background-color: var(--primary-hover);
}

/* Section des livres */
.books-section {
    margin-top: 2rem;
}

.books-count {
    text-align: center;
    margin-bottom: 2rem;
}

.books-count p {
    font-size: 1.1rem;
    color: var(--text-secondary);
}

/* Grille des livres */
.books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.book-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.book-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

/* Image du livre */
.book-image {
    position: relative;
    height: 250px;
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
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
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

/* Informations du livre */
.book-info {
    padding: 1.5rem;
}

.book-title {
    margin: 0 0 0.5rem 0;
    font-size: 1.3rem;
    line-height: 1.3;
}

.book-title a {
    color: var(--text-primary);
    text-decoration: none;
    transition: color 0.2s;
}

.book-title a:hover {
    color: var(--primary-color);
}

.book-author {
    color: var(--text-secondary);
    font-style: italic;
    margin: 0 0 1rem 0;
}

.book-description {
    color: var(--text-secondary);
    line-height: 1.5;
    margin: 0 0 1.5rem 0;
}

/* Métadonnées */
.book-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 1rem 0;
    padding: 1rem 0;
    border-top: 1px solid #eee;
    border-bottom: 1px solid #eee;
    font-size: 0.9rem;
    color: var(--text-secondary);
}

.book-owner,
.book-date {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.book-owner i,
.book-date i {
    color: var(--primary-color);
}

/* Actions */
.book-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    text-decoration: none;
    border-radius: 6px;
    border: 1px solid transparent;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.2s;
    justify-content: center;
    flex: 1;
}

.btn-primary {
    background-color: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background-color: var(--primary-hover);
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

.btn-outline-secondary {
    border-color: #6c757d;
    color: #6c757d;
    background: transparent;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    color: white;
}

.btn-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
}

/* État vide */
.no-books {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-secondary);
}

.no-books-icon {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 1.5rem;
}

.no-books h3 {
    margin: 1rem 0;
    color: var(--text-primary);
}

.no-books p {
    margin-bottom: 2rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
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

/* Responsive */
@media (max-width: 768px) {
    .books-title {
        font-size: 2rem;
    }
    
    .books-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .book-meta {
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-start;
    }
    
    .book-actions {
        flex-direction: column;
    }
    
    .search-input-group {
        border-radius: 6px;
    }
}

@media (max-width: 480px) {
    .books-public-content {
        padding: 0 0.5rem;
    }
    
    .book-info {
        padding: 1rem;
    }
    
    .books-header {
        margin-bottom: 2rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des boutons de contact
    const contactButtons = document.querySelectorAll('.btn-contact');
    
    contactButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bookId = this.dataset.bookId;
            const ownerId = this.dataset.ownerId;
            
            // Pour l'instant, simple alerte - à remplacer par l'ouverture de la messagerie
            alert('Fonctionnalité de messagerie à implémenter\nLivre ID: ' + bookId + '\nPropriétaire ID: ' + ownerId);
            
            // TODO: Rediriger vers la page de messagerie ou ouvrir une modal
            // window.location.href = '<?= BASE_URL ?>messagerie/nouveau?book_id=' + bookId + '&user_id=' + ownerId;
        });
    });
    
    // Animation au scroll (optionnel)
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observer toutes les cartes de livres
    document.querySelectorAll('.book-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
});
</script>