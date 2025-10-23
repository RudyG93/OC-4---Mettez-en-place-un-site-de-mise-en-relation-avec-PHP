<?php 
$activePage = 'account';

// Charger le BookManager pour afficher les livres
require_once APP_PATH . '/model/manager/BookManager.php';
?>

<div class="profile-page">
    <div class="container">
        <div class="profile-container">
            
            <!-- RANGÉE SUPÉRIEURE: 2 BLOCS CÔTE À CÔTE -->
            <div class="profile-top-row">
                
                <!-- BLOC 1: PROFIL UTILISATEUR (GAUCHE) -->
                <div class="profile-user-block">
                    <div class="profile-avatar">
                        <div class="avatar-placeholder">
                            <?php echo strtoupper(substr($user->getUsername(), 0, 1)); ?>
                        </div>
                    </div>
                    
                    <h1 class="profile-username"><?php echo htmlspecialchars($user->getUsername()); ?></h1>
                    
                    <p class="profile-member-info">
                        Membre depuis le 
                        <?php 
                        $createdAt = $user->getCreatedAt();
                        if ($createdAt) {
                            $date = new DateTime($createdAt);
                            echo $date->format('j M Y');
                        } else {
                            echo 'Date inconnue';
                        }
                        ?>
                    </p>
                    
                    <div class="profile-stats">
                        <div class="profile-stats-title">Bibliothèque</div>
                        <div class="profile-book-count">
                            <span class="book-count-icon">📚</span>
                            <?php
                            // Compter les livres de l'utilisateur
                            $bookManager = new BookManager();
                            $totalBooks = $bookManager->countUserBooks($user->getId());
                            $availableBooks = $bookManager->countAvailableUserBooks($user->getId());
                            ?>
                            <span><?= $totalBooks ?> livre<?= $totalBooks > 1 ? 's' : '' ?></span>
                        </div>
                        <div class="profile-book-stats">
                            <small><?= $availableBooks ?> disponible<?= $availableBooks > 1 ? 's' : '' ?></small>
                        </div>
                        <a href="<?= BASE_URL ?>book/my-books" class="btn-library">
                            Voir ma bibliothèque complète
                        </a>
                    </div>
                </div>
                
                <!-- BLOC 2: INFORMATIONS PERSONNELLES (DROITE) -->
                <div class="profile-info-block">
                    <h2 class="profile-info-title">Vos informations personnelles</h2>
                    
                    <form class="profile-form" method="POST" action="<?php echo BASE_URL; ?>mon-compte/update">
                        <input type="hidden" name="csrf_token" value="<?php echo Session::generateCsrfToken(); ?>">
                        
                        <div class="form-group">
                            <label class="form-label">Adresse email</label>
                            <input 
                                type="email" 
                                name="email"
                                class="form-input" 
                                value="<?php echo htmlspecialchars($user->getEmail()); ?>"
                                readonly>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Mot de passe</label>
                            <input 
                                type="password" 
                                name="password"
                                class="form-input" 
                                value="••••••••"
                                readonly>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Pseudo</label>
                            <input 
                                type="text" 
                                name="username"
                                class="form-input editable" 
                                value="<?php echo htmlspecialchars($user->getUsername()); ?>">
                        </div>
                        
                        <button type="submit" class="btn-save">
                            Enregistrer
                        </button>
                    </form>
                </div>
            </div>

            <!-- BLOC 3: TABLEAU DES LIVRES (PLEINE LARGEUR EN BAS) -->
            <div class="books-block">
                <div class="books-header">
                    <h2 class="books-title">Mes livres</h2>
                </div>

                <div class="books-table-container">
                    <?php
                    // Récupérer les livres de l'utilisateur (limité à 5 pour le profil)
                    $userBooks = $bookManager->findByUserId($user->getId());
                    $displayBooks = array_slice($userBooks, 0, 5); // Limiter à 5 livres pour le profil
                    ?>
                    
                    <?php if (empty($userBooks)): ?>
                        <div class="no-books-message">
                            <div class="no-books-icon">📚</div>
                            <h3>Aucun livre dans votre bibliothèque</h3>
                            <p>Commencez à ajouter vos livres pour démarrer les échanges avec la communauté TomTroc.</p>
                            <a href="<?= BASE_URL ?>book/add" class="btn-add-book">Ajouter mon premier livre</a>
                        </div>
                    <?php else: ?>
                        <table class="books-table">
                            <thead>
                                <tr>
                                    <th>Photo</th>
                                    <th>Titre</th>
                                    <th>Auteur</th>
                                    <th>Description</th>
                                    <th>Disponibilité</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($displayBooks as $book): ?>
                                    <tr>
                                        <td>
                                            <?php if ($book->getImage()): ?>
                                                <img src="<?= $book->getImagePath() ?>" alt="Couverture" class="book-cover">
                                            <?php else: ?>
                                                <div class="book-cover-placeholder">
                                                    📚
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="book-title"><?= htmlspecialchars($book->getTitle()) ?></div>
                                        </td>
                                        <td>
                                            <div class="book-author"><?= htmlspecialchars($book->getAuthor()) ?></div>
                                        </td>
                                        <td>
                                            <div class="book-description"><?= htmlspecialchars($book->getShortDescription(100)) ?></div>
                                        </td>
                                        <td>
                                            <span class="availability-badge <?= $book->getAvailabilityClass() ?>">
                                                <?= $book->getAvailabilityText() ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="<?= BASE_URL ?>book/<?= $book->getId() ?>/edit" class="btn-action btn-edit">Éditer</a>
                                                <button class="btn-action btn-delete" onclick="confirmDelete(<?= $book->getId() ?>, '<?= htmlspecialchars($book->getTitle()) ?>')">Supprimer</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                
                                <?php if (count($userBooks) > 5): ?>
                                    <tr class="show-more-row">
                                        <td colspan="6" class="text-center">
                                            <a href="<?= BASE_URL ?>book/my-books" class="btn-show-more">
                                                Voir tous mes livres (<?= count($userBooks) ?> au total)
                                            </a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(bookId, bookTitle) {
    if (confirm('Êtes-vous sûr de vouloir supprimer le livre "' + bookTitle + '" ?\n\nCette action est irréversible.')) {
        // Créer un formulaire pour envoyer la requête POST
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>book/' + bookId + '/delete';
        
        // Ajouter le token CSRF
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = 'csrf_token';
        csrfToken.value = '<?= Session::generateCsrfToken() ?>';
        form.appendChild(csrfToken);
        
        // Ajouter le formulaire au DOM et le soumettre
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
