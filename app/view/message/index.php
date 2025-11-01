<?php
// Récupérer le message flash s'il existe
$flash = Session::getFlash();
$activePage = 'messagerie';
?>

<div class="messagerie-layout">
    <!-- COLONNE GAUCHE: Liste des conversations -->
    <div class="conversations-sidebar">
        <div class="sidebar-header">
            <h1 class="sidebar-title">Messagerie</h1>
        </div>
        
        <div class="conversations-list">
            <?php if (empty($conversations)): ?>
                <div class="no-conversations">
                    <p><strong>Aucune conversation</strong></p>
                    <p>Commencez par envoyer un message depuis la page d'un livre.</p>
                </div>
            <?php else: ?>
                <?php foreach ($conversations as $conversation): ?>
                    <a href="<?= BASE_URL ?>messages/conversation/<?= $conversation->getOtherUserId() ?>" 
                       class="conversation-item <?= $conversation->getUnreadCount() > 0 ? 'conversation-unread' : '' ?>">
                        
                        <div class="conversation-content">
                            <div class="conversation-avatar">
                                <img src="<?= BASE_URL ?>uploads/avatars/<?= $conversation->getOtherAvatar() ?>" 
                                     alt="<?= e($conversation->getOtherUsername()) ?>">
                            </div>
                            
                            <div class="conversation-info">
                                <div class="conversation-header-info">
                                    <h3 class="conversation-name"><?= e($conversation->getOtherUsername()) ?></h3>
                                    <span class="conversation-time"><?= $conversation->getFormattedDate() ?></span>
                                </div>
                                
                                <p class="conversation-preview">
                                    <?php if ($conversation->isSentBy($currentUser->getId())): ?>
                                        Vous : 
                                    <?php endif?>
                                    <?= e($conversation->getExcerpt(50)) ?>
                                </p>
                            </div>
                        </div>
                    </a>
                <?php endforeach?>
            <?php endif?>
        </div>
    </div>

    <!-- COLONNE DROITE: État vide -->
    <div class="conversation-empty">
        <div class="empty-state">
            <i class="fas fa-comments"></i>
            <p><strong>Sélectionnez une conversation</strong></p>
            <p>Choisissez une conversation dans la liste pour afficher les messages</p>
        </div>
    </div>
</div>