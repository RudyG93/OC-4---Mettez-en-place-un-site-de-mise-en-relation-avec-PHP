<?php $activePage = 'books'?>

<div id="book-detail-container">
    <!-- Breadcrumb en haut à gauche -->
    <nav class="breadcrumb-top">
        <a href="<?= BASE_URL ?>nos-livres" class="breadcrumb-link">Nos livres</a>
        <span class="breadcrumb-separator">></span>
        <span class="breadcrumb-current"><?= escape($book->getTitle()) ?></span>
    </nav>

    <div class="book-detail-layout">
        <!-- Image à gauche -->
        <div class="book-image-container">
            <img src="<?= escape($book->getImage() ? BASE_URL . 'uploads/books/' . $book->getImage() : null) ?>" 
                 alt="<?= escape($book->getTitle()) ?>" 
                 class="book-detail-image">
        </div>

        <!-- Contenu à droite -->
        <div class="book-content-container">
            <div class="book-content-inner">
                
                <!-- Titre et auteur -->
                <div class="book-header">
                    <h1 class="book-detail-title"><?= escape($book->getTitle()) ?></h1>
                    <p class="book-detail-author">par <?= escape($book->getAuthor()) ?></p>
                </div>

                <div class="content-divider"></div>

                <!-- Description -->
                <?php if ($book->getDescription()): ?>
                <div class="book-section">
                    <h2 class="section-title">Description</h2>
                    <div class="section-content">
                        <?= nl2br(escape($book->getDescription())) ?>
                    </div>
                </div>
                <?php endif?>

                <!-- Propriétaire -->
                <div class="book-section">
                    <h2 class="section-title">Propriétaire</h2>
                    <a href="<?= BASE_URL ?>profil/<?= $owner->getId() ?>" class="owner-badge">
                        <div class="owner-avatar">
                            <img src="<?= BASE_URL ?>uploads/avatars/<?= escape($owner->getAvatar()) ?>" alt="<?= escape($owner->getUsername()) ?>">
                        </div>
                        <span class="owner-name"><?= escape($owner->getUsername()) ?></span>
                    </a>
                </div>

                <!-- Bouton message -->
                <?php if (Session::isLoggedIn() && Session::getUserId() != $book->getUserId()): ?>
                    <div class="book-actions">
                        <a href="<?= BASE_URL ?>messagerie/conversation/<?= $owner->getId() ?>" 
                           class="btn-message">
                            Envoyer un message
                        </a>
                    </div>
                <?php endif?>

                <?php if (Session::isLoggedIn() && Session::getUserId() == $book->getUserId()): ?>
                    <!-- Actions du propriétaire -->
                    <div class="book-actions owner-actions-section">
                        <a href="<?= BASE_URL ?>book/<?= $book->getId() ?>/edit" class="btn-edit">
                            Modifier
                        </a>
                        <button class="btn-delete delete-book" 
                                data-book-id="<?= $book->getId() ?>" 
                                data-book-title="<?= escape($book->getTitle()) ?>">
                            Supprimer
                        </button>
                    </div>
                <?php endif?>

                <?php if (!Session::isLoggedIn()): ?>
                    <div class="book-actions">
                        <a href="<?= BASE_URL ?>login" class="btn-message">
                            Se connecter pour contacter
                        </a>
                    </div>
                <?php endif?>

            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade is-hidden" id="deleteBookModal" tabindex="-1" aria-labelledby="deleteBookModalLabel" aria-hidden="true">
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
                <form method="POST" id="deleteBookForm">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>
