<div class="error-page">
    <div class="container">
        <div class="error-content">
            <h1 class="error-code"><?= escape($errorCode)?></h1>
            <h2 class="error-message"><?= escape($errorMessage)?></h2>
            <p class="error-description"><?= escape($errorDescription)?></p>
            <a href="<?= BASE_URL?>" class="btn-primary">Retour à l'accueil</a>
        </div>
    </div>
</div>
