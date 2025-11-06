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
                <a href="<?= BASE_URL ?>messagerie/conversation/<?= $conversation->getOtherUserId() ?>" 
                   class="conversation-item <?= $conversation->getUnreadCount() > 0 ? 'conversation-unread' : '' ?> <?= isset($otherUser) && $conversation->getOtherUserId() == $otherUser->getId() ? 'active' : '' ?>">
                    
                    <div class="conversation-content">
                        <div class="conversation-avatar">
                            <img src="<?= BASE_URL ?>uploads/avatars/<?= escape($conversation->getOtherAvatar()) ?>" 
                                 alt="<?= escape($conversation->getOtherUsername()) ?>">
                        </div>
                        
                        <div class="conversation-info">
                            <div class="conversation-header-info">
                                <h3 class="conversation-name"><?= escape($conversation->getOtherUsername()) ?></h3>
                                <span class="conversation-time"><?= escape(formatMessageDate($conversation->getCreatedAt())) ?></span>
                            </div>
                            
                            <p class="conversation-preview">
                                <?php if (isMessageSentBy($conversation, $userId)): ?>
                                    Vous : 
                                <?php endif?>
                                <?= escape(getTextExcerpt($conversation->getContent(), 50)) ?>
                            </p>
                        </div>
                    </div>
                </a>
            <?php endforeach?>
        <?php endif?>
    </div>
</div>
