<?php $activePage = 'profil' ?>

<link rel="stylesheet" href="<?= BASE_URL ?>css/profile.css">

<div class="public-profile-container">
    <div class="public-profile-content">
        <!-- Colonne gauche : Informations utilisateur -->
        <div class="user-info-sidebar">
            <div class="user-avatar-section">
                <div class="user-avatar">
                    <?php if ($user->getAvatar() && $user->getAvatar() !== 'pp_placeholder.png'): ?>
                        <img src="<?= BASE_URL ?>uploads/avatars/<?= escape($user->getAvatar()) ?>" 
                             alt="Avatar de <?= escape($user->getUsername()) ?>" 
                             class="avatar-image">
                    <?php else: ?>
                        <img src="<?= BASE_URL ?>uploads/avatars/pp_placeholder.png" 
                             alt="Avatar par défaut" 
                             class="avatar-image">
                    <?php endif; ?>
                </div>
                
                <h1 class="user-username"><?= escape($user->getUsername()) ?></h1>
                
                <p class="user-member-since">
                    Membre depuis 
                    <?php 
                    $createdAt = $user->getCreatedAt();
                    if ($createdAt) {
                        $date = new DateTime($createdAt);
                        $now = new DateTime();
                        $interval = $date->diff($now);
                        $years = $interval->y;
                        if ($years > 0) {
                            echo $years . ' an' . ($years > 1 ? 's' : '');
                        } else {
                            echo 'moins d\'un an';
                        }
                    } else {
                        echo 'récemment';
                    }
                    ?>
                </p>
                
                <div class="user-library-info">
                    <h3 class="library-title">BIBLIOTHÈQUE</h3>
                    <div class="library-count">
                        <span class="book-icon">📚</span>
                        <span><?= count($userBooks) ?> livre<?= count($userBooks) > 1 ? 's' : '' ?></span>
                    </div>
                </div>
                
                <?php if (Session::isLoggedIn() && Session::getUserId() != $user->getId()): ?>
                    <a href="<?= BASE_URL ?>messagerie/conversation/<?= $user->getId() ?>" class="btn-write-message">
                        Écrire un message
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Colonne droite : Liste des livres -->
        <div class="user-books-section">
            <?php if (empty($userBooks)): ?>
                <div class="no-books-message">
                    <p><?= escape($user->getUsername()) ?> n'a pas encore ajouté de livres.</p>
                </div>
            <?php else: ?>
                <table class="books-table">
                    <thead>
                        <tr>
                            <th>PHOTO</th>
                            <th>TITRE</th>
                            <th>AUTEUR</th>
                            <th>DESCRIPTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userBooks as $book): ?>
                            <tr>
                                <td class="book-photo-cell">
                                    <?php if ($book->getImage()): ?>
                                        <img src="<?= BASE_URL . 'uploads/books/' . $book->getImage() ?>" 
                                             alt="<?= escape($book->getTitle()) ?>" 
                                             class="book-thumbnail">
                                    <?php else: ?>
                                        <div class="book-thumbnail-placeholder">
                                            <i class="fas fa-book"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="book-title-cell">
                                    <a href="<?= BASE_URL ?>livre/<?= $book->getId() ?>">
                                        <?= escape($book->getTitle()) ?>
                                    </a>
                                </td>
                                <td class="book-author-cell">
                                    <?= escape($book->getAuthor()) ?>
                                </td>
                                <td class="book-description-cell">
                                    <?php if ($book->getDescription()): ?>
                                        <?= escape(strlen($book->getDescription()) > 150 ? substr($book->getDescription(), 0, 150) . '...' : $book->getDescription()) ?>
                                    <?php else: ?>
                                        <span class="no-description">Aucune description</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>