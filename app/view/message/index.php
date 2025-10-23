<?php
// Récupérer le message flash s'il existe
$flash = Session::getFlash();
$activePage = 'messagerie';
?>

<div class="messaging-container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="messaging-content">
        <!-- En-tête de la messagerie -->
        <div class="messaging-header">
            <h1 class="messaging-title">Mes messages</h1>
            <?php if ($unreadCount > 0): ?>
                <div class="unread-indicator">
                    <span class="unread-count"><?= $unreadCount ?></span>
                    <span class="unread-text">message<?= $unreadCount > 1 ? 's' : '' ?> non lu<?= $unreadCount > 1 ? 's' : '' ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Liste des conversations -->
        <div class="conversations-section">
            <?php if (empty($conversations)): ?>
                <div class="empty-conversations">
                    <div class="empty-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3>Aucune conversation</h3>
                    <p>Vous n'avez pas encore de conversations. Commencez par envoyer un message à un autre utilisateur depuis la page d'un livre qui vous intéresse.</p>
                    <a href="<?= BASE_URL ?>nos-livres" class="btn btn-primary">Parcourir les livres</a>
                </div>
            <?php else: ?>
                <div class="conversations-list">
                    <?php foreach ($conversations as $conversation): ?>
                        <a href="<?= BASE_URL ?>messages/conversation/<?= $conversation->getOtherUserId() ?>" 
                           class="conversation-item <?= $conversation->getUnreadCount() > 0 ? 'unread' : '' ?>">
                            
                            <div class="conversation-avatar">
                                <?php if ($conversation->getOtherAvatar()): ?>
                                    <img src="<?= BASE_URL ?>uploads/avatars/<?= $conversation->getOtherAvatar() ?>" 
                                         alt="<?= htmlspecialchars($conversation->getOtherUsername()) ?>">
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <?= strtoupper(substr($conversation->getOtherUsername(), 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="conversation-content">
                                <div class="conversation-header">
                                    <h3 class="conversation-name"><?= htmlspecialchars($conversation->getOtherUsername()) ?></h3>
                                    <span class="conversation-time"><?= $conversation->getFormattedDate() ?></span>
                                </div>
                                
                                <div class="conversation-preview">
                                    <p class="last-message">
                                        <?php if ($conversation->isSentBy($currentUser->getId())): ?>
                                            <span class="message-sender">Vous : </span>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($conversation->getExcerpt(60)) ?>
                                    </p>
                                    
                                    <?php if ($conversation->getUnreadCount() > 0): ?>
                                        <span class="unread-badge"><?= $conversation->getUnreadCount() ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Styles pour la messagerie */
.messaging-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.messaging-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #eee;
}

.messaging-title {
    font-size: 2rem;
    color: #333;
    margin: 0;
}

.unread-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
}

.unread-count {
    background-color: var(--primary-color);
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: bold;
}

.conversations-section {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.conversations-list {
    display: flex;
    flex-direction: column;
}

.conversation-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    text-decoration: none;
    color: inherit;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s;
}

.conversation-item:hover {
    background-color: #f8f9fa;
}

.conversation-item:last-child {
    border-bottom: none;
}

.conversation-item.unread {
    background-color: #f8f9ff;
    border-left: 4px solid var(--primary-color);
}

.conversation-avatar {
    margin-right: 1rem;
    flex-shrink: 0;
}

.conversation-avatar img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
}

.conversation-content {
    flex: 1;
    min-width: 0;
}

.conversation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.conversation-name {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0;
    color: #333;
}

.conversation-time {
    font-size: 0.85rem;
    color: #666;
}

.conversation-preview {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.last-message {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    flex: 1;
}

.message-sender {
    font-weight: 500;
    color: #333;
}

.unread-badge {
    background-color: var(--primary-color);
    color: white;
    border-radius: 10px;
    padding: 0.2rem 0.5rem;
    font-size: 0.75rem;
    font-weight: bold;
    margin-left: 0.5rem;
}

.empty-conversations {
    text-align: center;
    padding: 3rem 2rem;
    color: #666;
}

.empty-icon {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 1rem;
}

.empty-conversations h3 {
    margin: 1rem 0;
    color: #333;
}

.empty-conversations p {
    margin-bottom: 2rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
}

.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    text-decoration: none;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.2s;
    text-align: center;
}

.btn-primary {
    background-color: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background-color: var(--primary-hover);
    color: white;
}

.alert {
    padding: 0.75rem 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.alert-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-error,
.alert-danger {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

/* Responsive */
@media (max-width: 768px) {
    .messaging-container {
        margin: 1rem auto;
        padding: 0 0.5rem;
    }
    
    .messaging-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .conversation-item {
        padding: 0.75rem;
    }
    
    .conversation-avatar img,
    .avatar-placeholder {
        width: 40px;
        height: 40px;
    }
    
    .conversation-name {
        font-size: 1rem;
    }
    
    .conversation-time {
        font-size: 0.8rem;
    }
}
</style>