<div class="error-page">
    <div class="container">
        <div class="error-content">
            <h1 class="error-code"><?php echo htmlspecialchars($errorCode); ?></h1>
            <h2 class="error-message"><?php echo htmlspecialchars($errorMessage); ?></h2>
            <p class="error-description"><?php echo htmlspecialchars($errorDescription); ?></p>
            <a href="<?php echo BASE_URL; ?>" class="btn-primary">Retour à l'accueil</a>
        </div>
    </div>
</div>
