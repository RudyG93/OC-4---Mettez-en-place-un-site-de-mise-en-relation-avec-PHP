<?php $searchTerm = $searchTerm ?? ''; ?>
<?php $activePage = 'books'?>

<link rel="stylesheet" href="<?= BASE_URL ?>css/books.css">

<div id="books-public-container">
    <div class="books-public-content">
        <!-- En-tête -->
        <div class="books-header">
            <h1 class="books-title">Nos livres à l'échange</h1>
            
            <!-- Barre de recherche -->
            <div class="search-container">
                <form method="GET" action="<?= BASE_URL ?>nos-livres" class="search-form">
                    <div class="search-input-group">
                        <input type="text" 
                               name="q" 
                               class="search-input" 
                               placeholder="Rechercher un livre"
                               value="<?= escape($searchTerm) ?>"
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
                        <p>Aucun livre ne correspond à votre recherche "<span class="search-term"><?= escape($searchTerm) ?></span>"</p>
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
                        <p><?= count($books) ?> résultat<?= count($books) > 1 ? 's' : '' ?> trouvé<?= count($books) > 1 ? 's' : '' ?> pour "<?= escape($searchTerm) ?>"</p>
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
                        <a href="<?= BASE_URL ?>livre/<?= $book->getId() ?>" class="book-card-link">
                            <div class="book-card">
                                <div class="book-image">
                                    <img src="<?= $book->getImage() ? BASE_URL . 'uploads/books/' . $book->getImage() : '' ?>" alt="<?= escape($book->getTitle()) ?>">
                                </div>
                                
                                <div class="book-info">
                                    <h3 class="book-title"><?= escape($book->getTitle()) ?></h3>
                                    <p class="book-author"><?= escape($book->getAuthor()) ?></p>

                                    <div class="book-meta">
                                        <div class="book-owner">
                                            <span>Vendu par : <?= escape($owner['username']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach?>
                </div>
            <?php endif?>
        </div>
    </div>
</div>