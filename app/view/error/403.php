<div class="error-page">
    <div class="container">
        <div class="error-content">
            <h1 class="error-code"><?= e($errorCode)?></h1>
            <h2 class="error-message"><?= e($errorMessage)?></h2>
            <p class="error-description"><?= e($errorDescription)?></p>
            <a href="<?= BASE_URL?>" class="btn-primary">Retour à l'accueil</a>
        </div>
    </div>
</div>
