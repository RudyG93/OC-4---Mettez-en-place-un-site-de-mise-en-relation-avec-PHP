<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'TomTroc') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
</head>

<body<?php if (isset($activePage) && in_array($activePage, ['login', 'register'])) echo ' class="auth-page"'?>>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <div class="nav-brand">
                    <a href="<?= BASE_URL?>" class="logo">
                        <img src="<?= BASE_URL?>assets/logo.svg" alt="Logo Header TomTroc" class="logo">
                    </a>
                </div>
                <ul class="nav-menu">
                    <li class="nav-group nav-group-left">
                        <a href="<?= BASE_URL?>" class="nav-link<?= (isset($activePage) && $activePage === 'home') ? ' active' : ''?>">Accueil</a>
                        <a href="<?= BASE_URL?>nos-livres" class="nav-link<?= (isset($activePage) && $activePage === 'books') ? ' active' : ''?>">Nos livres à l'échange</a>
                    </li>
                    <li class="nav-separator"></li>
                    <?php if (Session::isLoggedIn()): ?>
                        <?php 
                            $messageManager = new MessageManager();
                            $unreadCount = $messageManager->getUnreadCount(Session::get('user_id'));
                        ?>
                        <li class="nav-group nav-group-right">
                            <a href="<?= BASE_URL?>messagerie" class="nav-link nav-link-icon<?= (isset($activePage) && $activePage === 'messagerie') ? ' active' : ''?>">
                                <img src="<?= BASE_URL?>assets/ico_message.svg" alt="Message Icon" class="nav-icon">
                                Messagerie
                            <?php if ($unreadCount > 0): ?>
                                    <span class="notification-badge"><?= $unreadCount ?></span>
                                <?php endif?>
                            </a>
                            <a href="<?= BASE_URL?>mon-compte" class="nav-link nav-link-icon<?= (isset($activePage) && $activePage === 'account') ? ' active' : ''?>">
                                <img src="<?= BASE_URL?>assets/ico_account.svg" alt="User Icon" class="nav-icon">
                                Mon compte
                            </a>
                            <a href="<?= BASE_URL?>logout" class="nav-link<?= (isset($activePage) && $activePage === 'logout') ? ' active' : ''?>">Déconnexion</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-group nav-group-right">
                            <a href="<?= BASE_URL?>login" class="nav-link<?= (isset($activePage) && $activePage === 'login') ? ' active' : ''?>">Connexion</a>
                        </li>
                    <?php endif?>
                </ul>
            </nav>
        </div>
    </header>

    <?php if (Session::has('flash')): ?>
        <?php $flash = Session::getFlash()?>
        <div class="flash-message flash-<?= e($flash['type']) ?>">
            <div class="container">
                <?= e($flash['message']) ?>
            </div>
        </div>
    <?php endif?>

    <main class="main-content">
        <?= $content?>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-item">Politique de confidentialité</div>
            <div class="footer-item">Mentions légales</div>
            <div class="footer-item">Tom Troc©</div>
            <div class="footer-item">
                <img src="<?= BASE_URL?>assets/ico_footer.svg" alt="Logo Footer TomTroc" class="footer-logo">
            </div>
        </div>
    </footer>
    </body>

</html>