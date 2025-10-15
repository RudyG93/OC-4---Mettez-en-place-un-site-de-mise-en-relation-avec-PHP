<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? htmlspecialchars($title) : 'TomTroc'; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <div class="nav-brand">
                    <a href="<?php echo BASE_URL; ?>" class="logo">TomTroc</a>
                </div>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>" class="nav-link">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>nos-livres" class="nav-link">Nos livres</a>
                    </li>
                    <?php if (Session::isLoggedIn()): ?>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>messagerie" class="nav-link">Messagerie</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>mon-compte" class="nav-link">Mon compte</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>logout" class="nav-link">Déconnexion</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>login" class="nav-link">Connexion</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>register" class="nav-link btn-primary">Inscription</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <?php if (Session::has('flash')): ?>
        <?php $flash = Session::getFlash(); ?>
        <div class="flash-message flash-<?php echo htmlspecialchars($flash['type']); ?>">
            <div class="container">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        </div>
    <?php endif; ?>

    <main class="main-content">
        <?php echo $content; ?>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-item">Politique de confidentialité</div>
            <div class="footer-item">Mentions légales</div>
            <div class="footer-item">Tom Troc©</div>
            <div class="footer-item">
                <img src="<?php echo BASE_URL; ?>assets/ico_footer.svg" alt="Logo Footer TomTroc" class="footer-logo">
            </div>
        </div>
    </footer>
</body>
</html>
