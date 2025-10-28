<?php
// Récupérer le message flash s'il existe
$flash = Session::getFlash();
$searchTerm = $searchTerm ?? '';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>css/books.css">

<div id="books-public-container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif?>

    <div class="books-public-content">
        <!-- En-tête -->
        <div class="books-header">
            <h1 class="books-title">Nos livres à l'échange</h1>
            <p class="books-subtitle">Découvrez tous les livres disponibles de la communauté TomTroc</p>
            
            <!-- Barre de recherche -->
            <div class="search-container">
                <form method="GET" action="<?= BASE_URL ?>nos-livres" class="search-form">
                    <div class="search-input-group">
                        <input type="text" 
                               name="q" 
                               class="search-input" 
                               placeholder="Rechercher un livre ou un auteur..."
                               value="<?= e($searchTerm) ?>"
                               autocomplete="off">
                        <?php if (!empty($searchTerm)): ?>
                            <a href="<?= BASE_URL ?>nos-livres" class="clear-search-btn" title="Effacer la recherche">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php else: ?>
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des livres -->
        <div class="books-section">
            <?php if (empty($books)): ?>
                <?php if (!empty($searchTerm)): ?>
                    <!-- Message aucun résultat de recherche -->
                    <div class="no-results">
                        <div class="no-results-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Aucun résultat trouvé</h3>
                        <p>Aucun livre ne correspond à votre recherche "<span class="search-term"><?= e($searchTerm) ?></span>"</p>
                        <a href="<?= BASE_URL ?>nos-livres" class="btn btn-primary">Voir tous les livres</a>
                    </div>
                <?php else: ?>
                    <!-- Message aucun livre disponible -->
                    <div class="no-books">
                        <div class="no-books-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h3>Aucun livre disponible</h3>
                        <p>Il n'y a actuellement aucun livre disponible pour l'échange.</p>
                        <?php if (Session::isLoggedIn()): ?>
                            <a href="<?= BASE_URL ?>mon-compte#add-book-modal" class="btn btn-primary">
                                Ajoutez le vôtre !
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>register" class="btn btn-primary">
                                Rejoignez-nous !
                            </a>
                        <?php endif?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="books-count">
                    <?php if (!empty($searchTerm)): ?>
                        <p><?= count($books) ?> résultat<?= count($books) > 1 ? 's' : '' ?> trouvé<?= count($books) > 1 ? 's' : '' ?> pour "<?= e($searchTerm) ?>"</p>
                    <?php else: ?>
                        <p><?= count($books) ?> livre<?= count($books) > 1 ? 's' : '' ?> disponible<?= count($books) > 1 ? 's' : '' ?></p>
                    <?php endif; ?>
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
                                    <img src="<?= $book->getImagePath() ?>" alt="<?= e($book->getTitle()) ?>">
                                <?php else: ?>
                                    <div class="book-placeholder">
                                        <i class="fas fa-book"></i>
                                    </div>
                                <?php endif?>
                                
                                <div class="book-overlay">
                                    <a href="<?= BASE_URL ?>livre/<?= $book->getId() ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Voir le détail
                                    </a>
                                </div>
                            </div>
                            
                            <div class="book-info">
                                <h3 class="book-title">
                                    <a href="<?= BASE_URL ?>livre/<?= $book->getId() ?>">
                                        <?= e($book->getTitle()) ?>
                                    </a>
                                </h3>
                                <p class="book-author">par <?= e($book->getAuthor()) ?></p>
                                
                                <?php if ($book->getDescription()): ?>
                                    <p class="book-description"><?= e($book->getShortDescription(120)) ?></p>
                                <?php endif?>
                                
                                <div class="book-meta">
                                    <div class="book-owner">
                                        <i class="fas fa-user"></i>
                                        <span>Par <?= e($owner['username']) ?></span>
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
                                    <?php endif?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach?>
                </div>
            <?php endif?>
        </div>
    </div>
</div>