<?php
// Récupérer le message flash s'il existe
$flash = Session::getFlash();
$activePage = 'messagerie';
?>

<div class="messagerie-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <!-- COLONNE DROITE: État vide -->
    <div class="conversation-empty">
        <div class="empty-state">
            <i class="fas fa-comments"></i>
            <p><strong>Sélectionnez une conversation</strong></p>
            <p>Choisissez une conversation dans la liste pour afficher les messages</p>
        </div>
    </div>
</div>