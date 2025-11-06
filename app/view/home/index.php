<?php $activePage = 'home' ?>

<link rel="stylesheet" href="<?= BASE_URL ?>css/home.css">

<div class="home-container">
    <!-- Bloc 1 -->
    <section class="home-block-1">
        <div class="block-1-content">
            <div class="block-1-left">
                <h2>Rejoignez nos lecteurs passionnés</h2>
                <p>
                    Donnez une nouvelle vie à vos livres en les échangeant avec d'autres amoureux de la lecture.
                    Nous croyons en la magie du partage de connaissances et d'histoires à travers les livres.
                </p>
                <a href="<?= BASE_URL ?>nos-livres" class="btn-discover">Découvrir</a>
            </div>
            
            <div class="block-1-right">
                <img src="<?= BASE_URL ?>assets/hamza-home.png" alt="Hamza">
                <p class="image-caption">Hamza</p>
            </div>
        </div>
    </section>
</div>

<!-- Bloc 2 -->
<section class="home-block-2">
    <div class="home-container">
        <h2>Les derniers livres ajoutés</h2>
        
        <div class="latest-books-grid">
            <?php foreach ($latestBooks as $bookData): 
                $book = $bookData['book'];
                $owner = $bookData['owner'];
            ?>
                <a href="<?= BASE_URL ?>livre/<?= $book->getId() ?>" class="book-card-link">
                    <div class="book-card">
                        <div class="book-image">
                            <?php if ($book->getImage()): ?>
                                <img src="<?= escape(BASE_URL . 'uploads/books/' . $book->getImage()) ?>" alt="<?= escape($book->getTitle()) ?>">
                            <?php else: ?>
                                <div class="book-placeholder">📚</div>
                            <?php endif; ?>
                        </div>
                        <div class="book-info">
                            <h3 class="book-title"><?= escape($book->getTitle()) ?></h3>
                            <p class="book-author"><?= escape($book->getAuthor()) ?></p>
                            <p class="book-owner">
                                <span>Vendu par: <?= escape($owner['username']) ?></span>
                            </p>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        
        <div class="block-2-action">
            <a href="<?= BASE_URL ?>nos-livres" class="btn-see-all">Voir tous les livres</a>
        </div>
    </div>
</section>

<div class="home-container">
    <!-- Bloc 3 -->
    <section class="home-block-3">
        <h2>Comment ça marche ?</h2>
        <p class="block-3-subtitle">
            Échanger des livres avec TomTroc c'est simple et amusant ! Suivez ces étapes pour commencer :
        </p>
        
        <div class="steps-grid">
            <div class="step-card">
                <p>Inscrivez-vous gratuitement sur notre plateforme.</p>
            </div>
            <div class="step-card">
                <p>Ajoutez les livres que vous souhaitez échanger à votre profil.</p>
            </div>
            <div class="step-card">
                <p>Parcourez les livres disponibles chez d'autres membres.</p>
            </div>
            <div class="step-card">
                <p>Proposez un échange et discutez avec d'autres passionnés de lecture.</p>
            </div>
        </div>
        
        <div class="block-3-action">
            <a href="<?= BASE_URL ?>nos-livres" class="btn-see-all-outline">Voir tous les livres</a>
        </div>
    </section>
</div>

<!-- Image bandeau séparateur -->
<div class="home-banner">
    <img src="<?= BASE_URL ?>assets/bandeau-home.png" alt="Bannière TomTroc">
</div>

<div class="home-container">
    <!-- Bloc 4 -->
    <section class="home-block-4">
        <h2>Nos valeurs</h2>
        <div class="block-4-content">
            <p>
                Chez Tom Troc, nous mettons l'accent sur le partage, la découverte et la communauté. 
                Nos valeurs sont ancrées dans notre passion pour les livres et notre désir de créer des liens entre les lecteurs. 
                Nous croyons en la puissance des histoires pour rassembler les gens et inspirer des conversations enrichissantes.
            </p>
            <p>
                Notre association a été fondée avec une conviction profonde : chaque livre mérite d'être lu et partagé. 
            </p>
            <p>
                Nous sommes passionnés par la création d'une plateforme conviviale qui permet aux lecteurs de se connecter, 
                de partager leurs découvertes littéraires et d'échanger des livres qui attendent patiemment sur les étagères.
            </p>
        </div>
        
        <div class="block-4-signature">
            <img src="<?= BASE_URL ?>assets/heart-home.svg" alt="Signature">
            <p>L'équipe Tom Troc</p>
        </div>
    </section>
</div>